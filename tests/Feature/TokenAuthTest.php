<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TokenAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_minor')->default(false);
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('canonical_name');
            $table->string('exercise_category')->nullable();
            $table->string('movement_pattern')->nullable();
            $table->uuid('equipment_id')->nullable();
            $table->string('laterality')->nullable();
            $table->text('shoulder_safety_notes')->nullable();
            $table->timestamps();
        });
    }

    public function test_registered_user_can_obtain_a_sanctum_token_and_use_it_on_training_routes(): void
    {
        $user = User::factory()->create();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Momentum PWA test',
        ]);

        $login->assertOk()->assertJsonStructure(['token']);

        $this->withToken($login->json('token'))
            ->getJson('/api/v1/training/exercises')
            ->assertOk();
    }

    public function test_invalid_credentials_return_a_json_message(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'not-the-password',
        ])->assertUnprocessable()
            ->assertJson(['message' => 'Invalid email or password.']);
    }
}
