# Bulldog Laravel File Stubs v1  
## First Critical Files for Phase 1 + 2

These are **starter stubs**, not full production-complete files.

Their job is to give Codex a clean, stack-specific starting point for:

- Laravel
- MySQL beta
- future Postgres portability
- identity/team/roster foundation

I’ll include the most important files first.

---

# 1. Base assumptions

These stubs assume:

- Laravel app already created
- auth scaffolding will exist or be added
- API-first structure under `app/Http/Controllers/Api`
- UUIDs used on core models
- business logic placed in Actions/Services
- controllers kept thin

---

# 2. Suggested first file set

## Models
- `app/Models/User.php`
- `app/Models/UserProfile.php`
- `app/Models/Sport.php`
- `app/Models/PlayerIdentity.php`
- `app/Models/Team.php`
- `app/Models/TeamRoleAssignment.php`
- `app/Models/TeamMembership.php`
- `app/Models/AuditLog.php`

## Services
- `app/Services/Codes/CodeGenerator.php`
- `app/Services/Audit/AuditLogService.php`
- `app/Services/AgeService.php`

## Actions
- `app/Actions/Auth/RegisterUserAction.php`
- `app/Actions/Identity/CreatePlayerIdentityAction.php`
- `app/Actions/Identity/ClaimPlayerIdentityAction.php`
- `app/Actions/Teams/CreateTeamAction.php`
- `app/Actions/Teams/AssignTeamRoleAction.php`
- `app/Actions/Roster/AddRosterPlayerAction.php`

## Requests
- `app/Http/Requests/Auth/RegisterRequest.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Identity/StorePlayerIdentityRequest.php`
- `app/Http/Requests/Teams/StoreTeamRequest.php`
- `app/Http/Requests/Teams/StoreTeamRoleRequest.php`
- `app/Http/Requests/Teams/StoreRosterEntryRequest.php`

## Controllers
- `app/Http/Controllers/Api/Auth/RegisterController.php`
- `app/Http/Controllers/Api/Auth/LoginController.php`
- `app/Http/Controllers/Api/Auth/MeController.php`
- `app/Http/Controllers/Api/Identity/PlayerIdentityController.php`
- `app/Http/Controllers/Api/Identity/ClaimPlayerIdentityController.php`
- `app/Http/Controllers/Api/Teams/TeamController.php`
- `app/Http/Controllers/Api/Teams/TeamRoleController.php`
- `app/Http/Controllers/Api/Teams/RosterController.php`

## Policies
- `app/Policies/TeamPolicy.php`
- `app/Policies/PlayerIdentityPolicy.php`

## Resources
- `app/Http/Resources/Auth/MeResource.php`
- `app/Http/Resources/Identity/PlayerIdentityResource.php`
- `app/Http/Resources/Teams/TeamResource.php`
- `app/Http/Resources/Teams/TeamRoleAssignmentResource.php`
- `app/Http/Resources/Teams/RosterEntryResource.php`

## Seeder
- `database/seeders/SportSeeder.php`

## Routes
- `routes/api.php`

---

# 3. Core model stubs

---

