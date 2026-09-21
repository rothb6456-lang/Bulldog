<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['external_code' => 'EQ-DB', 'name' => 'Dumbbells', 'category' => 'Free Weight', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-BB', 'name' => 'Barbell', 'category' => 'Free Weight', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-KB', 'name' => 'Kettlebell', 'category' => 'Free Weight', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-PLT', 'name' => 'Weight Plate', 'category' => 'Free Weight', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-SM', 'name' => 'Smith Machine', 'category' => 'Plate-Loaded Machine', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-CAB', 'name' => 'Adjustable Cable Pulley', 'category' => 'Cable', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-LF-MR', 'name' => 'LifeFitness Machine Row', 'category' => 'Selectorized Machine', 'manufacturer' => 'LifeFitness', 'gym_location' => 'Planet Fitness'],
            ['external_code' => 'EQ-LF-CP', 'name' => 'LifeFitness Chest Press', 'category' => 'Selectorized Machine', 'manufacturer' => 'LifeFitness', 'gym_location' => 'Planet Fitness'],
            ['external_code' => 'EQ-LF-LR', 'name' => 'LifeFitness Low Row', 'category' => 'Selectorized Machine', 'manufacturer' => 'LifeFitness', 'gym_location' => 'Planet Fitness'],
            ['external_code' => 'EQ-PRE-FTS', 'name' => 'Precor FTS Glide', 'category' => 'Cable', 'manufacturer' => 'Precor', 'gym_location' => 'LI Marriott'],
            ['external_code' => 'EQ-PRE-CP', 'name' => 'Precor Chest Press', 'category' => 'Selectorized Machine', 'manufacturer' => 'Precor', 'gym_location' => 'Hotel Gyms'],
            ['external_code' => 'EQ-C2', 'name' => 'Concept2 Rower', 'category' => 'Cardio', 'manufacturer' => 'Concept2', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-TM', 'name' => 'Treadmill', 'category' => 'Cardio', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-BIKE', 'name' => 'Stationary Bike', 'category' => 'Cardio', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-ER', 'name' => 'Endless Rope Machine', 'category' => 'Cardio', 'manufacturer' => 'Various', 'gym_location' => 'Planet Fitness'],
            ['external_code' => 'EQ-PLM', 'name' => 'Plate-Loaded Machine', 'category' => 'Plate-Loaded Machine', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-MCH', 'name' => 'Selectorized Machine (Generic)', 'category' => 'Selectorized Machine', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-BW', 'name' => 'Bodyweight', 'category' => 'Bodyweight', 'manufacturer' => 'N/A', 'gym_location' => 'N/A'],
            ['external_code' => 'EQ-BAND', 'name' => 'Resistance Band', 'category' => 'Band', 'manufacturer' => 'Various', 'gym_location' => 'Personal'],
            ['external_code' => 'EQ-BAND-R', 'name' => 'Resistance Band - Red', 'category' => 'Band', 'manufacturer' => 'Various', 'gym_location' => 'Personal', 'notes' => 'Light resistance'],
            ['external_code' => 'EQ-BAND-B', 'name' => 'Resistance Band - Blue', 'category' => 'Band', 'manufacturer' => 'Various', 'gym_location' => 'Personal', 'notes' => 'Medium resistance'],
            ['external_code' => 'EQ-BAND-G', 'name' => 'Resistance Band - Green', 'category' => 'Band', 'manufacturer' => 'Various', 'gym_location' => 'Personal', 'notes' => 'Heavy resistance'],
            ['external_code' => 'EQ-BENCH', 'name' => 'Adjustable Bench', 'category' => 'Accessory', 'manufacturer' => 'Various', 'gym_location' => 'Multiple'],
            ['external_code' => 'EQ-HOIST', 'name' => 'Hoist Cable Machine', 'category' => 'Cable', 'manufacturer' => 'Hoist', 'gym_location' => 'Hotel Gyms'],
            ['external_code' => 'EQ-NXGEN', 'name' => 'NXGen Equipment', 'category' => 'Various', 'manufacturer' => 'NXGen', 'gym_location' => 'NXGen Gym'],
        ];

        foreach ($rows as $row) {
            Equipment::updateOrCreate(['external_code' => $row['external_code']], $row);
        }
    }
}
