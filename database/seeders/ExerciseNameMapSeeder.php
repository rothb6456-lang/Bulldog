<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseNameMap;
use Illuminate\Database\Seeder;

class ExerciseNameMapSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['1 Arm Cable Curl', 'Standing Single Arm Cable Curl'],
            ['DB Romanian Deadlift (RDL)', 'Dumbbell Romanian Deadlift'],
            ['Incline Dumbell Curl (Supinated)', 'Incline Dumbbell Curl (Supinated)'],
            ['Dumbbell Incline Curl', 'Incline Dumbbell Curl (Supinated)'],
            ['Half-Kneeling Single-Arm Cable Pulldow', 'Half-Kneeling Single-Arm Cable Pulldown'],
            ['Cable: Kneeling 1Arm Overhead Extension', 'Kneeling Single Arm Rope Cable Extension'],
            ['Dumbbell: Squeeze Curl - Hammer', 'Dumbbell: Squeeze Curl - Neutral'],
            ['Standing Single Arm Extension (D-Grip)', 'Single Arm Cable Extensions'],
        ];

        foreach ($rows as [$original, $standard]) {
            $exercise = Exercise::where('canonical_name', $standard)->first();
            if (! $exercise) {
                continue; // standard name not in the seeded library yet — skip rather than guess
            }
            ExerciseNameMap::updateOrCreate(
                ['original_name' => $original],
                ['exercise_id' => $exercise->id, 'canonical_name' => $standard]
            );
        }
    }
}
