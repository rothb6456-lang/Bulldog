<?php

namespace App\Http\Controllers\Api\V1\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Training\StoreTrainingSessionRequest;
use App\Models\Exercise;
use App\Models\ExerciseNameMap;
use App\Models\PlayerIdentity;
use App\Models\PlayerPr;
use App\Models\TrainingPhase;
use App\Models\TrainingSession;
use App\Models\TrainingSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrainingSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $playerIdentity = $this->resolvePlayerIdentity($request);

        $sessions = TrainingSession::where('player_identity_id', $playerIdentity->id)
            ->with(['sets.exercise', 'phase'])
            ->orderBy('session_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json(['status' => 'success', 'data' => $sessions]);
    }

    public function store(StoreTrainingSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $playerIdentity = $this->resolvePlayerIdentity($request, $validated['player_identity_id'] ?? null);

        $phase = TrainingPhase::where('player_identity_id', $playerIdentity->id)
            ->where('start_date', '<=', $validated['session_date'])
            ->where(function ($query) use ($validated) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $validated['session_date']);
            })
            ->first();

        $session = DB::transaction(function () use ($validated, $playerIdentity, $phase) {
            $session = TrainingSession::create([
                'id'                 => (string) Str::uuid(),
                'phase_id'           => $phase?->id,
                'player_identity_id' => $playerIdentity->id,
                'session_date'       => $validated['session_date'],
                'program_day'        => $validated['program_day'] ?? null,
                'workout_name'       => $validated['workout_name'],
                'gym_location'       => $validated['gym_location'] ?? null,
                'general_notes'      => $validated['general_notes'] ?? null,
                'rpe_overall'        => $validated['rpe_overall'] ?? null,
            ]);

            foreach ($validated['sets'] as $setPayload) {
                $exercise = $this->resolveExercise($setPayload['exercise_id'] ?? null, $setPayload['exercise_name']);

                $set = TrainingSet::create([
                    'id'               => (string) Str::uuid(),
                    'session_id'       => $session->id,
                    'exercise_id'      => $exercise->id,
                    'section'          => $setPayload['section'] ?? 'primary',
                    'set_number'       => $setPayload['set_number'],
                    'weight_lbs'       => $setPayload['weight_lbs'] ?? 0,
                    'reps'             => $setPayload['reps'] ?? null,
                    'duration_seconds' => $setPayload['duration_seconds'] ?? null,
                    'rir'              => $setPayload['rir'] ?? null,
                    'tempo'            => $setPayload['tempo'] ?? null,
                    'set_notes'        => $setPayload['set_notes'] ?? null,
                ]);

                $this->evaluatePersonalRecord($playerIdentity, $session, $exercise, $set);
            }

            return $session->load(['sets.exercise', 'phase']);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Training session synced successfully.',
            'data'    => [
                'session_id'   => $session->id,
                'session_date' => $session->session_date->toDateString(),
                'synced_at'    => now()->toIso8601String(),
                'sets_count'   => $session->sets->count(),
            ],
        ], 201);
    }

    public function show(TrainingSession $session): JsonResponse
    {
        $this->authorize('view', $session);
        return response()->json([
            'status' => 'success',
            'data'   => $session->load(['sets.exercise', 'phase', 'playerIdentity']),
        ]);
    }

    private function resolvePlayerIdentity(Request $request, ?string $explicitId = null): PlayerIdentity
    {
        if ($explicitId) {
            return PlayerIdentity::findOrFail($explicitId);
        }

        $user = $request->user();
        $playerIdentity = PlayerIdentity::where('user_id', $user->id)->first();

        if (!$playerIdentity) {
            $playerIdentity = PlayerIdentity::create([
                'id'           => (string) Str::uuid(),
                'player_code'  => 'PLR-' . strtoupper(Str::random(8)),
                'user_id'      => $user->id,
                'claim_status' => 'claimed',
                'display_name' => $user->name ?? explode('@', $user->email)[0],
            ]);
        }

        return $playerIdentity;
    }

    /**
     * Resolve Exercise model using UUID or canonical/mapped name.
     * Patch: bulldog_exercise_resolve_fix.patch
     *   logged_name          -> original_name   (actual migration 000002 column)
     *   canonical_exercise_id -> exercise_id    (actual FK column)
     */
    private function resolveExercise(?string $exerciseId, string $rawName): Exercise
    {
        if ($exerciseId && $exercise = Exercise::find($exerciseId)) {
            return $exercise;
        }

        if ($exercise = Exercise::where('canonical_name', $rawName)->first()) {
            return $exercise;
        }

        $map = ExerciseNameMap::where('original_name', $rawName)->first();
        if ($map && $exercise = Exercise::find($map->exercise_id)) {
            return $exercise;
        }

        return Exercise::create([
            'id'             => (string) Str::uuid(),
            'canonical_name' => $rawName,
            'category'       => 'Other',
        ]);
    }

    private function evaluatePersonalRecord(PlayerIdentity $player, TrainingSession $session, Exercise $exercise, TrainingSet $set): void
    {
        if ($set->weight_lbs > 0 && ($set->reps > 0 || $set->duration_seconds > 0)) {
            $existingWeightPr = PlayerPr::where('player_identity_id', $player->id)
                ->where('exercise_id', $exercise->id)
                ->where('pr_type', 'Heaviest Weight')
                ->first();

            if (!$existingWeightPr || $set->weight_lbs > $existingWeightPr->pr_value) {
                PlayerPr::updateOrCreate(
                    ['player_identity_id' => $player->id, 'exercise_id' => $exercise->id, 'pr_type' => 'Heaviest Weight'],
                    [
                        'id'            => (string) Str::uuid(),
                        'pr_value'      => $set->weight_lbs,
                        'pr_unit'       => 'lbs',
                        'pr_date'       => $session->session_date,
                        'phase_id'      => $session->phase_id,
                        'previous_best' => $existingWeightPr?->pr_value,
                        'set_details'   => "{$set->reps} reps @ {$set->weight_lbs} lbs",
                        'notes'         => 'Automated PR detected from Momentum PWA sync',
                    ]
                );
            }
        }

        if ($set->duration_seconds > 0) {
            $existingDurationPr = PlayerPr::where('player_identity_id', $player->id)
                ->where('exercise_id', $exercise->id)
                ->where('pr_type', 'Longest Duration')
                ->first();

            if (!$existingDurationPr || $set->duration_seconds > $existingDurationPr->pr_value) {
                PlayerPr::updateOrCreate(
                    ['player_identity_id' => $player->id, 'exercise_id' => $exercise->id, 'pr_type' => 'Longest Duration'],
                    [
                        'id'            => (string) Str::uuid(),
                        'pr_value'      => $set->duration_seconds,
                        'pr_unit'       => 'seconds',
                        'pr_date'       => $session->session_date,
                        'phase_id'      => $session->phase_id,
                        'previous_best' => $existingDurationPr?->pr_value,
                        'set_details'   => "{$set->duration_seconds} sec @ {$set->weight_lbs} lbs",
                        'notes'         => 'Automated Duration PR detected from Momentum PWA sync',
                    ]
                );
            }
        }
    }
}
