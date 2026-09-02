Great — proceeding in that order.

# Next up
1. **Option A** — remaining Laravel stubs  
   - `CoachIdentity`
   - `GuardianRelationship`
   - related requests/controllers/resources/actions/models
2. **Option B** — Feature test stubs
3. **Option C** — first local Laravel setup guide

---

# Option A  
## Remaining Laravel Stubs for Identity + Guardian

---

## 1. Model stubs

### `app/Models/CoachIdentity.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CoachIdentity extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'coach_code',
        'user_id',
        'identity_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

### `app/Models/GuardianRelationship.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GuardianRelationship extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'guardian_user_id',
        'player_identity_id',
        'relationship_type',
        'verification_status',
        'is_primary_guardian',
    ];

    protected $casts = [
        'is_primary_guardian' => 'boolean',
    ];

    public function guardianUser()
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    public function playerIdentity()
    {
        return $this->belongsTo(PlayerIdentity::class);
    }
}
```

---

## 2. Action stubs

### `app/Actions/Identity/CreateCoachIdentityAction.php`

```php
<?php

namespace App\Actions\Identity;

use App\Models\CoachIdentity;
use App\Services\Audit\AuditLogService;
use App\Services\Codes\CodeGenerator;
use Illuminate\Validation\ValidationException;

class CreateCoachIdentityAction
{
    public function __construct(
        protected CodeGenerator $codes,
        protected AuditLogService $audit
    ) {}

    public function execute(string $actorUserId): CoachIdentity
    {
        $existing = CoachIdentity::query()
            ->where('user_id', $actorUserId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $coach = CoachIdentity::create([
            'coach_code' => $this->codes->generateCoachCode(),
            'user_id' => $actorUserId,
            'identity_status' => 'active',
        ]);

        $this->audit->record(
            actorUserId: $actorUserId,
            actionType: 'coach_identity.created',
            objectType: 'coach_identity',
            objectId: $coach->id
        );

        return $coach;
    }
}
```

---

### `app/Actions/Guardian/CreateGuardianRelationshipAction.php`

```php
<?php

namespace App\Actions\Guardian;

use App\Models\GuardianRelationship;
use App\Services\Audit\AuditLogService;
use Illuminate\Validation\ValidationException;

class CreateGuardianRelationshipAction
{
    public function __construct(
        protected AuditLogService $audit
    ) {}

    public function execute(array $data, string $actorUserId): GuardianRelationship
    {
        $existing = GuardianRelationship::query()
            ->where('guardian_user_id', $actorUserId)
            ->where('player_identity_id', $data['player_identity_id'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'player_identity_id' => 'Guardian relationship already exists for this player.',
            ]);
        }

        $relationship = GuardianRelationship::create([
            'guardian_user_id' => $actorUserId,
            'player_identity_id' => $data['player_identity_id'],
            'relationship_type' => $data['relationship_type'],
            'verification_status' => 'pending',
            'is_primary_guardian' => $data['is_primary_guardian'] ?? false,
        ]);

        $this->audit->record(
            actorUserId: $actorUserId,
            actionType: 'guardian_relationship.created',
            objectType: 'guardian_relationship',
            objectId: $relationship->id,
            metadata: [
                'player_identity_id' => $data['player_identity_id'],
                'relationship_type' => $data['relationship_type'],
            ]
        );

        return $relationship->load('playerIdentity');
    }
}
```

---

## 3. Request stubs

### `app/Http/Requests/Identity/StoreCoachIdentityRequest.php`

```php
<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [];
    }
}
```

---

### `app/Http/Requests/Guardian/StoreGuardianRelationshipRequest.php`

```php
<?php

namespace App\Http\Requests\Guardian;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuardianRelationshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'player_identity_id' => ['required', 'uuid', 'exists:player_identities,id'],
            'relationship_type' => ['required', 'string', 'max:50'],
            'is_primary_guardian' => ['nullable', 'boolean'],
        ];
    }
}
```

---

## 4. Controller stubs

### `app/Http/Controllers/Api/Identity/CoachIdentityController.php`

```php
<?php

namespace App\Http\Controllers\Api\Identity;

use App\Actions\Identity\CreateCoachIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreCoachIdentityRequest;
use App\Http\Resources\Identity\CoachIdentityResource;

