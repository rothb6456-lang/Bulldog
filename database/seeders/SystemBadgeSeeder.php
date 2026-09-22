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

        AchievementDefinition::updateOrCreate(
            ['code' => 'ANATOMY_INSIGHT'],
            [
                'name' => 'Anatomy Insight',
                'description' => 'Learned about a muscle, tendon, ligament, or functional structure via the Momentum Coach guide.',
                'category' => 'education',
                'rule_json' => ['trigger' => 'manual', 'event' => 'body_structure_learned'],
                'xp_value' => 10,
                'is_repeatable' => true,
            ]
        );
    }
}
