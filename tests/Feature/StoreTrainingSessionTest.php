<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\PlayerIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class StoreTrainingSessionTest extends TestCase
{
    use RefreshDatabase;
    public function test_sync_session_endpoint()
    {
        $user = User::factory()->create();
        $player = PlayerIdentity::firstOrCreate(
            ['player_code' => 'PLR-BULLDOG-001'],
            ['display_name' => 'Brian', 'user_id' => $user->id]
        );
        $payload = [
            'session_date' => '2026-09-16',
            'workout_name' => 'Test Session',
            'sets' => [
                [
                    'exercise_name' => 'Chest-supported DB Row - Spider',
                    'set_number' => 1,
                    'weight_lbs' => 140,
                    'reps' => 10,
                    'section' => 'primary'
                ]
            ]
        ];
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/v1/training/sessions', $payload);
        $response->dump();
        $response->assertStatus(201);
    }
}
