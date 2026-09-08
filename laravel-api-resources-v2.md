# Laravel Integration Tests & Routes for Bulldog Statbook

This guide contains the exact routes, registration controllers, and Feature test files to implement and test the complete **E2E Golden Path** (Registration → Team Creation → Roster Player Assignment) in your local GitHub Codespace.

---

## 🛠️ Step 1: Complete the Routes (`routes/api.php`)

Add the registration endpoint to your existing API routes file. Ensure they are prefixed appropriately and match the API blueprints [156, 170, 193].

Open **`routes/api.php`** and update its contents:

```php
<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Teams\RosterController;
use App\Http\Controllers\Api\Teams\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public / Guest Endpoints
Route::post('/v1/auth/register', [RegisterController::class, 'store']);

// Authenticated Endpoints
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Team Management [166]
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    
    // Roster Management [169]
    Route::post('/teams/{team}/roster', [RosterController::class, 'store']);
});
```

---

## 📋 Step 2: Create the Registration Layer

To make the E2E verification test complete, we need the controller and action layer to handle new user registration [156].

### 1. Create the Registration Action Class
Create a new file at **`app/Actions/Auth/RegisterUserAction.php`**:

```php
<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * Execute the registration and profile creation transaction.
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the base User
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_minor' => $data['is_minor'] ?? false,
                'status' => 'active',
            ]);

            // 2. Attach the initial UserProfile
            $user->profile()->create([
                'display_name' => $data['display_name'],
                'primary_sport_id' => $data['primary_sport_id'] ?? null,
            ]);

            return $user;
        });
    }
}
```

### 2. Create the Registration API Controller
Create a new file at **`app/Http/Controllers/Api/Auth/RegisterController.php`**:

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    protected RegisterUserAction $registerAction;

    public function __construct(RegisterUserAction $registerAction)
    {
        $this->registerAction = $registerAction;
    }

    /**
     * Store a newly created user and return a token.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'display_name' => ['required', 'string', 'max:100'],
            'is_minor' => ['nullable', 'boolean'],
            'primary_sport_id' => ['nullable', 'uuid', 'exists:sports,id'],
        ]);

        $user = $this->registerAction->execute($validated);

        // Issue a Sanctum token for immediate session API auth
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'is_minor' => $user->is_minor,
                'profile' => [
                    'display_name' => $user->profile->display_name,
                ]
            ],
            'token' => $token
        ], 201);
    }
}
```

---

## 🧪 Step 3: Implement the Integration Tests

These tests use Laravel's standard feature testing suite (`RefreshDatabase`) to verify everything connects perfectly in your SQLite setup [390, 395].

### Test 1: User Registration
Create a new file at **`tests/Feature/Auth/RegisterTest.php`** [391]:

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_register_and_automatically_receives_a_profile()
    {
        // 1. Arrange: Seed a sport (Baseball)
        $sport = Sport::create([
            'code' => 'baseball',
            'name' => 'Baseball'
        ]);

        $payload = [
            'email' => 'coach@bulldogstats.com',
            'password' => 'password123',
            'display_name' => 'Coach Hayes',
            'is_minor' => false,
            'primary_sport_id' => $sport->id,
        ];

        // 2. Act: Post registration request
        $response = $this->postJson('/api/v1/auth/register', $payload);

        // 3. Assert: Verify HTTP status and nested DB records
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                    'is_minor',
                    'profile' => [
                        'display_name',
                    ]
                ],
                'token'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'coach@bulldogstats.com',
            'is_minor' => false,
        ]);

        $this->assertDatabaseHas('user_profiles', [
            'display_name' => 'Coach Hayes',
            'primary_sport_id' => $sport->id,
        ]);
    }
}
```

### Test 2: Golden Path E2E Team Setup & Roster Assignment
Create a new file at **`tests/Feature/Teams/RosterTest.php`** [392]:

```php
<?php

namespace Tests\Feature\Teams;

use App\Models\Sport;
use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RosterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authenticated_user_can_execute_the_entire_golden_path_e2e()
    {
        // 1. Arrange: Create user, profile, and sport
        $sport = Sport::create([
            'code' => 'softball',
            'name' => 'Softball'
        ]);

        $user = User::create([
            'email' => 'admin@bulldogstats.com',
            'password' => bcrypt('password123'),
            'is_minor' => false,
            'status' => 'active'
        ]);

        $user->profile()->create([
            'display_name' => 'Coach Hayes',
            'primary_sport_id' => $sport->id
        ]);

        // Authenticate the user with Sanctum
        Sanctum::actingAs($user);

        // 2. Act - Part A: Create a Team
        $teamPayload = [
            'name' => 'Lady Bulldogs 14U',
            'sport_id' => $sport->id,
            'age_group' => '14U',
            'season_label' => 'Summer 2026'
        ];

        $teamResponse = $this->postJson('/api/v1/teams', $teamPayload);
        $teamResponse->assertStatus(201);
        
        $teamId = $teamResponse->json('team.id');

        // Confirm team creation and automatic role assignment (team_admin)
        $this->assertDatabaseHas('teams', [
            'id' => $teamId,
            'name' => 'Lady Bulldogs 14U'
        ]);

        $this->assertDatabaseHas('team_role_assignments', [
            'team_id' => $teamId,
            'user_id' => $user->id,
            'role_type' => 'team_admin'
        ]);

        // 3. Act - Part B: Add a roster player (unclaimed inline)
        $rosterPayload = [
            'display_name' => 'Jonny Damon',
            'jersey_number' => '18',
            'positions' => ['CF', 'LF']
        ];

        // Ensure TeamPolicy authorization allows the team admin to edit roster
        $rosterResponse = $this->postJson("/api/v1/teams/{$teamId}/roster", $rosterPayload);
        
        // 4. Assert: Confirm roster success and database records
        $rosterResponse->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'membership' => [
                    'id',
                    'jersey_number',
                    'positions_json',
                    'player_identity' => [
                        'id',
                        'player_code',
                        'display_name',
                        'claim_status'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('player_identities', [
            'display_name' => 'Jonny Damon',
            'claim_status' => 'unclaimed'
        ]);

        $this->assertDatabaseHas('team_memberships', [
            'team_id' => $teamId,
            'jersey_number' => '18',
            'membership_type' => 'player'
        ]);
    }
}
```

---

## 🏃 Step 4: Run Your Tests

Now that everything is wired up, run the standard artisan test command inside your GitHub Codespace terminal to confirm that registration, role authorization, policies, resources, and the complete E2E Golden Path are operating flawlessly [395]:

```bash
php artisan test
```