class CoachIdentityController extends Controller
{
    public function store(StoreCoachIdentityRequest $request, CreateCoachIdentityAction $action)
    {
        $coach = $action->execute($request->user()->id);

        return (new CoachIdentityResource($coach))
            ->response()
            ->setStatusCode(201);
    }
}
```

---

### `app/Http/Controllers/Api/Guardian/GuardianRelationshipController.php`

```php
<?php

namespace App\Http\Controllers\Api\Guardian;

use App\Actions\Guardian\CreateGuardianRelationshipAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\StoreGuardianRelationshipRequest;
use App\Http\Resources\Guardian\GuardianRelationshipResource;
use App\Models\GuardianRelationship;
use Illuminate\Http\Request;

class GuardianRelationshipController extends Controller
{
    public function index(Request $request)
    {
        $relationships = GuardianRelationship::query()
            ->where('guardian_user_id', $request->user()->id)
            ->with('playerIdentity')
            ->latest()
            ->get();

        return GuardianRelationshipResource::collection($relationships);
    }

    public function store(
        StoreGuardianRelationshipRequest $request,
        CreateGuardianRelationshipAction $action
    ) {
        $relationship = $action->execute(
            $request->validated(),
            $request->user()->id
        );

        return (new GuardianRelationshipResource($relationship))
            ->response()
            ->setStatusCode(201);
    }
}
```

---

## 5. Resource stubs

### `app/Http/Resources/Identity/CoachIdentityResource.php`

```php
<?php

namespace App\Http\Resources\Identity;

use Illuminate\Http\Resources\Json\JsonResource;

class CoachIdentityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'coach_code' => $this->coach_code,
            'user_id' => $this->user_id,
            'identity_status' => $this->identity_status,
        ];
    }
}
```

---

### `app/Http/Resources/Guardian/GuardianRelationshipResource.php`

```php
<?php

namespace App\Http\Resources\Guardian;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardianRelationshipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guardian_user_id' => $this->guardian_user_id,
            'relationship_type' => $this->relationship_type,
            'verification_status' => $this->verification_status,
            'is_primary_guardian' => $this->is_primary_guardian,
            'player' => $this->playerIdentity ? [
                'id' => $this->playerIdentity->id,
                'player_code' => $this->playerIdentity->player_code,
                'claim_status' => $this->playerIdentity->claim_status,
                'birth_year' => $this->playerIdentity->birth_year,
            ] : null,
        ];
    }
}
```

---

## 6. Policy stub

### `app/Policies/GuardianRelationshipPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\GuardianRelationship;
use App\Models\User;

class GuardianRelationshipPolicy
{
    public function view(User $user, GuardianRelationship $relationship): bool
    {
        return $relationship->guardian_user_id === $user->id;
    }
}
```

---

## 7. Update `MeResource` to include coach identity

### Revised `app/Http/Resources/Auth/MeResource.php`

```php
<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'bulldog_user_code' => $this->bulldog_user_code,
                'email' => $this->email,
                'is_minor' => $this->is_minor,
                'account_status' => $this->account_status,
            ],
            'profile' => $this->profile ? [
                'display_name' => $this->profile->display_name,
                'avatar_url' => $this->profile->avatar_url,
                'default_visibility_level' => $this->profile->default_visibility_level,
            ] : null,
            'identities' => [
                'player' => $this->playerIdentity ? [
                    'id' => $this->playerIdentity->id,
                    'player_code' => $this->playerIdentity->player_code,
                    'claim_status' => $this->playerIdentity->claim_status,
                ] : null,
                'coach' => $this->coachIdentity ? [
                    'id' => $this->coachIdentity->id,
                    'coach_code' => $this->coachIdentity->coach_code,
                    'identity_status' => $this->coachIdentity->identity_status,
                ] : null,
            ],
            'team_roles' => $this->teamRoleAssignments->map(fn ($role) => [
                'team_id' => $role->team_id,
                'role_type' => $role->role_type,
                'status' => $role->status,
            ]),
        ];
    }
}
```

---

## 8. Update `User` model for coach identity + guardianship relationships

### Revised `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasNotifiable, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'bulldog_user_code',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'is_minor',
        'account_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_minor' => 'boolean',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function playerIdentity()
    {
        return $this->hasOne(PlayerIdentity::class);
    }

    public function coachIdentity()
    {
        return $this->hasOne(CoachIdentity::class);
    }

    public function guardianRelationships()
    {
        return $this->hasMany(GuardianRelationship::class, 'guardian_user_id');
    }

    public function teamRoleAssignments()
    {
        return $this->hasMany(TeamRoleAssignment::class);
    }
}
```

---

## 9. Update routes

### Revised `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Guardian\GuardianRelationshipController;
use App\Http\Controllers\Api\Identity\ClaimPlayerIdentityController;
use App\Http\Controllers\Api\Identity\CoachIdentityController;
use App\Http\Controllers\Api\Identity\PlayerIdentityController;
use App\Http\Controllers\Api\Teams\RosterController;
use App\Http\Controllers\Api\Teams\TeamController;
use App\Http\Controllers\Api\Teams\TeamRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', RegisterController::class);
    Route::post('/auth/login', LoginController::class);

    Route::middleware('auth')->group(function () {
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

        Route::get('/teams/{team}/roles', [TeamRoleController::class, 'index']);
        Route::post('/teams/{team}/roles', [TeamRoleController::class, 'store']);
        Route::delete('/teams/{team}/roles/{assignment}', [TeamRoleController::class, 'destroy']);

        Route::get('/teams/{team}/roster', [RosterController::class, 'index']);
        Route::post('/teams/{team}/roster', [RosterController::class, 'store']);
        Route::delete('/teams/{team}/roster/{membership}', [RosterController::class, 'destroy']);
    });
});
```

---

# Option B  
## Feature Test Stubs

These are starter test skeletons.

---

## `tests/Feature/Auth/RegisterTest.php`

```php
<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'display_name' => 'Test User',
            'date_of_birth' => '2010-01-01',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'display_name' => 'Test User',
        ]);
    }
}
```

---

## `tests/Feature/Auth/LoginTest.php`

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
    }
}
```

