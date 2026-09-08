<?php

namespace Database\Seeders;

use App\Models\AchievementDefinition;
use Illuminate\Database\Seeder;

class SystemBadgeSeeder extends Seeder
{
    /**
     * Seed the standard system achievements.
     */
    public function run(): void
    {
        AchievementDefinition::updateOrCreate(
            ['code' => 'ACE_IN_HOLE'],
            [
                'name' => 'Ace in the Hole',
                'description' => 'Strike out 10 or more hitters on the mound in a single game.',
                'category' => 'performance',
                'rule_json' => ['metric' => 'SO_pitching', 'threshold' => 10],
                'xp_value' => 250,
                'is_repeatable' => true,
            ]
        );
    }
}
