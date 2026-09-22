<?php

namespace App\Actions\Training;

use App\Models\AchievementAward;
use App\Models\AchievementDefinition;
use App\Models\BodyStructure;
use App\Models\PlayerIdentity;
use App\Models\TimelineEntry;
use App\Models\XpLedger;
use Illuminate\Support\Facades\DB;

class AwardBodyStructureLearnedAction
{
    /**
     * Awards the one-time ANATOMY_INSIGHT achievement (+ XP, if the identity
     * is claimed) the first time a given player learns about a given body
     * structure via the Coach guide. Mirrors AwardSystemAchievementsAction's
     * pattern exactly: dedup via metadata_json, skip XP for unclaimed
     * identities per ADR-001, log a TimelineEntry for consistency.
     *
     * Returns ['awarded' => bool, 'xp' => int] so the controller can tell the
     * frontend whether this was a new award (worth celebrating) or a repeat
     * (silent no-op).
     */
    public function execute(PlayerIdentity $player, BodyStructure $structure): array
    {
        return DB::transaction(function () use ($player, $structure) {
            $definition = AchievementDefinition::where('code', 'ANATOMY_INSIGHT')->first();

            if (!$definition) {
                return ['awarded' => false, 'xp' => 0];
            }

            $exists = AchievementAward::where('achievement_definition_id', $definition->id)
                ->where('subject_type', 'player')
                ->where('subject_id', $player->id)
                ->where('metadata_json->body_structure_id', $structure->id)
                ->exists();

            if ($exists) {
                return ['awarded' => false, 'xp' => 0];
            }

            $award = AchievementAward::create([
                'achievement_definition_id' => $definition->id,
                'subject_type' => 'player',
                'subject_id' => $player->id,
                'official_status' => 'official',
                'source_type' => 'momentum_coach_guide',
                'metadata_json' => ['body_structure_id' => $structure->id, 'body_structure_name' => $structure->name],
                'awarded_at' => now(),
            ]);

            TimelineEntry::create([
                'subject_type' => 'player',
                'subject_id' => $player->id,
                'entry_type' => 'achievement_earned',
                'entry_date' => now(),
                'title' => "Learned about the {$structure->name}",
                'description' => "Read the Coach guide on the {$structure->name} — {$structure->short_description}",
                'source_type' => 'momentum_coach_guide',
                'source_ref_id' => $award->id,
                'visibility' => 'team',
            ]);

            $xpAwarded = 0;
            // Skip XP for unclaimed identities (no linked user) per ADR-001 — the
            // award/timeline entry above still records against the identity itself.
            if ($player->user_id) {
                XpLedger::create([
                    'user_id' => $player->user_id,
                    'xp_amount' => $definition->xp_value,
                    'source_type' => 'achievement_unlocked',
                    'source_ref_id' => $award->id,
                    'description' => "Earned '{$definition->name}': learned about the {$structure->name}.",
                ]);
                $xpAwarded = $definition->xp_value;
            }

            return ['awarded' => true, 'xp' => $xpAwarded];
        });
    }
}
