# Connecting Your Tested Backend Logic to the Web Blade Frontend

This guide structures the exact **Web Controllers, Routes, and Template Connections** required to transition your validated database operations and business logic into a live, interactive browser interface. 

It is designed to consume the **Wave 1 & 2** actions and models you've already verified (such as `CreateTeamAction` and `AddRosterPlayerAction`) and cleanly wire them up to your responsive Blade pages.

---

## 🛠️ Step 1: Declare the Web Routes (`routes/web.php`)

Replace your current `routes/web.php` with this authenticated group layout. This maps the browser URL requests directly to your new Web Controllers while keeping session-based authentication active.

```php
<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TeamPageController;
use Illuminate\Support\Facades\Route;

// Public Welcome Page (Fallback)
Route::get('/', function () {
    return view('welcome');
});

// Authenticated Team Operations & Dashboard (Session Guarded)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Teams Management Routes
    Route::get('/teams', [TeamPageController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamPageController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamPageController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamPageController::class, 'show'])->name('teams.show');
    
    // Roster Management (Within specific team context)
    Route::post('/teams/{team}/roster', [TeamPageController::class, 'storeRoster'])->name('teams.roster.store');

    // User Profile Management
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
```

---

## 🏗️ Step 2: Implement the Web Controllers

Create these three controller files to handle the web browser request lifecycles.

### 1. Dashboard Controller (`app/Http/Controllers/Web/DashboardController.php`)
This controller aggregates the exact metrics required by your visual dashboard prototype (Active Teams, Rostered Players, Active Roles) and passes them to the template.

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TeamMembership;
use App\Models\TeamRoleAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Gather all teams the user has a role on, loading the sport relation
        $roleAssignments = TeamRoleAssignment::with(['team.sport'])
            ->where('user_id', $user->id)
            ->get();

        $teams = $roleAssignments->map(function ($assignment) {
            return [
                'id' => $assignment->team->id,
                'name' => $assignment->team->name,
                'sport' => $assignment->team->sport->name,
                'season' => $assignment->team->season_label,
                'age_group' => $assignment->team->age_group,
                'role' => $assignment->role_type,
                'status' => $assignment->team->status,
            ];
        });

        // 2. Compute the exact summary counts for your dashboard metric cards
        $activeTeamsCount = $teams->where('status', 'active')->count();
        
        $managedTeamIds = $roleAssignments->whereIn('role_type', ['team_admin', 'coach'])
            ->pluck('team_id');
            
        $rosteredPlayersCount = TeamMembership::whereIn('team_id', $managedTeamIds)
            ->where('status', 'active')
            ->where('membership_type', 'player')
            ->count();

        $activeRolesCount = $roleAssignments->count();

        // 3. Extract the Primary Team Context to populate the primary context preview card
        $primaryAssignment = $roleAssignments->where('role_type', 'team_admin')->first() 
            ?? $roleAssignments->first();
        $primaryTeamContext = $primaryAssignment ? $primaryAssignment->team : null;
        $primaryTeamRole = $primaryAssignment ? $primaryAssignment->role_type : null;

        // 4. Retrieve the claimed player identity linked to this user
        $playerIdentity = $user->playerIdentity;

        return view('dashboard', compact(
            'user',
            'teams',
            'activeTeamsCount',
            'rosteredPlayersCount',
            'activeRolesCount',
            'primaryTeamContext',
            'primaryTeamRole',
            'playerIdentity'
        ));
    }
}
```

### 2. Team Page Controller (`app/Http/Controllers/Web/TeamPageController.php`)
This binds your validated `StoreTeamRequest` and `StoreRosterEntryRequest` to their transaction-safe Actions.

```php
<?php

namespace App\Http\Controllers\Web;

use App\Actions\Roster\AddRosterPlayerAction;
use App\Actions\Teams\CreateTeamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreRosterEntryRequest;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamPageController extends Controller
{
    protected CreateTeamAction $createTeamAction;
    protected AddRosterPlayerAction $addRosterPlayerAction;

    public function __construct(
        CreateTeamAction $createTeamAction,
        AddRosterPlayerAction $addRosterPlayerAction
    ) {
        $this->createTeamAction = $createTeamAction;
        $this->addRosterPlayerAction = $addRosterPlayerAction;
    }

    /**
     * Show list of teams.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $teams = Team::whereHas('roleAssignments', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['sport', 'roleAssignments' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->get();

        return view('teams.index', compact('teams'));
    }

    /**
     * Show Create Team form.
     */
    public function create()
    {
        // Seeding Baseball/Softball dropdown options
        $sports = Sport::all();

        return view('teams.create', compact('sports'));
    }

    /**
     * Handle Create Team submit.
     */
    public function store(StoreTeamRequest $request)
    {
        $team = $this->createTeamAction->execute(
            $request->validated(),
            $request->user()->id
        );

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team "' . $team->name . '" was successfully created!');
    }

    /**
     * Show Team detail workspace (Overview / Roster / Roles tabs).
     */
    public function show(Request $request, Team $team)
    {
        // Enforce Server-Side Policy
        $this->authorize('view', $team);

        $user = $request->user();

        // Eager-load relations to protect against database bottlenecks
        $team->load([
            'sport',
            'memberships.playerIdentity',
            'roleAssignments.user'
        ]);

        $userRoleAssignment = $team->roleAssignments->firstWhere('user_id', $user->id);
        $userRole = $userRoleAssignment ? $userRoleAssignment->role_type : 'viewer';

        return view('teams.show', compact('team', 'userRole'));
    }

    /**
     * Handle Rostering Player submit.
     */
    public function storeRoster(StoreRosterEntryRequest $request, Team $team)
    {
        // Enforce Server-Side Policy
        $this->authorize('manageRoster', $team);

        $membership = $this->addRosterPlayerAction->execute(
            $team->id,
            $request->validated(),
            $request->user()->id
        );

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Athlete "' . $membership->playerIdentity->display_name . '" was rostered successfully.');
    }
}
```

### 3. Profile Controller (`app/Http/Controllers/Web/ProfileController.php`)
This handles display preferences and the primary sport selection.

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('profile');
        $sports = Sport::all();

        return view('profile.index', compact('user', 'sports'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
            'primary_sport_id' => ['nullable', 'uuid', 'exists:sports,id'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update([
            'email' => $validated['email']
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $validated['display_name'],
                'avatar_url' => $validated['avatar_url'],
                'primary_sport_id' => $validated['primary_sport_id'],
            ]
        );

        return redirect()->route('profile.index')
            ->with('success', 'Profile settings updated successfully!');
    }
}
```