## `app/Models/User.php`

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

    public function teamRoleAssignments()
    {
        return $this->hasMany(TeamRoleAssignment::class);
    }
}
```

---

## `app/Models/UserProfile.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'display_name',
        'first_name',
        'last_name',
        'avatar_url',
        'bio',
        'city',
        'state_region',
        'country',
        'default_visibility_level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## `app/Models/Sport.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Sport extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'status',
        'created_at',
    ];
}
```

---

## `app/Models/PlayerIdentity.php`

```php
<?php

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

    public function teamMemberships()
    {
        return $this->hasMany(TeamMembership::class);
    }
}
```

---

## `app/Models/Team.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Team extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'team_code',
        'sport_id',
        'organization_id',
        'name',
        'nickname',
        'season_label',
        'age_group',
        'city',
        'state_region',
        'created_by_user_id',
        'visibility_level',
        'status',
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function roleAssignments()
    {
        return $this->hasMany(TeamRoleAssignment::class);
    }

    public function memberships()
    {
        return $this->hasMany(TeamMembership::class);
    }
}
```

---

## `app/Models/TeamRoleAssignment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TeamRoleAssignment extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'team_id',
        'user_id',
        'role_type',
        'granted_by_user_id',
        'status',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }
}
```

---

## `app/Models/TeamMembership.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TeamMembership extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'team_id',
        'membership_type',
        'user_id',
        'player_identity_id',
        'coach_identity_id',
        'jersey_number',
        'start_date',
        'end_date',
        'status',
        'created_by_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function playerIdentity()
    {
        return $this->belongsTo(PlayerIdentity::class);
    }

    public function positions()
    {
        return $this->hasMany(TeamMembershipPosition::class);
    }
}
```

---

## `app/Models/TeamMembershipPosition.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TeamMembershipPosition extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'team_membership_id',
        'position_code',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function membership()
    {
        return $this->belongsTo(TeamMembership::class, 'team_membership_id');
    }
}
```

---

## `app/Models/AuditLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'actor_user_id',
        'action_type',
        'object_type',
        'object_id',
        'metadata_json',
        'created_at',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
```

---

# 4. Support service stubs

---

## `app/Services/Codes/CodeGenerator.php`

```php
<?php

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

---

## `app/Services/Audit/AuditLogService.php`

```php
<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Str;

class AuditLogService
{
    public function record(
        ?string $actorUserId,
        string $actionType,
        string $objectType,
        string $objectId,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'id' => (string) Str::uuid(),
            'actor_user_id' => $actorUserId,
            'action_type' => $actionType,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'metadata_json' => $metadata,
            'created_at' => now(),
        ]);
    }
}
```

---

## `app/Services/AgeService.php`

```php
<?php

namespace App\Services;

use Carbon\Carbon;

class AgeService
{
    public function isMinor(?string $dateOfBirth): bool
    {
        if (!$dateOfBirth) {
            return false;
        }

        return Carbon::parse($dateOfBirth)->age < 18;
    }
}
```

---

# 5. Action stubs

---

## `app/Actions/Auth/RegisterUserAction.php`

```php
<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\AgeService;
use App\Services\Audit\AuditLogService;
use App\Services\Codes\CodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function __construct(
        protected CodeGenerator $codes,
        protected AgeService $ages,
        protected AuditLogService $audit
    ) {}

    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'bulldog_user_code' => $this->codes->generateBulldogUserCode(),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'is_minor' => $this->ages->isMinor($data['date_of_birth'] ?? null),
                'account_status' => 'active',
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'display_name' => $data['display_name'],
                'default_visibility_level' => 'private',
            ]);

            $this->audit->record(
                actorUserId: $user->id,
                actionType: 'user.registered',
                objectType: 'user',
                objectId: $user->id
            );

            return $user->load('profile');
        });
    }
}
```

---

## `app/Actions/Identity/CreatePlayerIdentityAction.php`

```php
<?php

namespace App\Actions\Identity;

use App\Models\PlayerIdentity;
use App\Services\Audit\AuditLogService;
use App\Services\Codes\CodeGenerator;

class CreatePlayerIdentityAction
{
    public function __construct(
        protected CodeGenerator $codes,
        protected AuditLogService $audit
    ) {}

    public function execute(array $data, string $actorUserId): PlayerIdentity
    {
        $player = PlayerIdentity::create([
            'player_code' => $this->codes->generatePlayerCode(),
            'user_id' => $data['user_id'] ?? null,
            'created_by_user_id' => $actorUserId,
            'primary_sport_id' => $data['primary_sport_id'] ?? null,
            'birth_year' => $data['birth_year'] ?? null,
            'claim_status' => !empty($data['user_id']) ? 'claimed' : 'unclaimed',
            'identity_status' => 'active',
        ]);

        $this->audit->record(
            actorUserId: $actorUserId,
            actionType: 'player_identity.created',
            objectType: 'player_identity',
            objectId: $player->id,
            metadata: ['context' => $data['context'] ?? 'self']
        );

        return $player;
    }
}
```

