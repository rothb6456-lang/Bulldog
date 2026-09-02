# Option D  
## Model Factories for First Tests

These factories are starter-safe for Phase 1 + 2 testing.

They assume:
- UUID primary keys
- Laravel factory conventions
- MySQL beta, Postgres-portable design
- minimal but valid relationships

---

## 1. `database/factories/UserFactory.php`

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'bulldog_user_code' => 'BDG-' . strtoupper(Str::random(8)),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => null,
            'phone_verified_at' => null,
            'date_of_birth' => null,
            'is_minor' => false,
            'account_status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function minor(): static
    {
        return $this->state(fn () => [
            'date_of_birth' => now()->subYears(12)->toDateString(),
            'is_minor' => true,
        ]);
    }
}
```

---

## 2. `database/factories/UserProfileFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'display_name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'avatar_url' => null,
            'bio' => null,
            'city' => fake()->city(),
            'state_region' => fake()->state(),
            'country' => 'USA',
            'default_visibility_level' => 'private',
        ];
    }
}
```

---

## 3. `database/factories/SportFactory.php`

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sport>
 */
class SportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'code' => 'sport_' . strtolower(Str::random(6)),
            'name' => fake()->word(),
            'status' => 'active',
            'created_at' => now(),
        ];
    }

    public function baseball(): static
    {
        return $this->state(fn () => [
            'code' => 'baseball',
            'name' => 'Baseball',
        ]);
    }

    public function softball(): static
    {
        return $this->state(fn () => [
            'code' => 'softball',
            'name' => 'Softball',
        ]);
    }
}
```

---

## 4. `database/factories/PlayerIdentityFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerIdentity>
 */
class PlayerIdentityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'player_code' => 'PLY-' . strtoupper(Str::random(8)),
            'user_id' => null,
            'created_by_user_id' => User::factory(),
            'primary_sport_id' => null,
            'birth_year' => fake()->numberBetween(2008, 2016),
            'claim_status' => 'unclaimed',
            'identity_status' => 'active',
        ];
    }

    public function claimed(?User $user = null): static
    {
        return $this->state(function () use ($user) {
            $resolved = $user ?? User::factory()->create();

            return [
                'user_id' => $resolved->id,
                'claim_status' => 'claimed',
            ];
        });
    }

    public function forSport(?Sport $sport = null): static
    {
        return $this->state(function () use ($sport) {
            $resolved = $sport ?? Sport::factory()->create();

            return [
                'primary_sport_id' => $resolved->id,
            ];
        });
    }
}
```

### Small note
In some teams, creating a model inside `state()` is avoided. If you want stricter factory purity later, we can refactor this.

---

## 5. `database/factories/CoachIdentityFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoachIdentity>
 */
class CoachIdentityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'coach_code' => 'COA-' . strtoupper(Str::random(8)),
            'user_id' => User::factory(),
            'identity_status' => 'active',
        ];
    }
}
```

---

## 6. `database/factories/GuardianRelationshipFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\PlayerIdentity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GuardianRelationship>
 */
class GuardianRelationshipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'guardian_user_id' => User::factory(),
            'player_identity_id' => PlayerIdentity::factory(),
            'relationship_type' => 'guardian',
            'verification_status' => 'pending',
            'is_primary_guardian' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => [
            'is_primary_guardian' => true,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'verification_status' => 'verified',
        ]);
    }
}
```

---

## 7. `database/factories/TeamFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'team_code' => 'TEM-' . strtoupper(Str::random(8)),
            'sport_id' => Sport::factory(),
            'organization_id' => null,
            'name' => fake()->company() . ' Bulldogs',
            'nickname' => 'Bulldogs',
            'season_label' => '2026',
            'age_group' => '12U',
            'city' => fake()->city(),
            'state_region' => fake()->state(),
            'created_by_user_id' => User::factory(),
            'visibility_level' => 'team',
            'status' => 'active',
        ];
    }
}
```

---

## 8. `database/factories/TeamRoleAssignmentFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamRoleAssignment>
 */
class TeamRoleAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'role_type' => 'viewer',
            'granted_by_user_id' => null,
            'status' => 'active',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role_type' => 'team_admin',
        ]);
    }

    public function coach(): static
    {
        return $this->state(fn () => [
            'role_type' => 'coach',
        ]);
    }
}
```

---

## 9. `database/factories/TeamMembershipFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\CoachIdentity;
use App\Models\PlayerIdentity;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMembership>
 */
class TeamMembershipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'team_id' => Team::factory(),
            'membership_type' => 'player',
            'user_id' => null,
            'player_identity_id' => PlayerIdentity::factory(),
            'coach_identity_id' => null,
            'jersey_number' => (string) fake()->numberBetween(0, 99),
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'status' => 'active',
            'created_by_user_id' => User::factory(),
        ];
    }

    public function player(): static
    {
        return $this->state(fn () => [
            'membership_type' => 'player',
            'player_identity_id' => PlayerIdentity::factory(),
            'coach_identity_id' => null,
            'user_id' => null,
        ]);
    }

    public function coach(): static
    {
        return $this->state(fn () => [
            'membership_type' => 'coach',
            'player_identity_id' => null,
            'coach_identity_id' => CoachIdentity::factory(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'inactive',
            'end_date' => now()->toDateString(),
        ]);
    }
}
```

---

## 10. `database/factories/TeamMembershipPositionFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\TeamMembership;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMembershipPosition>
 */
class TeamMembershipPositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'team_membership_id' => TeamMembership::factory(),
            'position_code' => fake()->randomElement(['P', 'C', '1B', '2B', '3B', 'SS', 'LF', 'CF', 'RF']),
            'created_at' => now(),
        ];
    }
}
```

---

## 11. `database/factories/AuditLogFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'actor_user_id' => User::factory(),
            'action_type' => 'test.action',
            'object_type' => 'test_object',
            'object_id' => (string) Str::uuid(),
            'metadata_json' => [],
            'created_at' => now(),
        ];
    }
}
```

---

# Important model note

For factories to work cleanly, your models should include:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
```

and then:

```php
use HasFactory;
```

inside each model.

That includes:
- `User`
- `UserProfile`
- `Sport`
- `PlayerIdentity`
- `CoachIdentity`
- `GuardianRelationship`
- `Team`
- `TeamRoleAssignment`
- `TeamMembership`
- `TeamMembershipPosition`
- `AuditLog`

---

# Option E  
## Policy Registration, Auth Wiring, and Middleware Notes

This is the glue that makes the stubs actually behave correctly in Laravel.

---

## 1. Policy registration

Depending on your Laravel version/setup, auto-discovery may work, but I recommend being explicit early.

### `app/Providers/AuthServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Models\GuardianRelationship;
use App\Models\PlayerIdentity;
use App\Models\Team;
use App\Policies\GuardianRelationshipPolicy;
use App\Policies\PlayerIdentityPolicy;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Team::class => TeamPolicy::class,
        PlayerIdentity::class => PlayerIdentityPolicy::class,
        GuardianRelationship::class => GuardianRelationshipPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
```

---

## 2. Auth middleware choice

For your beta, the simplest path is:

## Recommended
- **session-based web auth**
- API routes protected with authenticated session middleware if app is same-origin
- Sanctum can be added if needed, but do not overcomplicate immediately

In your current route stub, `auth` is fine as a starting point.

If you later want explicit Sanctum protection:

```php
Route::middleware('auth:sanctum')->group(function () {
    // protected routes
});
```

But for now, if your Laravel app is a single hosted web app with same-domain requests, basic authenticated session flow is okay.

---

## 3. Make sure AuthServiceProvider is loaded

Normally Laravel handles this automatically, but if policies seem not to work:
- confirm provider exists
- confirm it is registered correctly in the app bootstrap for your Laravel version

---

## 4. Middleware notes for your use case

### Recommended middleware stack
For authenticated app pages and API:
- auth
- verified later if email verification is added
- throttle for auth endpoints
- web middleware for session/csrf on browser routes

### API vs web note
If your Blade frontend calls same-origin routes, decide clearly whether:
- browser pages talk to API endpoints via JS/fetch
- or Blade forms post to web controllers

For now, your architecture is API-first, which is good.  
Just keep CSRF/session behavior in mind for browser usage.

---

## 5. TeamPolicy improvement note

Your current `TeamPolicy` includes:
- `view`
- `assignRoles`
- `manageRoster`

That’s good.

I would also add `update` now to keep authorization naming clear.

### Revised `app/Policies/TeamPolicy.php`

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

    public function update(User $user, Team $team): bool
    {
        return TeamRoleAssignment::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('role_type', 'team_admin')
            ->where('status', 'active')
            ->exists();
    }

    public function assignRoles(User $user, Team $team): bool
    {
        return $this->update($user, $team);
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

## 6. PlayerIdentityPolicy improvement note

Right now it supports:
- self
- guardian-linked access

Later, you will likely extend it for:
- team-context access
- coach/admin access to players on their own rosters
- minor-aware restrictions

For now, keep it tight rather than too open.

---

## 7. Route middleware recommendation

### Current safe starting point
```php
Route::middleware('auth')->group(function () {
    // protected routes
});
```

### If using Laravel Breeze / standard session auth
This is fine.

### If using Sanctum later
Move to:
```php
Route::middleware('auth:sanctum')->group(function () {
    // protected routes
});
```

---

## 8. Controller authorization notes

Be explicit inside controllers.

Examples already good:
- `$this->authorize('view', $team);`
- `$this->authorize('assignRoles', $team);`
- `$this->authorize('manageRoster', $team);`

Keep doing that.  
Do not assume route protection alone is enough.

---

## 9. Exception behavior note

For authorization and validation:
- 403 for forbidden
- 422 for validation/business-rule violations
- 404 when resource mismatch should not leak details

Example:
```php
abort_unless($assignment->team_id === $team->id, 404);
```

That is the right pattern.

---

## 10. CSRF/session note for Blade + API hybrid use

If your frontend uses Blade pages and form submissions:
- web middleware handles CSRF

If your frontend uses JS calls to `/api/v1/...` from same-origin pages:
- ensure auth/session/CSRF approach is coherent

For early beta simplicity, many teams choose one of:
1. Blade forms against web controllers
2. JS fetch with session-auth + CSRF token

Either is fine, but be consistent.

---

# Option F  
## Blade Page Stubs for First App Screens

These are simple, coach-friendly, responsive starters.

They are intentionally light.

---

## 1. Suggested view file structure

```text
resources/views/
  layouts/
    guest.blade.php
    app.blade.php
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
```

---

## 2. `resources/views/layouts/guest.blade.php`

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Bulldog') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white shadow rounded-2xl p-6">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold">Bulldog</h1>
                <p class="text-sm text-gray-500">Statbook Beta</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded bg-green-100 p-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded bg-red-100 p-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
```

