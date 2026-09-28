<?php

namespace Database\Seeders;

use App\Models\BodyStructure;
use App\Models\Equipment;
use App\Models\Exercise;
use Illuminate\Database\Seeder;

class FreeExerciseDbBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['canonical_name' => 'Barbell Squat', 'movement_pattern' => 'Squat', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-BB', 'laterality' => 'bilateral', 'primary_muscle' => 'Quads', 'secondary_muscles' => ['Calves', 'Glutes', 'Hamstrings', 'Lower Back'], 'notes' => 'Foundational lower-body compound lift.', 'shoulder_safety_notes' => 'Keep the bar over midfoot; brace before descending.'],
            ['canonical_name' => 'Barbell Deadlift', 'movement_pattern' => 'Hinge', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-BB', 'laterality' => 'bilateral', 'primary_muscle' => 'Lower Back', 'secondary_muscles' => ['Calves', 'Forearms', 'Glutes', 'Hamstrings', 'Lats', 'Back', 'Quads', 'Traps'], 'notes' => 'Foundational posterior-chain compound lift.', 'shoulder_safety_notes' => 'Neutral spine throughout; hinge at the hips, not the low back.'],
            ['canonical_name' => 'Barbell Shoulder Press', 'movement_pattern' => 'Push', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-BB', 'laterality' => 'bilateral', 'primary_muscle' => 'Shoulders', 'secondary_muscles' => ['Chest', 'Triceps'], 'notes' => 'Standing bilateral overhead press.', 'shoulder_safety_notes' => 'Avoid excessive lumbar arch; brace the core to keep ribs down.'],
            ['canonical_name' => 'Pull-Up', 'movement_pattern' => 'Pull', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-BW', 'laterality' => 'bilateral', 'primary_muscle' => 'Lats', 'secondary_muscles' => ['Biceps', 'Back'], 'notes' => 'Pronated (overhand) grip vertical pull.', 'shoulder_safety_notes' => 'Full dead-hang start; avoid kipping if training strict strength.'],
            ['canonical_name' => 'Chin-Up', 'movement_pattern' => 'Pull', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-BW', 'laterality' => 'bilateral', 'primary_muscle' => 'Lats', 'secondary_muscles' => ['Biceps', 'Forearms', 'Back'], 'notes' => 'Supinated (underhand) grip vertical pull.', 'shoulder_safety_notes' => 'Supinated grip reduces shoulder strain vs. a wide pull-up grip for some lifters.'],
            ['canonical_name' => 'Wide-Grip Lat Pulldown', 'movement_pattern' => 'Pull', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-CAB', 'laterality' => 'bilateral', 'primary_muscle' => 'Lats', 'secondary_muscles' => ['Biceps', 'Back', 'Shoulders'], 'notes' => 'Machine alternative to pull-ups, or a way to build toward one.', 'shoulder_safety_notes' => 'Pull to the upper chest, not behind the neck.'],
            ['canonical_name' => 'Barbell Curl', 'movement_pattern' => 'Isolation', 'exercise_category' => 'Isolation', 'equipment_code' => 'EQ-BB', 'laterality' => 'bilateral', 'primary_muscle' => 'Biceps', 'secondary_muscles' => ['Forearms'], 'notes' => 'Standard bilateral biceps isolation.', 'shoulder_safety_notes' => 'Avoid swinging the torso to generate momentum.'],
            ['canonical_name' => 'Dumbbell Bench Press', 'movement_pattern' => 'Push', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-DB', 'laterality' => 'bilateral', 'primary_muscle' => 'Chest', 'secondary_muscles' => ['Shoulders', 'Triceps'], 'notes' => 'Standard bilateral horizontal press.', 'shoulder_safety_notes' => 'Greater shoulder ROM than a barbell press - stop shy of full stretch if shoulder-sensitive.'],
            ['canonical_name' => 'Leg Press', 'movement_pattern' => 'Squat', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-PLM', 'laterality' => 'bilateral', 'primary_muscle' => 'Quads', 'secondary_muscles' => ['Calves', 'Glutes', 'Hamstrings'], 'notes' => 'Machine alternative/supplement to squatting.', 'shoulder_safety_notes' => 'Avoid locking the knees hard at the top; keep the low back flat against the pad.'],
            ['canonical_name' => 'Leg Extension', 'movement_pattern' => 'Isolation', 'exercise_category' => 'Isolation', 'equipment_code' => 'EQ-MCH', 'laterality' => 'bilateral', 'primary_muscle' => 'Quads', 'secondary_muscles' => [], 'notes' => 'Direct quadriceps isolation.', 'shoulder_safety_notes' => 'Light-to-moderate load recommended if any anterior knee sensitivity.'],
            ['canonical_name' => 'Lying Leg Curl', 'movement_pattern' => 'Isolation', 'exercise_category' => 'Isolation', 'equipment_code' => 'EQ-MCH', 'laterality' => 'bilateral', 'primary_muscle' => 'Hamstrings', 'secondary_muscles' => [], 'notes' => 'Direct hamstring isolation.', 'shoulder_safety_notes' => 'Avoid the hips lifting off the pad - isolate the hamstring.'],
            ['canonical_name' => 'Barbell Hip Thrust', 'movement_pattern' => 'Hinge', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-BB', 'laterality' => 'bilateral', 'primary_muscle' => 'Glutes', 'secondary_muscles' => ['Calves', 'Hamstrings'], 'notes' => 'Primary glute-focused hip extension exercise.', 'shoulder_safety_notes' => 'Pad the bar; drive through the heels and avoid overextending the low back at lockout.'],
            ['canonical_name' => 'Plank', 'movement_pattern' => 'Hold', 'exercise_category' => 'Hold', 'equipment_code' => 'EQ-BW', 'laterality' => 'bilateral', 'primary_muscle' => 'Core', 'secondary_muscles' => [], 'notes' => 'Core anti-extension hold.', 'shoulder_safety_notes' => 'Keep hips level with the shoulders; avoid sagging or piking.', 'is_time_based' => true],
            ['canonical_name' => 'Dips (Triceps)', 'movement_pattern' => 'Push', 'exercise_category' => 'Compound', 'equipment_code' => 'EQ-BW', 'laterality' => 'bilateral', 'primary_muscle' => 'Triceps', 'secondary_muscles' => ['Chest', 'Shoulders'], 'notes' => 'Bodyweight triceps/chest compound press.', 'shoulder_safety_notes' => 'Limit depth if shoulders are sensitive - stop when the upper arm is parallel to the floor.'],
        ];

        foreach ($rows as $row) {
            $equipment = Equipment::where('external_code', $row['equipment_code'])->first();

            $exercise = Exercise::updateOrCreate(
                ['canonical_name' => $row['canonical_name']],
                [
                    'movement_pattern' => $row['movement_pattern'],
                    'exercise_category' => $row['exercise_category'],
                    'equipment_id' => $equipment?->id,
                    'laterality' => $row['laterality'],
                    'is_time_based' => $row['is_time_based'] ?? false,
                    'is_distance_based' => false,
                    'shoulder_safety_notes' => $row['shoulder_safety_notes'],
                    'notes' => $row['notes'],
                ]
            );

            $structureIds = [];
            if ($primary = BodyStructure::where('name', $row['primary_muscle'])->first()) {
                $structureIds[$primary->id] = ['role' => 'primary'];
            }
            foreach ($row['secondary_muscles'] as $name) {
                if ($structure = BodyStructure::where('name', $name)->first()) {
                    $structureIds[$structure->id] = ['role' => 'secondary'];
                }
            }
            $exercise->bodyStructures()->sync($structureIds);
        }
    }
}
