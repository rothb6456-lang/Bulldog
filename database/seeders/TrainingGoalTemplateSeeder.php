<?php

namespace Database\Seeders;

use App\Models\TrainingGoalTemplate;
use Illuminate\Database\Seeder;

class TrainingGoalTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'code' => 'CONSISTENCY_4WK',
                'title' => 'Train consistently for 4 weeks',
                'description' => 'Complete your planned sessions each week for a full month.',
                'category' => 'consistency',
                'metric_key' => 'sessions_per_week',
                'default_target_unit' => 'sessions',
            ],
            [
                'code' => 'STRENGTH_1RM_PLUS',
                'title' => 'Increase a lift\'s working max',
                'description' => 'Add measurable load to a key lift over the course of a phase.',
                'category' => 'strength',
                'metric_key' => 'exercise_1rm',
                'default_target_unit' => 'lbs',
            ],
            [
                'code' => 'BODYWEIGHT_TARGET',
                'title' => 'Reach a target bodyweight',
                'description' => 'Move toward a target bodyweight in support of a broader goal.',
                'category' => 'general_fitness',
                'metric_key' => 'bodyweight',
                'default_target_unit' => 'lbs',
            ],
            [
                'code' => 'RETURN_TO_PLAY',
                'title' => 'Return to play after injury',
                'description' => 'Progress rehab work toward full clearance for sport participation.',
                'category' => 'return_to_play',
                'metric_key' => null,
                'default_target_unit' => null,
            ],
            [
                'code' => 'MOBILITY_IMPROVEMENT',
                'title' => 'Improve mobility in a problem area',
                'description' => 'Build range of motion and control in a specific joint or movement.',
                'category' => 'mobility',
                'metric_key' => null,
                'default_target_unit' => null,
            ],
            [
                'code' => 'GENERAL_FITNESS',
                'title' => 'General fitness and health',
                'description' => 'No specific performance target -- just building a sustainable training habit.',
                'category' => 'general_fitness',
                'metric_key' => null,
                'default_target_unit' => null,
            ],
        ];

        foreach ($templates as $template) {
            TrainingGoalTemplate::updateOrCreate(
                ['code' => $template['code']],
                $template + ['is_active' => true]
            );
        }
    }
}
