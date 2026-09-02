# Bulldog Laravel Starter Architecture v1  
## Beta Stack: Laravel + MySQL on DreamHost, Postgres-portable by design

This is the first stack-specific implementation guide for Bulldog Statbook.

It assumes:

- **WordPress** remains the main marketing/content site
- **Bulldog Statbook** is a separate Laravel application
- **MySQL on DreamHost** is acceptable for beta
- the app is delivered as a **responsive web app** on a subdomain
- users may save it to home screen like an app
- Codex must preserve **future PostgreSQL portability**

---

# 1. Locked technical direction

## Deployment shape
- `www.yourdomain.com` → WordPress
- `statbook.yourdomain.com` or `app.yourdomain.com` → Laravel Bulldog app

## Beta stack
- **Backend/App Framework:** Laravel
- **Database:** MySQL
- **Frontend delivery:** responsive web app
- **App-like usage:** browser + home-screen save
- **Future migration path:** PostgreSQL-compatible design discipline

---

# 2. Product delivery model

Bulldog is not being built as:
- a WordPress plugin
- a native iOS app
- a desktop executable

Bulldog is being built as:

# **a hosted web application**

That single app should support:
- desktop browser usage
- mobile browser usage
- home-screen saved usage
- coach/admin workflows
- player/parent history access

---

# 3. Laravel architecture recommendation

For Bulldog, I recommend a Laravel app structured like this:

```text
app/
  Actions/
    Auth/
    Identity/
    Teams/
    Roster/
    Guardian/
  Domain/
    Identity/
    Teams/
    Permissions/
    Shared/
  Http/
    Controllers/
      Api/
        Auth/
        Identity/
        Teams/
        Guardian/
    Requests/
      Auth/
      Identity/
      Teams/
      Guardian/
    Resources/
      Auth/
      Identity/
      Teams/
      Guardian/
    Middleware/
  Models/
  Policies/
  Providers/
  Services/
    Codes/
    Audit/
    Permissions/
  Support/
    Enums/
    Exceptions/
    Helpers/
database/
  factories/
  migrations/
  seeders/
routes/
  api.php
  web.php
resources/
  views/
tests/
  Feature/
  Unit/
```

---

# 4. Architectural style inside Laravel

## Use these Laravel patterns
- **Eloquent Models** for entities
- **Form Requests** for validation
- **Policies** for authorization
- **Action classes** for business workflows
- **API Resources** for response formatting
- **Feature tests** for endpoint/workflow testing
- **Seeders** for sports / baseline system data

## Avoid these pitfalls
- fat controllers
- business logic embedded directly in models
- WordPress-style procedural code
- DB triggers/stored procedures for app logic
- MySQL-specific shortcuts

---

# 5. Frontend approach recommendation

Because you want:
- web app behavior
- app-like feel
- no native app requirement
- home-screen usage

I recommend:

## Initial frontend approach
### **Laravel Blade + responsive UI**
for the first implementation slice.

Why:
- simplest path
- easiest DreamHost/PHP alignment
- fast to beta
- low infrastructure complexity
- good enough for Phase 1 + 2

Then, if needed later, you can evolve into:
- Livewire
- Inertia
- Vue/React frontend layers

But for now, I would not overcomplicate the frontend stack.

## Why this is okay
Phase 1 + 2 is mostly:
- auth
- profile
- team creation
- roster management
- role assignment

Those are very well suited to Blade + responsive forms/tables/cards.

---

# 6. Web app / home-screen app recommendations

To support the “save to home screen” pattern:

## Near-term
- responsive layout
- clean mobile nav
- branded icon assets
- mobile-friendly viewport/meta setup
- auth session flow that works cleanly on mobile

## Soon after
- `manifest.json`
- app icons
- theme color
- Apple touch icon
- add-to-home-screen friendly behavior

## Later
- PWA enhancements if useful
- offline caching only where truly beneficial

For beta, you do **not** need full PWA complexity yet.

---

# 7. Laravel package recommendations

I’ll keep this conservative.

## Likely useful
- **Laravel Breeze** or **Laravel Fortify** for auth
- **spatie/laravel-permission** only if needed later, but I would likely start with custom team-context roles
- **ramsey/uuid** if needed, though Laravel supports UUID patterns fine
- optionally **spatie/laravel-data** later, but not required

## My recommendation
Start simple:
- Laravel auth scaffolding
- custom team roles
- custom policies
- custom actions/services