---

## 3. `resources/views/layouts/app.blade.php`

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Bulldog') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="min-h-screen">
        <header class="bg-white border-b">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <div>
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold">Bulldog</a>
                    <p class="text-xs text-gray-500">Statbook Beta</p>
                </div>

                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
                    <a href="{{ route('teams.index') }}" class="hover:underline">Teams</a>
                    <a href="{{ route('profile.index') }}" class="hover:underline">Profile</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded bg-gray-900 text-white px-3 py-2">
                            Log out
                        </button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-4 py-6">
            @if (session('status'))
                <div class="mb-4 rounded bg-green-100 p-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
```

---

## 4. `resources/views/auth/login.blade.php`

```php
@extends('layouts.guest')

@section('content')
    <h2 class="text-lg font-semibold mb-4">Log in</h2>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="w-full rounded border px-3 py-2"
            >
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input
                type="password"
                name="password"
                required
                class="w-full rounded border px-3 py-2"
            >
        </div>

        <button type="submit" class="w-full rounded bg-gray-900 text-white px-4 py-2">
            Log in
        </button>
    </form>
@endsection
```

---

## 5. `resources/views/auth/register.blade.php`

```php
@extends('layouts.guest')

@section('content')
    <h2 class="text-lg font-semibold mb-4">Create account</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Display name</label>
            <input
                type="text"
                name="display_name"
                value="{{ old('display_name') }}"
                required
                class="w-full rounded border px-3 py-2"
            >
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="w-full rounded border px-3 py-2"
            >
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Date of birth</label>
            <input
                type="date"
                name="date_of_birth"
                value="{{ old('date_of_birth') }}"
                class="w-full rounded border px-3 py-2"
            >
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input
                type="password"
                name="password"
                required
                class="w-full rounded border px-3 py-2"
            >
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Confirm password</label>
            <input
                type="password"
                name="password_confirmation"
                required
                class="w-full rounded border px-3 py-2"
            >
        </div>

        <button type="submit" class="w-full rounded bg-gray-900 text-white px-4 py-2">
            Create account
        </button>
    </form>
@endsection
```

---

## 6. `resources/views/dashboard.blade.php`

```php
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="bg-white rounded-2xl shadow p-6">
            <h1 class="text-2xl font-bold mb-2">Dashboard</h1>
            <p class="text-gray-600">
                Welcome to Bulldog Statbook Beta.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="font-semibold mb-2">My Teams</h2>
                <p class="text-sm text-gray-600 mb-4">
                    View and manage teams where you have a role.
                </p>
                <a href="{{ route('teams.index') }}" class="inline-block rounded bg-blue-600 text-white px-4 py-2">
                    Open Teams
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="font-semibold mb-2">Profile</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Manage your account and identity settings.
                </p>
                <a href="{{ route('profile.index') }}" class="inline-block rounded bg-gray-900 text-white px-4 py-2">
                    Open Profile
                </a>
            </div>
        </section>
    </div>