---

## `app/Actions/Identity/ClaimPlayerIdentityAction.php`

```php
<?php

namespace App\Actions\Identity;

use App\Models\PlayerIdentity;
use App\Services\Audit\AuditLogService;
use Illuminate\Validation\ValidationException;

class ClaimPlayerIdentityAction
{
    public function __construct(
        protected AuditLogService $audit
    ) {}

    public function execute(PlayerIdentity $player, string $actorUserId): PlayerIdentity
    {
        if ($player->user_id && $player->user_id !== $actorUserId) {
            throw ValidationException::withMessages([
                'player' => 'This player identity has already been claimed.',
            ]);
        }

        if ($player->user_id === $actorUserId) {
            return $player;
        }

        $player->update([
            'user_id' => $actorUserId,
            'claim_status' => 'claimed',
        ]);

        $this->audit->record(
            actorUserId: $actorUserId,
            actionType: 'player_identity.claimed',
            objectType: 'player_identity',
            objectId: $player->id
        );

        return $player->fresh();
    }
}
```

---

## `app/Actions/Teams/CreateTeamAction.php`

```php
<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\TeamRoleAssignment;
use App\Services\Audit\AuditLogService;
use App\Services\Codes\CodeGenerator;
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
                objectId: $team->id
            );

            return $team;
        });
    }
}
```

---

## `app/Actions/Teams/AssignTeamRoleAction.php`

```php
<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\TeamRoleAssignment;
use App\Services\Audit\AuditLogService;
use Illuminate\Validation\ValidationException;

class AssignTeamRoleAction
{
    public function __construct(
        protected AuditLogService $audit
    ) {}

    public function execute(Team $team, array $data, string $actorUserId): TeamRoleAssignment
    {
        $exists = TeamRoleAssignment::query()
            ->where('team_id', $team->id)
            ->where('user_id', $data['user_id'])
            ->where('role_type', $data['role_type'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'role' => 'This role is already assigned.',
            ]);
        }

        $assignment = TeamRoleAssignment::create([
            'team_id' => $team->id,
            'user_id' => $data['user_id'],
            'role_type' => $data['role_type'],
            'granted_by_user_id' => $actorUserId,
            'status' => 'active',
        ]);

        $this->audit->record(
            actorUserId: $actorUserId,
            actionType: 'team_role.assigned',
            objectType: 'team_role_assignment',
            objectId: $assignment->id,
            metadata: [
                'team_id' => $team->id,
                'target_user_id' => $data['user_id'],
                'role_type' => $data['role_type'],
            ]
        );

        return $assignment;
    }
}
```

---

## `app/Actions/Roster/AddRosterPlayerAction.php`