---

## `tests/Feature/Identity/CreatePlayerIdentityTest.php`

```php
<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePlayerIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_player_identity(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/players', [
            'birth_year' => 2012,
            'context' => 'self',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('player_identities', 1);
    }
}
```

---

## `tests/Feature/Identity/ClaimPlayerIdentityTest.php`

```php
<?php

namespace Tests\Feature\Identity;

use App\Models\PlayerIdentity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimPlayerIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_claim_unclaimed_player_identity(): void
    {
        $user = User::factory()->create();
        $player = PlayerIdentity::factory()->create([
            'user_id' => null,
            'claim_status' => 'unclaimed',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/players/{$player->id}/claim");

        $response->assertStatus(200);

        $this->assertDatabaseHas('player_identities', [
            'id' => $player->id,
            'user_id' => $user->id,
            'claim_status' => 'claimed',
        ]);
    }

    public function test_claimed_player_identity_cannot_be_hijacked(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $player = PlayerIdentity::factory()->create([
            'user_id' => $owner->id,
            'claim_status' => 'claimed',
        ]);

        $response = $this->actingAs($other)
            ->postJson("/api/v1/players/{$player->id}/claim");

        $response->assertStatus(422);
    }
}
```

---

## `tests/Feature/Teams/CreateTeamTest.php`

```php
<?php

namespace Tests\Feature\Teams;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_team_and_becomes_team_admin(): void
    {
        $user = User::factory()->create();
        $sport = Sport::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/teams', [
            'sport_id' => $sport->id,
            'name' => 'Bulldogs 12U',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('team_role_assignments', [
            'user_id' => $user->id,
            'role_type' => 'team_admin',
            'status' => 'active',
        ]);
    }
}
```

---

## `tests/Feature/Teams/AssignTeamRoleTest.php`

```php
<?php

namespace Tests\Feature\Teams;

use App\Models\Sport;
use App\Models\Team;
use App\Models\TeamRoleAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignTeamRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_admin_can_assign_team_role(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();
        $sport = Sport::factory()->create();
        $team = Team::factory()->create(['sport_id' => $sport->id]);

        TeamRoleAssignment::factory()->create([
            'team_id' => $team->id,
            'user_id' => $admin->id,
            'role_type' => 'team_admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/teams/{$team->id}/roles", [
            'user_id' => $target->id,
            'role_type' => 'coach',
        ]);

        $response->assertStatus(201);
    }

    public function test_non_admin_cannot_assign_team_role(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();
        $sport = Sport::factory()->create();
        $team = Team::factory()->create(['sport_id' => $sport->id]);

        $response = $this->actingAs($user)->postJson("/api/v1/teams/{$team->id}/roles", [
            'user_id' => $target->id,
            'role_type' => 'coach',
        ]);

        $response->assertStatus(403);
    }
}
```

