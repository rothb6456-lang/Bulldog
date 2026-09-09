namespace App\Actions\Training;

use App\Models\PlayerIdentity;
use App\Models\PlayerTrainingAssumption;
use App\Models\PlayerPr;
use App\Models\TrainingSession;
use App\Models\TrainingSet;
use Illuminate\Support\Facades\DB;

class BuildCoachContext
{
    /**
     * Build the structured JSON context array for the LLM Coach.
     *
     * @param string $playerIdentityId
     * @param string|null $targetGymLocation
     * @return array
     */
    public function execute(string $playerIdentityId, ?string $targetGymLocation = null): array
    {
        $player = PlayerIdentity::findOrFail($playerIdentityId);

        // 1. Fetch active orthopedic and training assumptions
        $assumptions = PlayerTrainingAssumption::where('player_identity_id', $playerIdentityId)
            ->where('status', 'active')
            ->get()
            ->groupBy('category');

        $formattedAssumptions = [];
        foreach ($assumptions as $category => $items) {
            $formattedAssumptions[$category] = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'confidence' => $item->confidence,
                    'notes' => $item->notes,
                ];
            })->toArray();
        }

        // 2. Fetch top Personal Records
        $prs = PlayerPr::with('exercise')
            ->where('player_identity_id', $playerIdentityId)
            ->orderBy('pr_date', 'desc')
            ->get()
            ->map(function ($pr) {
                return [
                    'exercise' => $pr->exercise ? $pr->exercise->canonical_name : $pr->exercise_name,
                    'pr_type' => $pr->pr_type,
                    'value' => (float) $pr->pr_value,
                    'unit' => $pr->pr_unit,
                    'date' => $pr->pr_date ? $pr->pr_date->format('Y-m-d') : null,
                    'notes' => $pr->notes,
                ];
            })->toArray();

        // 3. Fetch latest completed training session for debrief
        $lastSession = TrainingSession::with(['sets.exercise'])
            ->where('player_identity_id', $playerIdentityId)
            ->orderBy('session_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $debriefData = null;
        if ($lastSession) {
            $setsLogged = $lastSession->sets->map(function ($set) {
                return [
                    'set_number' => $set->set_number,
                    'exercise' => $set->exercise ? $set->exercise->canonical_name : 'Unknown',
                    'weight_lbs' => (float) $set->weight_lbs,
                    'reps' => $set->reps,
                    'duration_seconds' => $set->duration_seconds,
                    'tempo' => $set->tempo,
                    'rir' => $set->rir,
                    'set_notes' => $set->set_notes,
                ];
            })->toArray();

            $debriefData = [
                'session_id' => $lastSession->id,
                'session_date' => $lastSession->session_date ? $lastSession->session_date->format('Y-m-d') : null,
                'phase_number' => $lastSession->phase_number,
                'program_day' => $lastSession->program_day,
                'workout_name' => $lastSession->workout_name,
                'gym_location' => $lastSession->gym_location,
                'general_notes' => $lastSession->general_notes,
                'logged_sets' => $setsLogged,
            ];
        }

        // 4. Equipment context based on gym location
        $gymLocation = $targetGymLocation ?? ($lastSession ? $lastSession->gym_location : 'Planet Fitness');
        $equipmentContext = $this->resolveEquipmentContext($gymLocation);

        return [
            'player_profile' => [
                'identity_id' => $player->id,
                'display_name' => $player->display_name,
                'claim_status' => $player->claim_status,
            ],
            'active_training_assumptions' => $formattedAssumptions,
            'personal_records_summary' => $prs,
            'equipment_and_facility_context' => $equipmentContext,
            'last_session_debrief' => $debriefData,
        ];
    }

    /**
     * Resolve facility equipment ceilings and specifics.
     */
    protected function resolveEquipmentContext(string $gymLocation): array
    {
        if (str_contains(strtolower($gymLocation), 'planet fitness')) {
            return [
                'facility' => 'Planet Fitness',
                'dumbbell_ceiling_lbs' => 75.0,
                'has_barbell' => false,
                'has_smith_machine' => true,
                'has_cable_stacks' => true,
                'notes' => 'Dumbbells cap at 75 lbs. Use tempo, RIR, and unilateral load variations for progressive overload past 75 lbs.',
            ];
        }

        if (str_contains(strtolower($gymLocation), 'nxgen')) {
            return [
                'facility' => 'NXGen Fitness',
                'dumbbell_ceiling_lbs' => 120.0,
                'has_barbell' => true,
                'has_smith_machine' => true,
                'has_cable_stacks' => true,
                'notes' => 'Heavy dumbbell selection available (up to 120 lbs).',
            ];
        }

        return [
            'facility' => $gymLocation,
            'dumbbell_ceiling_lbs' => 50.0,
            'has_barbell' => false,
            'has_smith_machine' => false,
            'has_cable_stacks' => true,
            'notes' => 'Standard travel/hotel gym facility assumptions apply.',
        ];
    }
}