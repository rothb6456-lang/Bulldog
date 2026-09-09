namespace App\Http\Controllers\Api\V1\Training;

use App\Http\Controllers\Controller;
use App\Services\CoachAiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CoachController extends Controller
{
    protected CoachAiService $coachAiService;

    public function __construct(CoachAiService $coachAiService)
    {
        $this->coachAiService = $coachAiService;
    }

    /**
     * Generate a new workout card via the LLM Coach.
     *
     * POST /api/v1/training/coach/generate-card
     */
    public function generateCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'], // e.g. "Queue up Phase 10 Week 3 Day 4"
            'gym_location' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $playerIdentity = $user->playerIdentity;

        if (!$playerIdentity) {
            return response()->json([
                'message' => 'No player identity linked to this account.',
            ], 422);
        }

        try {
            $markdownCard = $this->coachAiService->generateWorkoutCard(
                $playerIdentity->id,
                $validated['prompt'],
                $validated['gym_location'] ?? null
            );

            return response()->json([
                'success' => true,
                'card_markdown' => $markdownCard,
                'player_identity_id' => $playerIdentity->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Coach AI generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