Do **not** over-package the app early.

---

# 8. Core Phase 1 + 2 models

These are your first Eloquent models.

## Identity
- `User`
- `UserProfile`
- `PlayerIdentity`
- `CoachIdentity`
- `GuardianRelationship`

## Sports/teams
- `Sport`
- `Organization`
- `Team`
- `TeamMembership`
- `TeamMembershipPosition`
- `TeamRoleAssignment`

## Support
- `AuditLog`

---

# 9. UUID strategy

Use UUIDs from day one.

## Recommendation
Each core table should use:
- UUID primary key
- UUID foreign keys consistently

### In Laravel models
Use:
- non-incrementing keys
- string key type

Example pattern:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Team extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
}
```

This is the right long-term move.

---

# 10. MySQL-first / Postgres-later guardrails

These are stack-specific rules for Codex.

## Rule 1
Use Laravel migrations only.

## Rule 2
Do not use MySQL-only stored procedures or triggers for core workflows.

## Rule 3
Use string status fields instead of DB-native enums.

Examples:
- `account_status`
- `claim_status`
- `role_type`
- `visibility_level`

## Rule 4
Use JSON sparingly and only for flexible metadata, not core relational truth.

## Rule 5
Keep workflow logic in:
- Actions
- Services
- Policies

## Rule 6
Prefer Laravel Query Builder / Eloquent over raw SQL.

## Rule 7
Keep search simple in beta.
No dependency on MySQL full-text search behavior.

---

# 11. Recommended first routes

## `routes/api.php`
These are the first API routes I recommend.

```php
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Identity\PlayerIdentityController;
use App\Http\Controllers\Api\Identity\ClaimPlayerIdentityController;
use App\Http\Controllers\Api\Identity\CoachIdentityController;
use App\Http\Controllers\Api\Guardian\GuardianRelationshipController;
use App\Http\Controllers\Api\Teams\TeamController;
use App\Http\Controllers\Api\Teams\TeamRoleController;
use App\Http\Controllers\Api\Teams\RosterController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', RegisterController::class);
    Route::post('/auth/login', LoginController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', MeController::class);

        Route::post('/players', [PlayerIdentityController::class, 'store']);
        Route::get('/players/{player}', [PlayerIdentityController::class, 'show']);
        Route::post('/players/{player}/claim', ClaimPlayerIdentityController::class);

        Route::post('/coaches', [CoachIdentityController::class, 'store']);

        Route::get('/guardians/relationships', [GuardianRelationshipController::class, 'index']);
        Route::post('/guardians/relationships', [GuardianRelationshipController::class, 'store']);

        Route::get('/teams', [TeamController::class, 'index']);
        Route::post('/teams', [TeamController::class, 'store']);
        Route::get('/teams/{team}', [TeamController::class, 'show']);
        Route::patch('/teams/{team}', [TeamController::class, 'update']);

        Route::get('/teams/{team}/roles', [TeamRoleController::class, 'index']);
        Route::post('/teams/{team}/roles', [TeamRoleController::class, 'store']);
        Route::delete('/teams/{team}/roles/{assignment}', [TeamRoleController::class, 'destroy']);

        Route::get('/teams/{team}/roster', [RosterController::class, 'index']);
        Route::post('/teams/{team}/roster', [RosterController::class, 'store']);
        Route::patch('/teams/{team}/roster/{membership}', [RosterController::class, 'update']);
        Route::delete('/teams/{team}/roster/{membership}', [RosterController::class, 'destroy']);
    });
});
```

---

# 12. Recommended first web routes

For Blade-based app screens:

## `routes/web.php`

```php
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/profile', 'profile.index')->name('profile.index');
    Route::view('/teams', 'teams.index')->name('teams.index');
    Route::view('/teams/create', 'teams.create')->name('teams.create');
});
```

You can keep API and web routes cleanly separated.

---

# 13. First migrations in Laravel syntax

These are the first migration stubs I recommend.

---

## `create_users_table.php`
If Laravel auth scaffolding generates this, adapt it.

```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('bulldog_user_code', 32)->unique();
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password')->nullable();
    $table->string('phone')->nullable();
    $table->timestamp('phone_verified_at')->nullable();
    $table->date('date_of_birth')->nullable();
    $table->boolean('is_minor')->default(false);
    $table->string('account_status', 30)->default('active');
    $table->rememberToken();
    $table->timestamps();
});
```

---

## `create_user_profiles_table.php`

```php
Schema::create('user_profiles', function (Blueprint $table) {
    $table->uuid('user_id')->primary();
    $table->string('display_name', 150);
    $table->string('first_name')->nullable();
    $table->string('last_name')->nullable();
    $table->text('avatar_url')->nullable();
    $table->text('bio')->nullable();
    $table->string('city')->nullable();
    $table->string('state_region')->nullable();
    $table->string('country')->nullable();
    $table->string('default_visibility_level', 30)->default('private');
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
});
```

---

## `create_sports_table.php`

```php
Schema::create('sports', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('code', 30)->unique();
    $table->string('name', 100);
    $table->string('status', 30)->default('active');
    $table->timestamp('created_at')->useCurrent();
});
```

---

## `create_player_identities_table.php`

```php
Schema::create('player_identities', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('player_code', 32)->unique();
    $table->uuid('user_id')->nullable();
    $table->uuid('created_by_user_id')->nullable();
    $table->uuid('primary_sport_id')->nullable();
    $table->unsignedInteger('birth_year')->nullable();
    $table->string('claim_status', 30)->default('unclaimed');
    $table->string('identity_status', 30)->default('active');
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
    $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
    $table->foreign('primary_sport_id')->references('id')->on('sports')->nullOnDelete();

    $table->index('user_id');
});
```

---

## `create_coach_identities_table.php`

```php
Schema::create('coach_identities', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('coach_code', 32)->unique();
    $table->uuid('user_id');
    $table->string('identity_status', 30)->default('active');
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
    $table->unique('user_id');
});
```

---

## `create_guardian_relationships_table.php`

```php
Schema::create('guardian_relationships', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('guardian_user_id');
    $table->uuid('player_identity_id');
    $table->string('relationship_type', 50);
    $table->string('verification_status', 30)->default('pending');
    $table->boolean('is_primary_guardian')->default(false);
    $table->timestamps();

    $table->foreign('guardian_user_id')->references('id')->on('users')->cascadeOnDelete();
    $table->foreign('player_identity_id')->references('id')->on('player_identities')->cascadeOnDelete();
    $table->unique(['guardian_user_id', 'player_identity_id']);
});
```

---

## `create_organizations_table.php`

```php
Schema::create('organizations', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->string('organization_type', 50);
    $table->string('city')->nullable();
    $table->string('state_region')->nullable();
    $table->string('country')->nullable();
    $table->uuid('created_by_user_id')->nullable();
    $table->string('status', 30)->default('active');
    $table->timestamps();

    $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
});
```

---

## `create_teams_table.php`

```php
Schema::create('teams', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('team_code', 32)->unique();
    $table->uuid('sport_id');
    $table->uuid('organization_id')->nullable();
    $table->string('name');
    $table->string('nickname')->nullable();
    $table->string('season_label')->nullable();
    $table->string('age_group', 50)->nullable();
    $table->string('city')->nullable();
    $table->string('state_region')->nullable();
    $table->uuid('created_by_user_id')->nullable();
    $table->string('visibility_level', 30)->default('team');
    $table->string('status', 30)->default('active');
    $table->timestamps();

    $table->foreign('sport_id')->references('id')->on('sports');
    $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
    $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
});
```

---

## `create_team_memberships_table.php`

```php
Schema::create('team_memberships', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('team_id');
    $table->string('membership_type', 30);
    $table->uuid('user_id')->nullable();
    $table->uuid('player_identity_id')->nullable();
    $table->uuid('coach_identity_id')->nullable();
    $table->string('jersey_number', 20)->nullable();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->string('status', 30)->default('active');
    $table->uuid('created_by_user_id')->nullable();
    $table->timestamps();

    $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
    $table->foreign('player_identity_id')->references('id')->on('player_identities')->nullOnDelete();
    $table->foreign('coach_identity_id')->references('id')->on('coach_identities')->nullOnDelete();
    $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();

    $table->index(['team_id', 'membership_type', 'status']);
});
```

---

## `create_team_membership_positions_table.php`

```php
Schema::create('team_membership_positions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('team_membership_id');
    $table->string('position_code', 20);
    $table->timestamp('created_at')->useCurrent();

    $table->foreign('team_membership_id')
        ->references('id')
        ->on('team_memberships')
        ->cascadeOnDelete();
});
```

---

## `create_team_role_assignments_table.php`

```php
Schema::create('team_role_assignments', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('team_id');
    $table->uuid('user_id');
    $table->string('role_type', 30);
    $table->uuid('granted_by_user_id')->nullable();
    $table->string('status', 30)->default('active');
    $table->timestamps();

    $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
    $table->foreign('granted_by_user_id')->references('id')->on('users')->nullOnDelete();

    $table->unique(['team_id', 'user_id', 'role_type']);
});
```

---

## `create_audit_logs_table.php`

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('actor_user_id')->nullable();
    $table->string('action_type', 100);
    $table->string('object_type', 50);
    $table->uuid('object_id');
    $table->json('metadata_json')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
});
```

