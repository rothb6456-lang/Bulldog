# Option H  
## First-Pass Implementation Order with Exact Artisan Commands

This is the most practical “do this next” sequence for getting from zero to a working Phase 1 + 2 foundation.

I’ll assume:
- Laravel project already created
- local MySQL available
- you are building locally first
- DreamHost deploy comes later

---

## 1. Create the Laravel project if not already created

```bash
composer create-project laravel/laravel bulldog-statbook
cd bulldog-statbook
```

---

## 2. Set up environment

Copy env file:

```bash
cp .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

Edit `.env` for local DB:

```env
APP_NAME=Bulldog
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bulldog_local
DB_USERNAME=root
DB_PASSWORD=
```

---

## 3. Install auth scaffolding if desired

For a clean starting point, I recommend Breeze.

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
```

Then migrate default auth tables:

```bash
php artisan migrate
```

### Note
You will likely modify the default `users` migration to fit Bulldog fields, or replace it early before too much code depends on it.

---

## 4. Create the local database

In MySQL, create:

```sql
CREATE DATABASE bulldog_local;
```

If you already ran default migrations before setting DB, reset after DB is ready:

```bash
php artisan migrate:fresh
```

---

## 5. Generate models, migrations, seeders, requests, controllers, policies, factories

You can use artisan to scaffold files quickly.

### Models with migrations/factories where useful

```bash
php artisan make:model UserProfile -m -f
php artisan make:model Sport -m -f
php artisan make:model PlayerIdentity -m -f
php artisan make:model CoachIdentity -m -f
php artisan make:model GuardianRelationship -m -f
php artisan make:model Organization -m
php artisan make:model Team -m -f
php artisan make:model TeamMembership -m -f
php artisan make:model TeamMembershipPosition -m -f
php artisan make:model TeamRoleAssignment -m -f
php artisan make:model AuditLog -m -f
```

### Seeders

```bash
php artisan make:seeder SportSeeder
```

### Form Requests

```bash
php artisan make:request Auth/RegisterRequest
php artisan make:request Auth/LoginRequest
php artisan make:request Identity/StorePlayerIdentityRequest
php artisan make:request Identity/StoreCoachIdentityRequest
php artisan make:request Guardian/StoreGuardianRelationshipRequest
php artisan make:request Teams/StoreTeamRequest
php artisan make:request Teams/UpdateTeamRequest
php artisan make:request Teams/StoreTeamRoleRequest
php artisan make:request Teams/StoreRosterEntryRequest
php artisan make:request Teams/UpdateRosterEntryRequest
```

### API Controllers

```bash
php artisan make:controller Api/Auth/RegisterController
php artisan make:controller Api/Auth/LoginController
php artisan make:controller Api/Auth/MeController
php artisan make:controller Api/Identity/PlayerIdentityController
php artisan make:controller Api/Identity/ClaimPlayerIdentityController
php artisan make:controller Api/Identity/CoachIdentityController
php artisan make:controller Api/Guardian/GuardianRelationshipController
php artisan make:controller Api/Teams/TeamController
php artisan make:controller Api/Teams/TeamRoleController
php artisan make:controller Api/Teams/RosterController
```

### Web Controllers for Blade pages

```bash
php artisan make:controller Web/DashboardController
php artisan make:controller Web/ProfileController
php artisan make:controller Web/TeamPageController
```

### Policies

```bash
php artisan make:policy TeamPolicy --model=Team
php artisan make:policy PlayerIdentityPolicy --model=PlayerIdentity
php artisan make:policy GuardianRelationshipPolicy --model=GuardianRelationship
```

### API Resources

```bash
php artisan make:resource Auth/MeResource
php artisan make:resource Identity/PlayerIdentityResource
php artisan make:resource Identity/CoachIdentityResource
php artisan make:resource Guardian/GuardianRelationshipResource
php artisan make:resource Teams/TeamResource
php artisan make:resource Teams/TeamRoleAssignmentResource
php artisan make:resource Teams/RosterEntryResource
```

### Feature tests

```bash
php artisan make:test Feature/Auth/RegisterTest
php artisan make:test Feature/Auth/LoginTest
php artisan make:test Feature/Identity/CreatePlayerIdentityTest
php artisan make:test Feature/Identity/ClaimPlayerIdentityTest
php artisan make:test Feature/Teams/CreateTeamTest
php artisan make:test Feature/Teams/AssignTeamRoleTest
php artisan make:test Feature/Teams/RosterTest
php artisan make:test Feature/Guardian/GuardianRelationshipTest
```

---

## 6. Add custom Action and Service directories manually

Artisan doesn’t generate these cleanly by default in the structure we want, so create them manually:

