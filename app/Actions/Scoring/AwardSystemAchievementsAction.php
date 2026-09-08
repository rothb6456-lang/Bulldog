<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\AchievementDefinition;
use App\Models\AchievementAward;
use App\Models\TimelineEntry;
use App\Models\XpLedger;
use App\Models\GamePlayerStat;
use App\Models\PlayerIdentity;
use Illuminate\Support\Facades\DB;

class AwardSystemAchievementsAction
{
    public function execute(Game $game): void
    {
        DB::transaction(function () use ($game) {
            // Find game players with 10+ strikeouts on the mound
            $pitchers = GamePlayerStat::where('game_id', $game->id)
                ->where('stat_key', 'SO_pitching')
                ->where('stat_value', '>=', 10)
                ->get();

            $aceDefinition = AchievementDefinition::where('code', 'ACE_IN_HOLE')->first();

            if ($aceDefinition) {
                foreach ($pitchers as $stat) {
                    $playerId = $stat->player_identity_id;

                    // Ensure this player has not earned this achievement in this specific game
                    $exists = AchievementAward::where('achievement_definition_id', $aceDefinition->id)
                        ->where('subject_type', 'player')
                        ->where('subject_id', $playerId)
                        ->where('metadata_json->game_id', $game->id)
                        ->exists();

                    if (!$exists) {
                        $award = AchievementAward::create([
                            'achievement_definition_id' => $aceDefinition->id,
                            'subject_type' => 'player',
                            'subject_id' => $playerId,
                            'official_status' => 'official',
                            'source_type' => 'bulldog_derived',
                            'metadata_json' => ['game_id' => $game->id],
                            'awarded_at' => now(),
                        ]);

                        // Generate Timeline highlight
                        TimelineEntry::create([
                            'subject_type' => 'player',
                            'subject_id' => $playerId,
                            'entry_type' => 'achievement_earned',
                            'entry_date' => now(),
                            'title' => "Earned '{$aceDefinition->name}' Badge",
                            'description' => "Dominating performance on the mound with 10+ game strikeouts.",
                            'source_type' => 'bulldog_derived',
                            'source_ref_id' => $award->id,
                            'visibility' => 'team',
                        ]);

                        // Secure XP ledger mapping — Only trigger if player identity is linked and NOT historical import [77, 144]
                        $player = PlayerIdentity::find($playerId);
                        if ($player && $player->user_id) {
                            XpLedger::create([
                                'user_id' => $player->user_id,
                                'xp_amount' => $aceDefinition->xp_value,
                                'source_type' => 'achievement_unlocked',
                                'source_ref_id' => $award->id,
                                'description' => "Earned system badge: '{$aceDefinition->name}' during official play.",
                            ]);
                        }
                    }
                }
            }
        });
    }
}