---

# 14. First Eloquent model list

## Example: `app/Models/PlayerIdentity.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlayerIdentity extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'player_code',
        'user_id',
        'created_by_user_id',
        'primary_sport_id',
        'birth_year',
        'claim_status',
        'identity_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'primary_sport_id');
    }

    public function guardianRelationships()
    {
        return $this->hasMany(GuardianRelationship::class);
    }
}
```

That pattern should be repeated for each model.

---

# 15. First Form Request classes

These are the first request validators I recommend.

## Auth
- `RegisterRequest`
- `LoginRequest`

## Identity
- `StorePlayerIdentityRequest`
- `ClaimPlayerIdentityRequest`
- `StoreCoachIdentityRequest`

## Guardian
- `StoreGuardianRelationshipRequest`

## Teams
- `StoreTeamRequest`
- `UpdateTeamRequest`
- `StoreTeamRoleRequest`
- `StoreRosterEntryRequest`
- `UpdateRosterEntryRequest`

---

## Example: `StoreTeamRequest`

```php
namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'sport_id' => ['required', 'uuid', 'exists:sports,id'],
            'organization_id' => ['nullable', 'uuid', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'season_label' => ['nullable', 'string', 'max:100'],
            'age_group' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'state_region' => ['nullable', 'string', 'max:100'],
        ];
    }
}
```

---

# 16. First Policies

These are critical.

## Recommended policy classes
- `PlayerIdentityPolicy`
- `GuardianRelationshipPolicy`
- `TeamPolicy`
- `TeamRoleAssignmentPolicy`
- `TeamMembershipPolicy`

---

## Example: `TeamPolicy`

```php
namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamRoleAssignment;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return TeamRoleAssignment::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function update(User $user, Team $team): bool
    {
        return TeamRoleAssignment::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('role_type', 'team_admin')
            ->where('status', 'active')
            ->exists();
    }

    public function manageRoster(User $user, Team $team): bool
    {
        return TeamRoleAssignment::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->whereIn('role_type', ['team_admin', 'coach'])
            ->where('status', 'active')
            ->exists();
    }

    public function assignRoles(User $user, Team $team): bool
    {
        return $this->update($user, $team);
    }
}
```

---

# 17. First Action classes

I recommend Action classes for workflows.

## Auth actions
- `RegisterUserAction`
- `LoginUserAction`

## Identity actions
- `CreatePlayerIdentityAction`
- `ClaimPlayerIdentityAction`
- `CreateCoachIdentityAction`

## Guardian actions
- `CreateGuardianRelationshipAction`

## Team actions
- `CreateTeamAction`
- `AssignTeamRoleAction`
- `AddRosterPlayerAction`
- `UpdateRosterMembershipAction`
- `DeactivateRosterMembershipAction`

---

## Example: `CreateTeamAction`

```php
namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\TeamRoleAssignment;
use App\Services\Codes\CodeGenerator;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class CreateTeamAction
{
    public function __construct(
        protected CodeGenerator $codes,
        protected AuditLogService $audit
    ) {}

    public function execute(array $data, string $actorUserId): Team
    {
        return DB::transaction(function () use ($data, $actorUserId) {
            $team = Team::create([
                'team_code' => $this->codes->generateTeamCode(),
                'sport_id' => $data['sport_id'],
                'organization_id' => $data['organization_id'] ?? null,
                'name' => $data['name'],
                'nickname' => $data['nickname'] ?? null,
                'season_label' => $data['season_label'] ?? null,
                'age_group' => $data['age_group'] ?? null,
                'city' => $data['city'] ?? null,
                'state_region' => $data['state_region'] ?? null,
                'created_by_user_id' => $actorUserId,
                'visibility_level' => 'team',
                'status' => 'active',
            ]);

            TeamRoleAssignment::create([
                'team_id' => $team->id,
                'user_id' => $actorUserId,
                'role_type' => 'team_admin',
                'granted_by_user_id' => $actorUserId,
                'status' => 'active',
            ]);

            $this->audit->record(
                actorUserId: $actorUserId,
                actionType: 'team.created',
                objectType: 'team',
                objectId: $team->id,
                metadata: []
            );

            return $team;
        });
    }
}
```

---

# 18. First service classes

These are support services, not main domain workflows.

## Recommended
- `CodeGenerator`
- `AuditLogService`
- `AgeService`

---

## Example: `CodeGenerator`

```php
namespace App\Services\Codes;