```php
<?php

namespace App\Actions\Roster;

use App\Actions\Identity\CreatePlayerIdentityAction;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\TeamMembershipPosition;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AddRosterPlayerAction
{
    public function __construct(
        protected CreatePlayerIdentityAction $createPlayerIdentity,
        protected AuditLogService $audit
    ) {}

    public function execute(Team $team, array $data, string $actorUserId): TeamMembership
    {
        return DB::transaction(function () use ($team, $data, $actorUserId) {
            $playerIdentityId = $data['player_id'] ?? null;

            if (!$playerIdentityId && !empty($data['create_player'])) {
                $player = $this->createPlayerIdentity->execute([
                    'user_id' => null,
                    'primary_sport_id' => $team->sport_id,
                    'birth_year' => $data['create_player']['birth_year'] ?? null,
                    'context' => 'team_roster',
                ], $actorUserId);

                $playerIdentityId = $player->id;
            }

            if (!$playerIdentityId) {
                throw ValidationException::withMessages([
                    'player' => 'Either player_id or create_player is required.',
                ]);
            }

            $existing = TeamMembership::query()
                ->where('team_id', $team->id)
                ->where('player_identity_id', $playerIdentityId)
                ->where('membership_type', 'player')
                ->where('status', 'active')
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages([
                    'player' => 'Player is already on this roster.',
                ]);
            }

            $membership = TeamMembership::create([
                'team_id' => $team->id,
                'membership_type' => 'player',
                'player_identity_id' => $playerIdentityId,
                'jersey_number' => $data['jersey_number'] ?? null,
                'status' => 'active',
                'created_by_user_id' => $actorUserId,
            ]);

            foreach (($data['positions'] ?? []) as $positionCode) {
                TeamMembershipPosition::create([
                    'id' => (string) Str::uuid(),
                    'team_membership_id' => $membership->id,
                    'position_code' => $positionCode,
                    'created_at' => now(),
                ]);
            }

            $this->audit->record(
                actorUserId: $actorUserId,
                actionType: 'team_roster.player_added',
                objectType: 'team_membership',
                objectId: $membership->id,
                metadata: [
                    'team_id' => $team->id,
                    'player_identity_id' => $playerIdentityId,
                ]
            );

            return $membership->load('playerIdentity', 'positions');
        });
    }
}
```

---

# 6. Request stubs

---

## `app/Http/Requests/Auth/RegisterRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'display_name' => ['required', 'string', 'max:150'],
            'date_of_birth' => ['nullable', 'date'],
        ];
    }
}
```

---

## `app/Http/Requests/Auth/LoginRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
```

---

## `app/Http/Requests/Identity/StorePlayerIdentityRequest.php`

```php
<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;

class StorePlayerIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'primary_sport_id' => ['nullable', 'uuid', 'exists:sports,id'],
            'birth_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'context' => ['nullable', 'string', 'max:50'],
        ];
    }
}
```

---

## `app/Http/Requests/Teams/StoreTeamRequest.php`

```php
<?php

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

## `app/Http/Requests/Teams/StoreTeamRoleRequest.php`

```php
<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'role_type' => ['required', 'string', 'in:team_admin,coach,scorekeeper,viewer'],
        ];
    }
}
```

---

## `app/Http/Requests/Teams/StoreRosterEntryRequest.php`

```php
<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreRosterEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'player_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'create_player' => ['nullable', 'array'],
            'create_player.birth_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'jersey_number' => ['nullable', 'string', 'max:20'],
            'positions' => ['nullable', 'array'],
            'positions.*' => ['string', 'max:20'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->input('player_id') && !$this->input('create_player')) {
                $validator->errors()->add('player', 'Either player_id or create_player is required.');
            }
        });
    }
}
```

---

# 7. Controller stubs

---

## `app/Http/Controllers/Api/Auth/RegisterController.php`

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\MeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->execute($request->validated());

        Auth::login($user);

        return (new MeResource($user->load('profile')))
            ->response()
            ->setStatusCode(201);
    }
}
```

---

## `app/Http/Controllers/Api/Auth/LoginController.php`

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\MeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        $request->session()->regenerate();

        return (new MeResource($request->user()->load('profile')))
            ->response()
            ->setStatusCode(200);
    }
}
```

---

## `app/Http/Controllers/Api/Auth/MeController.php`

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\MeResource;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): MeResource
    {
        return new MeResource(
            $request->user()->load('profile', 'playerIdentity', 'teamRoleAssignments')
        );
    }
}
```

---

## `app/Http/Controllers/Api/Identity/PlayerIdentityController.php`

```php
<?php

namespace App\Http\Controllers\Api\Identity;

use App\Actions\Identity\CreatePlayerIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StorePlayerIdentityRequest;
use App\Http\Resources\Identity\PlayerIdentityResource;
use App\Models\PlayerIdentity;
use Illuminate\Http\Request;

