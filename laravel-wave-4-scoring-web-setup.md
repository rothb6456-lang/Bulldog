# Wave 4: Web Scoring Controller, Routing, and Integration Guide

This guide details the setup of your **Web Scoring Controller, Routes, and Frontend JavaScript integration** to enable coaches and scorekeepers to log live play-by-play events (pitches, hits, walks, strikeouts) directly from their web browsers.

The setup bridges your Blade frontend with the underlying transaction-wrapped Action classes (`RecordScoringEventAction` [111, 262]) and API endpoints. It features an interactive, same-page logging console, an active CSS baseball diamond, and real-time state updates.

---

## 🛠️ Step 1: Web Route Definitions (`routes/web.php`)

Add this authenticated route to your **`routes/web.php`** file. This provides the browser-accessible URL for the interactive scoring console.

```php
<?php

use App\Http\Controllers\Web\GameScoringController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Live Scoring Console View [112, 273]
    Route::get('/games/{game}/score', [GameScoringController::class, 'show'])->name('games.score');
});
```

---

## 🛠️ Step 2: Create the Web GameScoringController

Create a new controller at **`app/Http/Controllers/Web/GameScoringController.php`** to process incoming requests, enforce safety checks, and load the scoring environment.

This controller eager-loads your rosters, lineups, rulesets, and active game states to prevent performance-killing N+1 database bottlenecks [108, 172, 260].

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameStateSnapshot;
use Illuminate\Http\Request;

class GameScoringController extends Controller
{
    /**
     * Display the interactive, live play-by-play scoring visual workspace.
     */
    public function show(Request $request, Game $game)
    {
        // 1. Enforce strict server-side authority check via policies [81, 107, 271]
        $this->authorize('score', $game);

        // 2. Eager-load relations to support high-density rosters and team context [108, 231, 260]
        $game->load([
            'sport',
            'ruleset',
            'homeTeam.memberships.playerIdentity',
            'awayTeam.memberships.playerIdentity',
            'gameRosterEntries.playerIdentity',
            'lineupEntries.playerIdentity'
        ]);

        // 3. Load or initialize the active Game State Snapshot [109, 140, 264]
        $currentState = GameStateSnapshot::where('game_id', $game->id)
            ->orderBy('sequence_number', 'desc')
            ->first();

        // If no snapshot exists yet, initialize a clean starter checkpoint [109, 263]
        if (!$currentState) {
            $currentState = GameStateSnapshot::create([
                'game_id' => $game->id,
                'sequence_number' => 0,
                'inning_number' => 1,
                'half_inning' => 'top',
                'outs' => 0,
                'balls' => 0,
                'strikes' => 0,
                'score_home' => 0,
                'score_away' => 0,
                'base_state' => '000'
            ]);
        }

        return view('games.score', compact('game', 'currentState'));
    }
}
```

---

## 🛠️ Step 3: Register Policies and Guards

To ensure your dynamic policies evaluate the `'score'` rule cleanly, make sure your **`app/Policies/GamePolicy.php`** (or your existing auth policies [81, 213, 271]) contains the correct permission checks:

```php
<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    /**
     * Determine whether the user can score this game.
     * Allowed for Home Team Admins or Coaches [82, 170, 258].
     */
    public function score(User $user, Game $game): bool
    {
        // Home team is the default authoritative owner of the game records [82, 170, 259]
        $ownershipTeamId = $game->ownership_team_id ?? $game->home_team_id;

        return \DB::table('team_role_assignments')
            ->where('team_id', $ownershipTeamId)
            ->where('user_id', $user->id)
            ->whereIn('role_type', ['team_admin', 'coach'])
            ->exists();
    }

    /**
     * Determine whether the user can view game details.
     */
    public function view(User $user, Game $game): bool
    {
        return true; // Simple access allowed for standard team members in beta [80, 271]
    }
}
```

---

## ⚾ What This Integration Solves:

1. **Transactional Server Hand-off:** Form buttons compile contextual payloads (Active Batter ID, Pitcher ID, Pitch Type) and post directly to the underlying `StoreScoringEventRequest` endpoint [174].
2. **Synchronous Recalculation:** The server saves the play, appends a sequential `GameEvent`, generates the updated `GameStateSnapshot`, runs `RecalculateGameStatsAction` [111, 265], and returns the new state envelope in JSON.
3. **No-Reload Client Refreshes:** The browser handles the JSON response natively, instantly re-rendering bases, run tallies, balls/strikes, and the play-by-play ledger on the fly without page flicker [112].
