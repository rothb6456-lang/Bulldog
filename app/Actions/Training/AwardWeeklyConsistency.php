<?php

namespace App\Actions\Training;

use App\Models\{TrainingSession, PlayerIdentity, AchievementAward, AchievementDefinition, XpLedger};
use Illuminate\Support\Facades\DB;

class AwardWeeklyConsistency
{
    public function execute(TrainingSession $session): bool
    {
        $phase = $session->phase;
        if (! $phase || $phase->sessions_per_week < 1) { return false; }
        return DB::transaction(function () use ($session, $phase) {
            $player = PlayerIdentity::lockForUpdate()->findOrFail($session->player_identity_id);
            $start = $session->session_date->copy()->startOfWeek();
            $week = $start->toDateString();
            $count = TrainingSession::where('player_identity_id', $player->id)->where('phase_id', $phase->id)
                ->whereBetween('session_date', [$start, $start->copy()->endOfWeek()])->count();
            if ($count < $phase->sessions_per_week) { return false; }
            $definition = AchievementDefinition::where('code', 'WEEKLY_CONSISTENCY')->first();
            if (! $definition) { return false; }
            if (AchievementAward::where('achievement_definition_id', $definition->id)->where('subject_id', $player->id)->where('metadata_json->week', $week)->exists()) { return false; }
            $award = AchievementAward::create(['achievement_definition_id' => $definition->id, 'subject_type' => 'player', 'subject_id' => $player->id, 'official_status' => 'official', 'source_type' => 'momentum_training', 'metadata_json' => ['week' => $week, 'phase_id' => $phase->id], 'awarded_at' => now()]);
            if ($player->user_id) { XpLedger::create(['user_id' => $player->user_id, 'xp_amount' => $definition->xp_value, 'source_type' => 'achievement_unlocked', 'source_ref_id' => $award->id, 'description' => 'Completed the weekly training target.']); }
            return true;
        });
    }
}
