<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SportSeeder::class,
            RulesetSeeder::class,
            SystemBadgeSeeder::class,
            TrainingGoalTemplateSeeder::class,
            EquipmentSeeder::class,
            BodyStructureSeeder::class,
            ExerciseSeeder::class,
            ExerciseNameMapSeeder::class,
        ]);
    }
}