---

## 🔌 Step 3: Wire the HTML Blade Templates to Forms

Now you need to replace the static `<form>` placeholders in your visual template views with real Laravel attributes to hook them up to the backend.

### 1. Wiring the Team Creation Form (`resources/views/teams/create.blade.php`)
Update the form block inside your team creation layout to use the dynamic action routes and sports variables:

```html
<!-- Real Blade Form Setup -->
<form action="{{ route('teams.store') }}" method="POST" class="space-y-6">
    @csrf

    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="p-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Dynamic Sport Dropdown -->
    <div>
        <label for="sport_id" class="block text-sm font-semibold text-gray-700">Sport *</label>
        <select name="sport_id" id="sport_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green focus:ring-green">
            <option value="">Select Sport...</option>
            @foreach($sports as $sport)
                <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>
                    {{ $sport->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Team Name -->
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700">Team Name *</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g., Bulldogs 12U"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green focus:ring-green">
    </div>

    <!-- Age Group -->
    <div>
        <label for="age_group" class="block text-sm font-semibold text-gray-700">Age Group (Optional)</label>
        <input type="text" name="age_group" id="age_group" value="{{ old('age_group') }}" placeholder="e.g., 12U, Varsity"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green focus:ring-green">
    </div>

    <!-- Season Label -->
    <div>
        <label for="season_label" class="block text-sm font-semibold text-gray-700">Season (Optional)</label>
        <input type="text" name="season_label" id="season_label" value="{{ old('season_label') }}" placeholder="e.g., Fall 2026"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green focus:ring-green">
    </div>

    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('teams.index') }}" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="px-4 py-2 bg-green hover:bg-green-hover text-white font-semibold rounded-md shadow-sm">
            Create Team
        </button>
    </div>
</form>
```

### 2. Wiring the Dynamic Roster Player Form (`resources/views/teams/show.blade.php`)
This form runs inside your main team detail page. It enables coaches to add a player to the team roster inline (Pathway B):

```html
@can('manageRoster', $team)
<!-- Only visible to Team Admins and Coaches via TeamPolicy -->
<form action="{{ route('teams.roster.store', $team->id) }}" method="POST" class="space-y-4 bg-white p-6 rounded-lg border border-gray-200">
    @csrf

    <h3 class="text-md font-bold text-navy">Add Athlete to Roster</h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Display Name -->
        <div>
            <label for="display_name" class="block text-xs font-semibold text-gray-700">Athlete Name *</label>
            <input type="text" name="display_name" id="display_name" required placeholder="Jonny Damon"
                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-green focus:border-green">
        </div>

        <!-- Jersey Number -->
        <div>
            <label for="jersey_number" class="block text-xs font-semibold text-gray-700">Jersey Number</label>
            <input type="text" name="jersey_number" id="jersey_number" placeholder="18"
                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-green focus:border-green">
        </div>

        <!-- Birth Year -->
        <div>
            <label for="birth_year" class="block text-xs font-semibold text-gray-700">Birth Year (Optional)</label>
            <input type="number" name="birth_year" id="birth_year" min="1900" max="{{ date('Y') }}" placeholder="2014"
                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-green focus:border-green">
        </div>
    </div>

    <!-- Preferred Positions (Passes array directly to cast into JSON) -->
    <div class="mt-3">
        <label class="block text-xs font-semibold text-gray-700 mb-1">Primary Positions</label>
        <div class="flex space-x-4">
            <label class="inline-flex items-center text-sm"><input type="checkbox" name="positions[]" value="P" class="text-green focus:ring-green"> <span class="ml-1">P</span></label>
            <label class="inline-flex items-center text-sm"><input type="checkbox" name="positions[]" value="C" class="text-green focus:ring-green"> <span class="ml-1">C</span></label>
            <label class="inline-flex items-center text-sm"><input type="checkbox" name="positions[]" value="SS" class="text-green focus:ring-green"> <span class="ml-1">SS</span></label>
            <label class="inline-flex items-center text-sm"><input type="checkbox" name="positions[]" value="LF" class="text-green focus:ring-green"> <span class="ml-1">LF</span></label>
            <label class="inline-flex items-center text-sm"><input type="checkbox" name="positions[]" value="CF" class="text-green focus:ring-green"> <span class="ml-1">CF</span></label>
            <label class="inline-flex items-center text-sm"><input type="checkbox" name="positions[]" value="RF" class="text-green focus:ring-green"> <span class="ml-1">RF</span></label>
        </div>
    </div>

    <div class="flex justify-end mt-4">
        <button type="submit" class="px-4 py-2 bg-green hover:bg-green-hover text-white text-sm font-semibold rounded-md shadow-sm">
            Roster Player
        </button>
    </div>
</form>
@endcan
```