class PlayerIdentityController extends Controller
{
    public function store(StorePlayerIdentityRequest $request, CreatePlayerIdentityAction $action)
    {
        $player = $action->execute($request->validated(), $request->user()->id);

        return (new PlayerIdentityResource($player))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, PlayerIdentity $player)
    {
        $this->authorize('view', $player);

        return new PlayerIdentityResource($player->load('sport'));
    }
}
```

---

## `app/Http/Controllers/Api/Identity/ClaimPlayerIdentityController.php`

```php
<?php

namespace App\Http\Controllers\Api\Identity;

use App\Actions\Identity\ClaimPlayerIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Identity\PlayerIdentityResource;
use App\Models\PlayerIdentity;
use Illuminate\Http\Request;

class ClaimPlayerIdentityController extends Controller
{
    public function __invoke(Request $request, PlayerIdentity $player)
    {
        $updated = app(ClaimPlayerIdentityAction::class)
            ->execute($player, $request->user()->id);

        return new PlayerIdentityResource($updated);
    }
}
```

---

## `app/Http/Controllers/Api/Teams/TeamController.php`

```php
<?php

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
            ->whereHas('roleAssignments', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'active');
            })
            ->with('sport')
            ->latest()
            ->get();

        return TeamResource::collection($teams);
    }

    public function store(StoreTeamRequest $request, CreateTeamAction $action)
    {
        $team = $action->execute($request->validated(), $request->user()->id);

        return (new TeamResource($team->load('sport')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        return new TeamResource($team->load('sport', 'roleAssignments'));
    }
}
```

---

## `app/Http/Controllers/Api/Teams/TeamRoleController.php`

```php
<?php

namespace App\Http\Controllers\Api\Teams;

use App\Actions\Teams\AssignTeamRoleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamRoleRequest;
use App\Http\Resources\Teams\TeamRoleAssignmentResource;
use App\Models\Team;
use App\Models\TeamRoleAssignment;
use Illuminate\Http\Request;

class TeamRoleController extends Controller
{
    public function index(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        $roles = $team->roleAssignments()->with('user')->get();

        return TeamRoleAssignmentResource::collection($roles);
    }

    public function store(StoreTeamRoleRequest $request, Team $team, AssignTeamRoleAction $action)
    {
        $this->authorize('assignRoles', $team);

        $assignment = $action->execute($team, $request->validated(), $request->user()->id);

        return (new TeamRoleAssignmentResource($assignment->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, Team $team, TeamRoleAssignment $assignment)
    {
        $this->authorize('assignRoles', $team);

        abort_unless($assignment->team_id === $team->id, 404);

        $assignment->update(['status' => 'inactive']);

        return response()->json(['message' => 'Role assignment deactivated.']);
    }
}
```

---

## `app/Http/Controllers/Api/Teams/RosterController.php`

```php
<?php

namespace App\Http\Controllers\Api\Teams;

use App\Actions\Roster\AddRosterPlayerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreRosterEntryRequest;
use App\Http\Resources\Teams\RosterEntryResource;
use App\Models\Team;
use App\Models\TeamMembership;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function index(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        $roster = $team->memberships()
            ->where('membership_type', 'player')
            ->where('status', 'active')
            ->with('playerIdentity', 'positions')
            ->get();

        return RosterEntryResource::collection($roster);
    }

    public function store(StoreRosterEntryRequest $request, Team $team, AddRosterPlayerAction $action)
    {
        $this->authorize('manageRoster', $team);

        $membership = $action->execute($team, $request->validated(), $request->user()->id);

        return (new RosterEntryResource($membership))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, Team $team, TeamMembership $membership)
    {
        $this->authorize('manageRoster', $team);

        abort_unless($membership->team_id === $team->id, 404);

        $membership->update([
            'status' => 'inactive',
            'end_date' => now()->toDateString(),
        ]);

        return response()->json(['message' => 'Roster membership deactivated.']);
    }
}
```

---

# 8. Policy stubs

---

## `app/Policies/TeamPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\TeamRoleAssignment;
use App\Models\User;

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

    public function assignRoles(User $user, Team $team): bool
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
}
```

---

## `app/Policies/PlayerIdentityPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\GuardianRelationship;
use App\Models\PlayerIdentity;
use App\Models\User;

class PlayerIdentityPolicy
{
    public function view(User $user, PlayerIdentity $player): bool
    {
        if ($player->user_id === $user->id) {
            return true;
        }

        return GuardianRelationship::query()
            ->where('guardian_user_id', $user->id)
            ->where('player_identity_id', $player->id)
            ->exists();
    }
}
```

---

# 9. Resource stubs

---

## `app/Http/Resources/Auth/MeResource.php`

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

## `app/Http/Resources/Identity/PlayerIdentityResource.php`

```php
<?php

namespace App\Http\Resources\Identity;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerIdentityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'player_code' => $this->player_code,
            'birth_year' => $this->birth_year,
            'claim_status' => $this->claim_status,
            'identity_status' => $this->identity_status,
            'sport' => $this->whenLoaded('sport', fn () => [
                'id' => $this->sport->id,
                'code' => $this->sport->code,
                'name' => $this->sport->name,
            ]),
        ];
    }
}
```

---

## `app/Http/Resources/Teams/TeamResource.php`

```php
<?php

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
        ];
    }
}
```

---

## `app/Http/Resources/Teams/TeamRoleAssignmentResource.php`

```php
<?php

