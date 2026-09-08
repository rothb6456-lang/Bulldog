# Wave 3: Game Controller & Scheduling Engine

This guide contains the implementation files to wire up your game scheduler (`create.blade.php`) and pre-game command center (`show.blade.php`) inside your Laravel application.

These classes manage validating the input, generating custom game codes, running transaction-wrapped database operations, and loading rosters dynamically.

---

## 🛠️ Step 1: Create the Store Game Request

Create a new file in your project at **`app/Http/Requests/Games/StoreGameRequest.php`**:

```php
<?php

namespace App\Http\Requests\Games;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Ensure the logged-in user is a Team Admin or Coach of the selected Home Team.
     */
    public function authorize(): bool
    {
        $homeTeamId = $this->input('home_team_id');
        if (!$homeTeamId) {
            return false;
        }

        $team = Team::find($homeTeamId);
        if (!$team) {
            return false;
        }

        // Check if user has admin/coach role on the Home Team
        return $team->roleAssignments()
            ->where('user_id', $this->user()->id)
            ->whereIn('role_type', ['team_admin', 'coach'])
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'home_team_id' => ['required', 'uuid', 'exists:teams,id'],
            'away_team_id' => [
                'required', 
                'uuid', 
                'exists:teams,id',
                'different:home_team_id' // A team cannot play itself
            ],
            'ruleset_id' => ['required', 'uuid', 'exists:rulesets,id'],
            'scheduled_at' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'away_team_id.different' => 'The Away Team must be different from the Home Team.',
            'scheduled_at.after_or_equal' => 'Matchups cannot be scheduled in the past.',
        ];
    }
}
```

---

## 🛠️ Step 2: Implement the Create Game Action

This Action generates a unique Game Code (e.g., `GM-A4F9X2`), saves the matchup inside a transaction, and pre-seeds the game roster context by grabbing active players from both teams.

Create a new file in your project at **`app/Actions/Games/CreateGameAction.php`**:

```php
<?php

namespace App\Actions\Games;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateGameAction
{
    /**
     * Execute the transaction-safe game scheduling workflow.
     */
    public function execute(array $data, string $creatorUserId): Game
    {
        return DB::transaction(function () use ($data, $creatorUserId) {
            // Generate unique Game Code
            do {
                $gameCode = 'GM-' . Str::upper(Str::random(8));
            } while (Game::where('game_code', $gameCode)->exists());

            // 1. Create the Game
            $game = Game::create([
                'game_code' => $gameCode,
                'sport_id' => Team::find($data['home_team_id'])->sport_id, // Inherit sport from home team
                'home_team_id' => $data['home_team_id'],
                'away_team_id' => $data['away_team_id'],
                'ruleset_id' => $data['ruleset_id'],
                'scheduled_at' => $data['scheduled_at'],
                'location' => $data['location'],
                'status' => 'draft', // Standard initial state
                'ownership_team_id' => $data['home_team_id'], // Home team owns by default
                'created_by_user_id' => $creatorUserId,
            ]);

            // 2. Pre-populate game roster tables from active team memberships
            $this->loadTeamRosterIntoGame($game, $game->home_team_id);
            $this->loadTeamRosterIntoGame($game, $game->away_team_id);

            return $game;
        });
    }

    /**
     * Helper to load current team players as eligible game roster slots.
     */
    protected function loadTeamRosterIntoGame(Game $game, string $teamId): void
    {
        $team = Team::find($teamId);
        if (!$team) {
            return;
        }

        $activePlayerMemberships = $team->memberships()
            ->where('membership_type', 'player')
            ->where('status', 'active')
            ->get();

        foreach ($activePlayerMemberships as $membership) {
            $game->gameRosterEntries()->create([
                'team_id' => $teamId,
                'player_identity_id' => $membership->player_identity_id,
                'team_membership_id' => $membership->id,
                'roster_status' => 'active',
                'eligible_to_play' => true, // Default to true
            ]);
        }
    }
}
```

---

## 🛠️ Step 3: Implement the Web Game Controller

This Web Controller connects your views to the scheduling business logic, loading matching sports, teams, and rulesets.

Create a new file in your project at **`app/Http/Controllers/GameController.php`**:

```php
<?php

namespace App\Http\Controllers;

use App\Actions\Games\CreateGameAction;
use App\Http\Requests\Games\StoreGameRequest;
use App\Models\Game;
use App\Models\Ruleset;
use App\Models\Team;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected CreateGameAction $createGameAction;

    public function __construct(CreateGameAction $createGameAction)
    {
        $this->createGameAction = $createGameAction;
    }

    /**
     * Show the Game Creation scheduler page.
     */
    public function create(Request $request)
    {
        // Fetch all teams where the user has scheduling rights (Admin or Coach)
        $managedTeamIds = $request->user()->id 
            ? \DB::table('team_role_assignments')
                ->where('user_id', $request->user()->id)
                ->whereIn('role_type', ['team_admin', 'coach'])
                ->pluck('team_id')
            : collect();

        $managedTeams = Team::whereIn('id', $managedTeamIds)->with('sport')->get();

        // Fetch possible opponents grouped by sport for front-end real-time filtering
        $allTeams = Team::with('sport')->get();

        // Fetch seeded rulesets
        $rulesets = Ruleset::all();

        return view('games.create', compact('managedTeams', 'allTeams', 'rulesets'));
    }

    /**
     * Store scheduled game and redirect to workspace.
     */
    public function store(StoreGameRequest $request)
    {
        $game = $this->createGameAction->execute(
            $request->validated(),
            $request->user()->id
        );

        return redirect()->route('games.show', $game->id)
            ->with('success', 'Game scheduled successfully. Your roster has been prepared.');
    }

    /**
     * Show the Pre-Game interactive workspace.
     */
    public function show(Game $game)
    {
        // Eager load nested associations to avoid N+1 query bottlenecks on the lineup diamond
        $game->load([
            'sport',
            'ruleset',
            'homeTeam.memberships.playerIdentity',
            'awayTeam.memberships.playerIdentity',
            'gameRosterEntries.playerIdentity',
            'lineupEntries.playerIdentity',
            'defensiveAssignments.playerIdentity'
        ]);

        return view('games.show', compact('game'));
    }
}
```

---

## 🛠️ Step 4: Register the Web Routes

Open your **`routes/web.php`** file and ensure the following routes are defined inside your authenticated session middleware group:

```php
use App\Http\Controllers\GameController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Game Scheduling and Workspace Routes
    Route::get('/games/create', [GameController::class, 'create'])->name('games.create');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
});
```
