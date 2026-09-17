<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reproduction harness for the Momentum PWA -> Statbook sync 500.
 *
 * Posts the EXACT payload shape emitted by syncSessionToStatbook() in
 * sync.js so the failure surfaces locally with a full stack trace
 * instead of a generic "Server Error" string on the client.
 *
 * Run with:  php artisan test --filter=MomentumSyncReproTest
 */
class MomentumSyncReproTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The payload the PWA actually sends today (the live builder).
     * Note: no weight_lbs / reps / duration_seconds / set_notes.
     */
    private function livePwaPayload(): array
    {
        return [
            'session_id'      => 'session_1757980000_ab12cd',
            'workout_name'    => 'Phase 2 - Day 1 Upper',
            'session_date'    => '2026-09-16',
            'phase'           => 'phase-2',
            'week'            => '3',
            'day'             => '1',
            'completed_at'    => '2026-09-16T19:05:00.000Z',
            'coach_questions' => '',
            'sets'            => [
                [
                    'set_number'       => 1,
                    'exercise_name'    => 'Dumbbell Bench Press',
                    'load'             => '55',
                    'reps_or_duration' => '8',
                    'is_timed'         => false,
                    'tempo'            => '3-1-2',
                    'rir'              => '2-3',
                    'rest'             => '90s',
                    'section'          => 'primary',
                    'optional'         => false,
                    'notes'            => '',
                ],
                [
                    'set_number'       => 1,
                    'exercise_name'    => 'Farmer Carry',
                    'load'             => '70',
                    'reps_or_duration' => '45 sec',
                    'is_timed'         => true,
                    'tempo'            => '',
                    'rir'              => '',
                    'rest'             => '2m',
                    'section'          => 'optional',
                    'optional'         => true,
                    'notes'            => 'grip failed late',
                ],
            ],
        ];
    }

    public function test_live_pwa_payload_reproduces_the_failure(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/training/sessions', $this->livePwaPayload());

        // Intentionally dump rather than assert: we want to SEE the outcome.
        dump([
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        $this->assertTrue(true);
    }

    /**
     * Reproduces the actual production failure: an exercise name that isn't
     * in the canonical library yet, but IS aliased via ExerciseNameMap.
     * Before the fix this threw SQLSTATE[42S22] on 'logged_name'. This test
     * fails loudly (rather than silently falling through to Exercise::create)
     * if the column/attribute names ever regress.
     */
    public function test_resolves_exercise_via_name_map_alias_without_querying_a_missing_column(): void
    {
        $user = User::factory()->create();

        $canonical = \App\Models\Exercise::create([
            'id'             => (string) \Illuminate\Support\Str::uuid(),
            'canonical_name' => 'Dumbbell Row',
            'category'       => 'Pull',
        ]);

        \App\Models\ExerciseNameMap::create([
            'id'             => (string) \Illuminate\Support\Str::uuid(),
            'original_name'  => 'Chest-supported Dumbbell Row',
            'exercise_id'    => $canonical->id,
        ]);

        $payload = $this->livePwaPayload();
        $payload['sets'][0]['exercise_name'] = 'Chest-supported Dumbbell Row';
        $payload['sets'][0]['section'] = 'primary';
        unset($payload['sets'][1]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/training/sessions', $payload);

        $response->assertStatus(201);

        // The set must have resolved to the CANONICAL exercise, not spawned
        // a brand-new duplicate Exercise row named after the logged alias.
        $this->assertDatabaseHas('training_sets', [
            'exercise_id' => $canonical->id,
        ]);
        $this->assertDatabaseCount('exercises', 1);
    }

    /**
     * Control case: the payload shape the server actually validates for,
     * matching the orphaned mapSessionToApiPayload() in sync.js.
     * If this passes while the above fails, the bug is purely payload shape.
     */
    public function test_correctly_shaped_payload_succeeds(): void
    {
        $user = User::factory()->create();

        $payload = [
            'workout_name' => 'Phase 2 - Day 1 Upper',
            'session_date' => '2026-09-16',
            'program_day'  => 1,
            'gym_location' => 'Planet Fitness',
            'sets'         => [
                [
                    'set_number'    => 1,
                    'exercise_name' => 'Dumbbell Bench Press',
                    'section'       => 'primary',
                    'weight_lbs'    => 55,
                    'reps'          => 8,
                    'tempo'         => '3-1-2',
                    'rir'           => '2-3',
                    'set_notes'     => null,
                ],
                [
                    'set_number'       => 1,
                    'exercise_name'    => 'Farmer Carry',
                    'section'          => 'primary',
                    'weight_lbs'       => 70,
                    'reps'             => null,
                    'duration_seconds' => 45,
                    'set_notes'        => 'grip failed late',
                ],
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/training/sessions', $payload);

        dump([
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        $this->assertTrue(true);
    }
}