namespace App\Http\Resources\Teams;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamRoleAssignmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'role_type' => $this->role_type,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'email' => $this->user->email,
            ]),
        ];
    }
}
```

---

## `app/Http/Resources/Teams/RosterEntryResource.php`

```php
<?php

namespace App\Http\Resources\Teams;

use Illuminate\Http\Resources\Json\JsonResource;

class RosterEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'membership_type' => $this->membership_type,
            'jersey_number' => $this->jersey_number,
            'status' => $this->status,
            'player' => $this->playerIdentity ? [
                'id' => $this->playerIdentity->id,
                'player_code' => $this->playerIdentity->player_code,
                'claim_status' => $this->playerIdentity->claim_status,
                'birth_year' => $this->playerIdentity->birth_year,
            ] : null,
            'positions' => $this->positions->map(fn ($position) => [
                'position_code' => $position->position_code,
            ]),
        ];
    }
}
```

---

# 10. Seeder stub

---

## `database/seeders/SportSeeder.php`

```php
<?php

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
                    'created_at' => now(),
                ]
            );
        }
    }
}
```

Also update `DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        SportSeeder::class,
    ]);
}
```

---

# 11. Route stub

---

## `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Identity\ClaimPlayerIdentityController;
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

# 12. Important notes before coding

## Note 1
Some files are intentionally omitted here for brevity:
- `CoachIdentity`
- `GuardianRelationship`
- related requests/controllers/resources

Those should be added next, but the above is enough to start the first build slice.

## Note 2
The `auth` middleware choice may later become:
- session auth
- Sanctum
depending on your setup.

## Note 3
You’ll want to register policies in `AuthServiceProvider` or via Laravel’s policy auto-discovery.

## Note 4
The code generator currently uses random strings; later you may want collision checks.

---

# 13. Best next step from here

The most useful follow-up now is one of these:

## Option A
I generate the **remaining missing Laravel stubs** for:
- `CoachIdentity`
- `GuardianRelationship`
- related requests/controllers/resources

## Option B
I generate **Feature test stubs** for the core flows

## Option C
I generate a **first local Laravel setup guide** step-by-step

---

# My recommendation
Do this next, in order:

1. **Option A** — complete the missing identity/guardian files
2. **Option B** — feature test stubs
3. **Option C** — local setup guide

If you want, I can continue immediately with **Option A**.