use Illuminate\Support\Str;

class CodeGenerator
{
    public function generateBulldogUserCode(): string
    {
        return 'BDG-' . strtoupper(Str::random(8));
    }

    public function generatePlayerCode(): string
    {
        return 'PLY-' . strtoupper(Str::random(8));
    }

    public function generateCoachCode(): string
    {
        return 'COA-' . strtoupper(Str::random(8));
    }

    public function generateTeamCode(): string
    {
        return 'TEM-' . strtoupper(Str::random(8));
    }
}
```

If you want more collision protection, loop until uniqueness is verified.

---

# 19. First controllers

## Recommended API controllers
- `RegisterController`
- `LoginController`
- `MeController`
- `PlayerIdentityController`
- `ClaimPlayerIdentityController`
- `CoachIdentityController`
- `GuardianRelationshipController`
- `TeamController`
- `TeamRoleController`
- `RosterController`

---

## Example: `TeamController`

```php
namespace App\Http\Controllers\Api\Teams;

use App\Actions\Teams\CreateTeamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Resources\Teams\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::query()
            ->whereHas('roleAssignments', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                  ->where('status', 'active');
            })
            ->latest()
            ->get();

        return TeamResource::collection($teams);
    }

    public function store(StoreTeamRequest $request, CreateTeamAction $action)
    {
        $team = $action->execute(
            $request->validated(),
            $request->user()->id
        );

        return (new TeamResource($team))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        return new TeamResource($team->load('sport', 'organization'));
    }
}
```

---

# 20. First API Resources

## Recommended resources
- `UserResource`
- `MeResource`
- `PlayerIdentityResource`
- `GuardianRelationshipResource`
- `TeamResource`
- `TeamRoleAssignmentResource`
- `RosterEntryResource`

These help keep response shapes stable.

---

## Example: `TeamResource`

```php
namespace App\Http\Resources\Teams;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'team_code' => $this->team_code,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'season_label' => $this->season_label,
            'age_group' => $this->age_group,
            'city' => $this->city,
            'state_region' => $this->state_region,
            'visibility_level' => $this->visibility_level,
            'status' => $this->status,
            'sport' => $this->whenLoaded('sport', fn () => [
                'id' => $this->sport->id,
                'code' => $this->sport->code,
                'name' => $this->sport->name,
            ]),
            'organization' => $this->whenLoaded('organization', fn () => [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ]),
        ];
    }
}
```

---

# 21. First Blade pages

For the first website/app experience, I recommend these pages:

## Public/basic
- welcome / landing page for app subdomain
- login
- register

## Authenticated
- dashboard
- profile
- teams index
- create team
- team detail
- team roster
- team roles

Keep them simple and functional.

---

# 22. Suggested Blade structure

```text
resources/views/
  layouts/
    app.blade.php
    guest.blade.php
  auth/
    login.blade.php
    register.blade.php
  dashboard.blade.php
  profile/
    index.blade.php
  teams/
    index.blade.php
    create.blade.php
    show.blade.php
    partials/
      roster-table.blade.php
      roles-table.blade.php