```bash
mkdir -p app/Actions/Auth
mkdir -p app/Actions/Identity
mkdir -p app/Actions/Teams
mkdir -p app/Actions/Roster
mkdir -p app/Actions/Guardian
mkdir -p app/Services/Codes
mkdir -p app/Services/Audit
mkdir -p app/Services
```

Then create files:

```bash
touch app/Actions/Auth/RegisterUserAction.php
touch app/Actions/Identity/CreatePlayerIdentityAction.php
touch app/Actions/Identity/ClaimPlayerIdentityAction.php
touch app/Actions/Identity/CreateCoachIdentityAction.php
touch app/Actions/Teams/CreateTeamAction.php
touch app/Actions/Teams/AssignTeamRoleAction.php
touch app/Actions/Roster/AddRosterPlayerAction.php
touch app/Actions/Guardian/CreateGuardianRelationshipAction.php

touch app/Services/Codes/CodeGenerator.php
touch app/Services/Audit/AuditLogService.php
touch app/Services/AgeService.php
```

---

## 7. Replace generated file contents with the stubs we created

Priority order:

1. migrations
2. models
3. seeders
4. services
5. actions
6. requests
7. policies
8. resources
9. controllers
10. routes
11. Blade pages
12. tests
13. factories

---

## 8. Run fresh migrations and seeders

Once migrations are edited:

```bash
php artisan migrate:fresh --seed
```

This should:
- create all tables
- seed baseball/softball

---

## 9. Register policies

Update `app/Providers/AuthServiceProvider.php`.

Then clear caches if needed:

```bash
php artisan optimize:clear
```

---

## 10. Run local server

```bash
php artisan serve
```

Open:

```text
http://localhost:8000
```

---

## 11. Run tests repeatedly

```bash
php artisan test
```

For a single file:

```bash
php artisan test tests/Feature/Auth/RegisterTest.php
```

---

## 12. Recommended build order by real dependency

### Pass 1
- environment
- migrations
- models
- seeders

### Pass 2
- services
- actions

### Pass 3
- requests
- policies
- resources
- API controllers
- API routes

### Pass 4
- factories
- feature tests

### Pass 5
- Blade pages
- web controllers
- web routes

That order reduces thrash.

---

# Option G  
## Web Route + Controller Stubs for Blade Pages

These wire your Blade app screens into something usable.

---

## 1. Web routes

### `routes/web.php`

```php
<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TeamPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/teams', [TeamPageController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamPageController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamPageController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamPageController::class, 'show'])->name('teams.show');
});
```

---

## 2. Dashboard controller

