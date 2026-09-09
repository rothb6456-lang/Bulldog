<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\PlayerIdentity;
use App\Models\TrainingPhase;
use App\Models\TrainingSession;
use App\Models\TrainingSet;
use App\Models\Exercise;
use App\Models\ExerciseNameMap;

class ImportMomentumData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'momentum:import-historical {--file= : Path to the CSV/Markdown file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingests 100% of historical Momentum training sets (Phases 1-10) into the Laravel database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Momentum Historical Data Ingestion...');

        // 1. Locate Target Player Identity
        $player = PlayerIdentity::where('player_code', 'PLR-BULLDOG-001')
            ->orWhere('display_name', 'Brian Roth')
            ->first();

        if (!$player) {
            $this->error('PlayerIdentity (Brian Roth / PLR-BULLDOG-001) not found. Run MomentumHistoricalDataSeeder first.');
            return Command::FAILURE;
        }

        $filePath = $this->option('file') ?? 'database/data/Momentum_Phase_10.csv';
        if (!str_starts_with($filePath, DIRECTORY_SEPARATOR)) {
            $filePath = base_path($filePath);
        }

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            $this->line('Provide a CSV with --file=/absolute/path/to/Momentum_Phase_10.csv.');
            return Command::FAILURE;
        }

        $this->info("Parsing file: {$filePath}");

        DB::beginTransaction();

        try {
            $file = fopen($filePath, 'r');
            $header = fgetcsv($file); // Skip header row

            $importedSets = 0;
            $importedSessions = 0;
            $sessionMap = [];

            while (($row = fgetcsv($file)) !== false) {
                if (count($row) < 7 || empty($row[0])) {
                    continue;
                }

                $phaseNumber = (int) trim($row[0]);
                $dateStr = trim($row[1]);
                $setNumber = (int) trim($row[2]);
                $section = trim($row[3]);
                $exerciseName = trim($row[4]);
                $weightLbs = is_numeric($row[5]) ? (float) $row[5] : 0.0;
                $repsOrDuration = trim($row[6]);
                $notes = isset($row[11]) ? trim($row[11]) : null;

                // Format Date
                $sessionDate = date('Y-m-d', strtotime($dateStr));
                $sessionKey = "P{$phaseNumber}_{$sessionDate}";

                // 2. Resolve/Create Training Phase
                $phase = TrainingPhase::firstOrCreate(
                    [
                        'player_identity_id' => $player->id,
                        'phase_number' => $phaseNumber,
                    ],
                    [
                        'name' => "Phase {$phaseNumber}",
                        'start_date' => $sessionDate,
                        'status' => 'completed',
                    ]
                );

                // 3. Resolve/Create Training Session
                if (!isset($sessionMap[$sessionKey])) {
                    $session = TrainingSession::firstOrCreate(
                        [
                            'player_identity_id' => $player->id,
                            'phase_id' => $phase->id,
                            'session_date' => $sessionDate,
                        ],
                        [
                            'workout_name' => "Phase {$phaseNumber} Session ({$sessionDate})",
                            'gym_location' => str_contains(strtolower($notes ?? ''), 'marriott') ? 'LI Marriott' : 'Planet Fitness',
                            'general_notes' => $notes,
                        ]
                    );
                    $sessionMap[$sessionKey] = $session->id;
                    $importedSessions++;
                }

                $sessionId = $sessionMap[$sessionKey];

                // 4. Resolve Canonical Exercise ID via Alias
                $exerciseId = $this->resolveExerciseId($exerciseName);

                // Parse Reps vs Duration
                $reps = null;
                $durationSeconds = null;
                if (str_contains(strtolower($repsOrDuration), ':')) {
                    $parts = explode(':', $repsOrDuration);
                    $durationSeconds = ((int)$parts[0] * 60) + (int)$parts[1];
                } elseif (is_numeric($repsOrDuration)) {
                    $reps = (int) $repsOrDuration;
                }

                // Parse RIR and Tempo from Notes
                $rir = $this->extractPattern('/RIR\s*=?\s*([0-9\-\+\.]+)/i', $notes);
                $tempo = $this->extractPattern('/Tempo\s*([0-9E-P-C\-]+)/i', $notes);

                // 5. Insert Set
                TrainingSet::create([
                    'session_id' => $sessionId,
                    'exercise_id' => $exerciseId,
                    'set_number' => $setNumber,
                    'weight_lbs' => $weightLbs,
                    'reps' => $reps,
                    'duration_seconds' => $durationSeconds,
                    'rir' => $rir,
                    'tempo' => $tempo,
                    'set_notes' => $notes,
                ]);

                $importedSets++;
            }

            fclose($file);
            DB::commit();

            $this->info("✓ Ingestion complete!");
            $this->info("✓ Total Sessions Processed: {$importedSessions}");
            $this->info("✓ Total Sets Imported: {$importedSets}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Ingestion failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Resolves exercise alias or creates canonical lookup.
     */
    private function resolveExerciseId(string $loggedName): string
    {
        $alias = ExerciseNameMap::where('original_name', $loggedName)->first();
        if ($alias) {
            return $alias->exercise_id;
        }

        $canonical = Exercise::where('canonical_name', $loggedName)->first();
        if ($canonical) {
            return $canonical->id;
        }

        // Fallback: Create new Canonical Exercise
        $newExercise = Exercise::create([
            'canonical_name' => $loggedName,
            'category' => 'General',
            'equipment_type' => 'Dumbbell/Cable',
        ]);

        ExerciseNameMap::create([
            'original_name' => $loggedName,
            'exercise_id' => $newExercise->id,
            'canonical_name' => $newExercise->canonical_name,
        ]);

        return $newExercise->id;
    }

    private function extractPattern(string $pattern, ?string $text): ?string
    {
        if (!$text) return null;
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