@endsection
```

---

## 7. `resources/views/profile/index.blade.php`

```php
@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-2xl shadow p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-4">Profile</h1>

        <form method="POST" action="#">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Display name</label>
                    <input type="text" class="w-full rounded border px-3 py-2" value="{{ auth()->user()->profile->display_name ?? '' }}">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" class="w-full rounded border px-3 py-2" value="{{ auth()->user()->email }}" disabled>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium mb-1">Bio</label>
                <textarea class="w-full rounded border px-3 py-2" rows="4">{{ auth()->user()->profile->bio ?? '' }}</textarea>
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

## 8. `resources/views/teams/index.blade.php`

```php
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">My Teams</h1>
                <p class="text-gray-600 text-sm">Teams where you have an active role.</p>
            </div>

            <a href="{{ route('teams.create') }}" class="rounded bg-blue-600 text-white px-4 py-2">
                Create Team
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($teams ?? [] as $team)
                <a href="{{ route('teams.show', $team) }}" class="block bg-white rounded-2xl shadow p-6 hover:shadow-md">
                    <h2 class="text-lg font-semibold">{{ $team->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $team->season_label }} · {{ $team->age_group }}</p>
                    <p class="text-sm text-gray-500 mt-2">{{ $team->city }} {{ $team->state_region }}</p>
                </a>
            @empty
                <div class="bg-white rounded-2xl shadow p-6 text-gray-600">
                    No teams yet.
                </div>
            @endforelse
        </div>
    </div>
@endsection
```

---

## 9. `resources/views/teams/create.blade.php`

```php
@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-2xl shadow p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-4">Create Team</h1>

        <form method="POST" action="#" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Team name</label>
                <input type="text" name="name" class="w-full rounded border px-3 py-2" required>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Season label</label>
                    <input type="text" name="season_label" class="w-full rounded border px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Age group</label>
                    <input type="text" name="age_group" class="w-full rounded border px-3 py-2">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <input type="text" name="city" class="w-full rounded border px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">State / Region</label>
                    <input type="text" name="state_region" class="w-full rounded border px-3 py-2">
                </div>
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

## 10. `resources/views/teams/show.blade.php`

```php
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="bg-white rounded-2xl shadow p-6">
            <h1 class="text-2xl font-bold">{{ $team->name ?? 'Team' }}</h1>
            <p class="text-sm text-gray-600 mt-1">
                {{ $team->season_label ?? '' }} {{ $team->age_group ?? '' }}
            </p>
            <p class="text-sm text-gray-500 mt-2">
                {{ $team->city ?? '' }} {{ $team->state_region ?? '' }}
            </p>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Roster</h2>
                    <button class="rounded bg-blue-600 text-white px-3 py-2 text-sm">
                        Add Player
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse ($roster ?? [] as $entry)
                        <div class="border rounded p-3">
                            <div class="font-medium">
                                {{ $entry->playerIdentity->player_code ?? 'Player' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                Jersey #{{ $entry->jersey_number ?? '--' }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No active roster entries.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Roles</h2>
                    <button class="rounded bg-gray-900 text-white px-3 py-2 text-sm">
                        Assign Role
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse ($roles ?? [] as $role)
                        <div class="border rounded p-3">
                            <div class="font-medium">{{ $role->user->email ?? 'User' }}</div>
                            <div class="text-sm text-gray-500">{{ $role->role_type }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No roles assigned.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
```

---

# Important note on these Blade stubs

These pages are intentionally:
- simple
- static-ish
- layout-first

They are meant to establish:
- navigation
- page structure
- mobile-friendly layout
- “hosted app” feel

They are **not yet wired** to final controllers/forms/routes.  
That comes next as implementation tightens.

---

# Recommended immediate next move

Now that we’ve completed:
- factories
- auth/policy wiring notes
- Blade stubs

the next best artifact is one of these:

## Option G
**Web route/controller stubs** for the Blade pages

## Option H
**A first-pass implementation order with exact artisan commands**

## Option I
**A “copy/paste into Codex” execution sequence for building these files incrementally**

### My recommendation
Do these next in order:
1. **Option H**
2. **Option G**
3. **Option I**

If you want, I can continue immediately with **Option H**.