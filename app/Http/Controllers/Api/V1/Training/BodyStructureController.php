<?php

namespace App\Http\Controllers\Api\V1\Training;

use App\Actions\Training\AwardBodyStructureLearnedAction;
use App\Http\Controllers\Controller;
use App\Models\BodyStructure;
use App\Models\PlayerIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BodyStructureController extends Controller
{
    /**
     * The full anatomy/education reference library — synced and cached client-side
     * independent of the exercise catalog, since the same ~32 structures repeat
     * across dozens of exercises.
     *
     * GET /api/v1/training/body-structures
     */
    public function index(): JsonResponse
    {
        $structures = BodyStructure::query()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'type', 'region', 'short_description', 'function_notes', 'common_issues']);

        return response()->json([
            'status' => 'success',
            'data' => $structures,
        ]);
    }

    /**
     * Records that the authenticated player read the Coach guide on a given
     * body structure, awarding ANATOMY_INSIGHT (+XP if the identity is
     * claimed) the first time only. Safe to call repeatedly — repeats are a
     * silent no-op, not an error.
     *
     * POST /api/v1/training/body-structures/{bodyStructure}/learned
     */
    public function markLearned(Request $request, BodyStructure $bodyStructure, AwardBodyStructureLearnedAction $action): JsonResponse
    {
        $playerIdentity = $this->resolvePlayerIdentity($request);

        $result = $action->execute($playerIdentity, $bodyStructure);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    private function resolvePlayerIdentity(Request $request): PlayerIdentity
    {
        $user = $request->user();
        $playerIdentity = PlayerIdentity::where('user_id', $user->id)->first();

        if (!$playerIdentity) {
            $playerIdentity = PlayerIdentity::create([
                'id' => (string) Str::uuid(),
                'player_code' => 'PLR-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'claim_status' => 'claimed',
                'display_name' => $user->name ?? explode('@', $user->email)[0],
            ]);
        }

        return $playerIdentity;
    }
}