### `app/Http/Controllers/Web/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard');
    }
}
```

---

## 3. Profile controller

### `app/Http/Controllers/Web/ProfileController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load('profile');

        return view('profile.index', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string'],
        ]);

        $profile = $request->user()->profile;

        if (!$profile) {
            $profile = $request->user()->profile()->create([
                'display_name' => $validated['display_name'],
                'default_visibility_level' => 'private',
            ]);
        } else {
            $profile->update([
                'display_name' => $validated['display_name'],
                'bio' => $validated['bio'] ?? null,
            ]);
        }

        return redirect()
            ->route('profile.index')
            ->with('status', 'Profile updated.');
    }
}
```

---

## 4. Team page controller

### `app/Http/Controllers/Web/TeamPageController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Actions\Teams\CreateTeamAction;
use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamPageController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::query()
            ->whereHas('roleAssignments', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'active');
            })
            ->with('sport')
            ->latest()
            ->get();

        return view('teams.index', [
            'teams' => $teams,
        ]);
    }

    public function create()
    {
        return view('teams.create', [
            'sports' => Sport::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CreateTeamAction $action)
    {
        $validated = $request->validate([
            'sport_id' => ['required', 'uuid', 'exists:sports,id'],
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'season_label' => ['nullable', 'string', 'max:100'],
            'age_group' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'state_region' => ['nullable', 'string', 'max:100'],
        ]);

        $team = $action->execute($validated, $request->user()->id);

        return redirect()
            ->route('teams.show', $team)
            ->with('status', 'Team created successfully.');
    }

    public function show(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        $team->load('sport');

        $roles = $team->roleAssignments()
            ->with('user')
            ->where('status', 'active')
            ->get();

        $roster = $team->memberships()
            ->where('membership_type', 'player')
            ->where('status', 'active')
            ->with('playerIdentity', 'positions')
            ->get();

        return view('teams.show', [
            'team' => $team,
            'roles' => $roles,
            'roster' => $roster,
        ]);
    }
}
```

---

## 5. Improve the create team Blade page to actually post to a route

### Revised `resources/views/teams/create.blade.php`

```php
@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-2xl shadow p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-4">Create Team</h1>

        <form method="POST" action="{{ route('teams.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Sport</label>
                <select name="sport_id" class="w-full rounded border px-3 py-2" required>
                    <option value="">Select sport</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected(old('sport_id') === $sport->id)>
                            {{ $sport->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Team name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded border px-3 py-2" required>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Nickname</label>
                    <input type="text" name="nickname" value="{{ old('nickname') }}" class="w-full rounded border px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Season label</label>
                    <input type="text" name="season_label" value="{{ old('season_label') }}" class="w-full rounded border px-3 py-2">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Age group</label>
                    <input type="text" name="age_group" value="{{ old('age_group') }}" class="w-full rounded border px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="w-full rounded border px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">State / Region</label>
                <input type="text" name="state_region" value="{{ old('state_region') }}" class="w-full rounded border px-3 py-2">
            </div>

            <div>
                <button type="submit" class="rounded bg-blue-600 text-white px-4 py-2">
                    Create Team
                </button>
            </div>
        </form>
    </div>
@endsection
```

---

## 6. Improve profile Blade page to post correctly

### Revised `resources/views/profile/index.blade.php`

```php
@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-2xl shadow p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-4">Profile</h1>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Display name</label>
                    <input
                        type="text"
                        name="display_name"
                        class="w-full rounded border px-3 py-2"
                        value="{{ old('display_name', $user->profile->display_name ?? '') }}"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input
                        type="email"
                        class="w-full rounded border px-3 py-2"
                        value="{{ $user->email }}"
                        disabled
                    >
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium mb-1">Bio</label>
                <textarea
                    name="bio"
                    class="w-full rounded border px-3 py-2"
                    rows="4"
                >{{ old('bio', $user->profile->bio ?? '') }}</textarea>
            </div>

            <div class="mt-6">
                <button type="submit" class="rounded bg-gray-900 text-white px-4 py-2">
                    Save profile
                </button>
            </div>
        </form>
    </div>
@endsection
```

---

## 7. Optional welcome route/page note

You can keep Laravel’s default `welcome.blade.php` at first, or replace it later with a branded subdomain landing/login gateway.

---

# Option I  
## Copy/Paste Codex Execution Sequence for Incremental Building

This is a practical sequence you can use with Codex so it doesn’t try to build everything at once.

Use **one block at a time**.

---

## Codex Session 1 — App foundation

```text
You are building Bulldog Statbook as a separate Laravel application.

Constraints:
- Laravel
- MySQL for beta
- future PostgreSQL portability required
- WordPress remains separate
- responsive hosted web app
- UUIDs for core models
- use Laravel migrations, Eloquent, Form Requests, Policies, Actions/Services
- no DB-native enums
- no MySQL-specific triggers/stored procedures
- keep business logic out of controllers

Task:
Generate the recommended Laravel Phase 1 + 2 folder structure and explain the purpose of each major folder for:
- auth
- user/profile
- player identity
- coach identity
- guardian relationships
- teams
- team roles
- roster
```

---

## Codex Session 2 — Migrations

```text
Using Laravel Schema Builder, generate the Phase 1 + 2 migrations for:
- users
- user_profiles
- sports
- player_identities
- coach_identities
- guardian_relationships
- organizations
- teams
- team_memberships
- team_membership_positions
- team_role_assignments
- audit_logs

Requirements:
- UUID primary keys
- string status/type fields instead of DB enums
- useful indexes
- foreign keys
- MySQL-compatible but PostgreSQL-portable
- no game tables yet
```

---

## Codex Session 3 — Models

```text
Generate the Eloquent models for the Phase 1 + 2 schema.

Requirements:
- include HasFactory and HasUuids where appropriate
- UUID key config
- fillable fields
- core relationships
- keep models lean
- preserve User != PlayerIdentity
- support Team, TeamRoleAssignment, TeamMembership, GuardianRelationship workflows
```

---

## Codex Session 4 — Seeders and factories

```text
Generate:
- SportSeeder
- DatabaseSeeder update
- model factories for:
  User
  UserProfile
  Sport
  PlayerIdentity
  CoachIdentity
  GuardianRelationship
  Team
  TeamRoleAssignment
  TeamMembership
  TeamMembershipPosition
  AuditLog

Requirements:
- testing-friendly
- UUID-safe
- Phase 1 + 2 only
```

---

## Codex Session 5 — Support services

```text
Generate Laravel support services for:
- CodeGenerator
- AuditLogService
- AgeService

Requirements:
- portable design
- no major domain workflow logic here
- CodeGenerator for bulldog user code, player code, coach code, team code
- AuditLogService writes audit rows
- AgeService determines minor status from DOB
```

---

## Codex Session 6 — Identity actions

```text
Generate Action classes for:
- RegisterUserAction
- CreatePlayerIdentityAction
- ClaimPlayerIdentityAction
- CreateCoachIdentityAction
- CreateGuardianRelationshipAction

Requirements:
- use transactions where appropriate
- audit important operations
- prevent hijacking claimed player identities
- guardian relationship is not account ownership
- portable across MySQL beta / future Postgres
```

---

## Codex Session 7 — Team and roster actions

```text
Generate Action classes for:
- CreateTeamAction
- AssignTeamRoleAction
- AddRosterPlayerAction
- UpdateRosterMembershipAction
- DeactivateRosterMembershipAction

Requirements:
- creator becomes team_admin
- team roles are contextual
- add roster supports existing player or inline unclaimed player
- preserve roster history
- audit important operations
```

---

## Codex Session 8 — Requests and validation

```text
Generate Form Request classes for:
- RegisterRequest
- LoginRequest
- StorePlayerIdentityRequest
- StoreCoachIdentityRequest
- StoreGuardianRelationshipRequest
- StoreTeamRequest
- UpdateTeamRequest
- StoreTeamRoleRequest
- StoreRosterEntryRequest
- UpdateRosterEntryRequest

Requirements:
- app-layer validation
- no heavy business logic here
- Phase 1 + 2 only
```

---

## Codex Session 9 — Policies

```text
Generate Laravel policies for:
- TeamPolicy
- PlayerIdentityPolicy
- GuardianRelationshipPolicy

Requirements:
- server-side enforcement
- team_admin can assign/remove roles
- team_admin and coach can manage roster
- self/guardian-based access for player identity
- readable and maintainable logic
```

---

## Codex Session 10 — API resources

```text
Generate API Resource classes for:
- MeResource
- PlayerIdentityResource
- CoachIdentityResource
- GuardianRelationshipResource
- TeamResource
- TeamRoleAssignmentResource
- RosterEntryResource

Requirements:
- stable response shape
- no unnecessary sensitive leakage
- preserve distinction between User and PlayerIdentity
```

---

## Codex Session 11 — API controllers and routes

```text
Generate Laravel API controllers and routes for:
- auth register/login/me
- player create/show/claim
- coach create
- guardian list/create
- team list/create/show
- team role list/create/delete
- roster list/create/delete

Requirements:
- thin controllers
- Form Requests
- Actions
- Policies
- API Resources
- /api/v1 route organization
```

---

## Codex Session 12 — Web controllers, routes, and Blade pages

```text
Generate:
- web routes
- DashboardController
- ProfileController
- TeamPageController
- Blade views for:
  layouts/guest
  layouts/app
  auth/login
  auth/register
  dashboard
  profile/index
  teams/index
  teams/create
  teams/show

Requirements:
- responsive
- simple
- mobile-friendly
- hosted web app feel
- compatible with save-to-home-screen usage
```

---

## Codex Session 13 — Tests

```text
Generate Laravel Feature tests for:
- register
- login
- create player identity
- claim player identity
- prevent hijack
- create team
- creator becomes team_admin
- assign team role
- non-admin cannot assign team role
- add existing player to roster
- create unclaimed player inline
- unauthorized roster management blocked
- create guardian relationship
- user sees only own guardian relationships
```

---

## Codex Session 14 — Portability review

```text
Review the generated Phase 1 + 2 Laravel code for PostgreSQL portability.

Check for:
- enum usage
- JSON overuse
- MySQL-specific assumptions
- timestamp/date handling
- UUID consistency
- raw SQL portability risks
- business logic incorrectly placed in DB layer

Provide a checklist of anything that should be fixed now.
```

---

# Recommended real-world workflow with Codex

For each Codex session:

## 1
Paste the session prompt

## 2
Let Codex generate the files

## 3
Review for these specific issues:
- User vs Player separation preserved?
- team-context permissions preserved?
- no WordPress coupling?
- no MySQL-only shortcuts?
- minors not made publicly searchable?

## 4
Apply/revise

## 5
Run:
```bash
php artisan test
php artisan optimize:clear
```

## 6
Move to next session

---

# My practical recommendation from here

Your best immediate build path is now:

1. **Set up the Laravel app locally**
2. **Create the files from Options H/G/I**
3. **Run migrations + seeders**
4. **Wire auth**
5. **Get one golden path working:**
   - register
   - create team
   - assign role
   - add roster player

That is the first meaningful milestone.

---

# Best next artifact after this

The next most useful deliverable would be one of these:

## Option J
A **Golden Path Checklist** for the first fully working demo flow

## Option K
A **local-to-DreamHost deployment runbook** once the app works locally

## Option L
A **Phase 1 bug-risk checklist** so you know what is most likely to break first

## My recommendation
Do these next in order:
1. **Option J**
2. **Option L**
3. **Option K**

If you want, I can continue immediately with **Option J**.