```

---

# 23. Suggested CSS/frontend strategy

Keep it simple and fast.

## Recommendation
Use:
- Laravel’s standard frontend scaffolding
- Tailwind if comfortable
- responsive card/table layouts

This is sufficient for beta and very compatible with home-screen use.

---

# 24. First seeders

## `SportSeeder`
Seed:
- baseball
- softball

## Optional later
- visibility defaults
- achievement definitions
- role labels

---

## Example: `SportSeeder`

```php
namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        $sports = [
            ['code' => 'baseball', 'name' => 'Baseball'],
            ['code' => 'softball', 'name' => 'Softball'],
        ];

        foreach ($sports as $sport) {
            Sport::updateOrCreate(
                ['code' => $sport['code']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $sport['name'],
                    'status' => 'active',
                ]
            );
        }
    }
}
```

---

# 25. First feature tests

These should be built early.

## Auth
- user can register
- user can login
- current session endpoint works

## Player identity
- user can create player identity
- user can claim unclaimed player identity
- claimed identity cannot be hijacked

## Teams
- user can create team
- team creator becomes team admin
- team admin can assign role
- non-admin cannot assign role

## Roster
- admin/coach can add existing player
- admin/coach can create unclaimed player inline
- unauthorized user cannot modify roster

## Guardian
- guardian can create relationship
- unrelated user cannot access relationship list

---

# 26. Suggested first sprint file map

Here is the first concrete slice I’d want Codex to create.

## Models
- `User.php`
- `UserProfile.php`
- `Sport.php`
- `PlayerIdentity.php`
- `CoachIdentity.php`
- `GuardianRelationship.php`
- `Organization.php`
- `Team.php`
- `TeamMembership.php`
- `TeamMembershipPosition.php`
- `TeamRoleAssignment.php`
- `AuditLog.php`

## Actions
- `RegisterUserAction.php`
- `CreatePlayerIdentityAction.php`
- `ClaimPlayerIdentityAction.php`
- `CreateTeamAction.php`
- `AssignTeamRoleAction.php`
- `AddRosterPlayerAction.php`

## Services
- `CodeGenerator.php`
- `AuditLogService.php`
- `AgeService.php`

## Requests
- `RegisterRequest.php`
- `LoginRequest.php`
- `StorePlayerIdentityRequest.php`
- `StoreTeamRequest.php`
- `StoreTeamRoleRequest.php`
- `StoreRosterEntryRequest.php`

## Controllers
- `RegisterController.php`
- `LoginController.php`
- `MeController.php`
- `PlayerIdentityController.php`
- `ClaimPlayerIdentityController.php`
- `TeamController.php`
- `TeamRoleController.php`
- `RosterController.php`

## Policies
- `PlayerIdentityPolicy.php`
- `TeamPolicy.php`
- `TeamRoleAssignmentPolicy.php`
- `TeamMembershipPolicy.php`

## Resources
- `MeResource.php`
- `PlayerIdentityResource.php`
- `TeamResource.php`
- `TeamRoleAssignmentResource.php`
- `RosterEntryResource.php`

---

# 27. Laravel auth recommendation

For beta, keep auth straightforward.

## Recommendation
Use:
- **Laravel Breeze** or equivalent lightweight auth foundation
- session-based auth for web app
- Sanctum if you want API auth cleanly too

If your app is primarily same-domain web app usage, Laravel’s built-in auth flow is enough to start.

---

# 28. Deployment interpretation for your use case

This architecture supports:

## Browser app
User visits:
- `statbook.yourdomain.com`

## Home-screen app
User opens same URL on iPhone/iPad and saves it to home screen.

## Beta updates
You deploy server-side changes and users get updates without app store friction.

That is exactly aligned with the deployment pattern you described.

---

# 29. Immediate build order

Here is the best stack-specific order now.

## Step 1
Create Laravel app skeleton

## Step 2
Set up MySQL connection to DreamHost beta DB

## Step 3
Add UUID support and base model pattern

## Step 4
Create migrations:
- users/profile
- sports
- identities
- teams
- roles
- memberships
- audit

## Step 5
Seed sports

## Step 6
Build auth endpoints and pages

## Step 7
Build player identity endpoints

## Step 8
Build team create/list/detail

## Step 9
Build team roles

## Step 10
Build roster add/list/update/deactivate

That is the correct first slice.

---

# 30. What I recommend next

Now that the Laravel stack is chosen, the most useful next deliverable is:

## Option A
A **Laravel-specific Codex build prompt pack** for Phase 1 + 2

## Option B
Actual **Laravel file stubs** for the first core files

## Option C
A **DreamHost deployment checklist** for Laravel on a subdomain

---

# My recommendation
Do these next in order:

1. **Laravel-specific Codex build prompt pack**
2. **DreamHost deployment checklist**
3. **Laravel file stubs**

If you want, I can start immediately with:

# Bulldog Laravel Codex Build Prompt Pack v1