---

## `tests/Feature/Teams/RosterTest.php`

```php
<?php

namespace Tests\Feature\Teams;

use App\Models\PlayerIdentity;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TeamRoleAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_admin_can_add_existing_player_to_roster(): void
    {
        $admin = User::factory()->create();
        $sport = Sport::factory()->create();
        $team = Team::factory()->create(['sport_id' => $sport->id]);
        $player = PlayerIdentity::factory()->create();

        TeamRoleAssignment::factory()->create([
            'team_id' => $team->id,
            'user_id' => $admin->id,
            'role_type' => 'team_admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/teams/{$team->id}/roster", [
            'player_id' => $player->id,
            'jersey_number' => '12',
            'positions' => ['P', '1B'],
        ]);

        $response->assertStatus(201);
    }

    public function test_team_admin_can_create_unclaimed_player_inline(): void
    {
        $admin = User::factory()->create();
        $sport = Sport::factory()->create();
        $team = Team::factory()->create(['sport_id' => $sport->id]);

        TeamRoleAssignment::factory()->create([
            'team_id' => $team->id,
            'user_id' => $admin->id,
            'role_type' => 'team_admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/teams/{$team->id}/roster", [
            'create_player' => [
                'birth_year' => 2013,
            ],
            'jersey_number' => '7',
            'positions' => ['SS'],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('player_identities', [
            'claim_status' => 'unclaimed',
        ]);
    }

    public function test_unauthorized_user_cannot_manage_roster(): void
    {
        $user = User::factory()->create();
        $sport = Sport::factory()->create();
        $team = Team::factory()->create(['sport_id' => $sport->id]);
        $player = PlayerIdentity::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/teams/{$team->id}/roster", [
            'player_id' => $player->id,
        ]);

        $response->assertStatus(403);
    }
}
```

---

## `tests/Feature/Guardian/GuardianRelationshipTest.php`

```php
<?php

namespace Tests\Feature\Guardian;

use App\Models\GuardianRelationship;
use App\Models\PlayerIdentity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_guardian_relationship(): void
    {
        $user = User::factory()->create();
        $player = PlayerIdentity::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/guardians/relationships', [
            'player_identity_id' => $player->id,
            'relationship_type' => 'guardian',
            'is_primary_guardian' => true,
        ]);

        $response->assertStatus(201);
    }

    public function test_user_only_sees_own_guardian_relationships(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $player = PlayerIdentity::factory()->create();

        GuardianRelationship::factory()->create([
            'guardian_user_id' => $owner->id,
            'player_identity_id' => $player->id,
        ]);

        $response = $this->actingAs($other)->getJson('/api/v1/guardians/relationships');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
```

---

# Option C  
## First Local Laravel Setup Guide

Here is the cleanest first local setup path.

---

## 1. Install/create Laravel app
If not already created:

```bash
composer create-project laravel/laravel bulldog-statbook
cd bulldog-statbook
```

---

## 2. Create local `.env`
Copy env file:

```bash
cp .env.example .env
```

Then set local DB values, for example:

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

## 3. Generate app key

```bash
php artisan key:generate
```

---

## 4. Create local database
Create:
- `bulldog_local`

in your local MySQL instance.

---

## 5. Add migrations/models/stubs
Add the files we created.

---

## 6. Run migrations

```bash
php artisan migrate
```

---

## 7. Run seeders

```bash
php artisan db:seed
```

---

## 8. Serve app locally

```bash
php artisan serve
```

Then open:
- `http://localhost:8000`

---

## 9. Run tests

```bash
php artisan test
```

---

## 10. Recommended local milestone order
1. app boots
2. DB connects
3. migrations succeed
4. sports seed works
5. registration works
6. team creation works
7. role assignment works
8. roster add works
9. guardian relationship works

---

# Recommended next step

The next very useful thing would be:

## Option D
Generate **model factories** for the tests

or

## Option E
Generate **AuthServiceProvider / policy registration / middleware notes**

or

## Option F
Generate **Blade page stubs** for the first app screens

### My recommendation
Do this next in order:
1. **Option D** — model factories
2. **Option E** — policy registration and auth wiring
3. **Option F** — Blade page stubs

If you want, I can continue immediately with **Option D**.