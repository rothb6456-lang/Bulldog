<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\Streak;
use App\Models\GamePlayerStat;
use Illuminate\Support\Facades\DB;

class EvaluateGameStreaksAction
{
    public function execute(Game $game): void
    {
        DB::transaction(function () use ($game) {
            // Fetch all players who registered at least one Plate Appearance (PA) in this game
            $participants = GamePlayerStat::where('game_id', $game->id)
                ->where('stat_key', 'PA')
                ->where('stat_value', '>', 0)
                ->get();

            foreach ($participants as $statRecord) {
                $playerId = $statRecord->player_identity_id;

                // Check Hits and On-Base occurrences for this game
                $hits = GamePlayerStat::where('game_id', $game->id)
                    ->where('player_identity_id', $playerId)
                    ->where('stat_key', 'H')
                    ->value('stat_value') ?? 0;

                $walks = GamePlayerStat::where('game_id', $game->id)
                    ->where('player_identity_id', $playerId)
                    ->where('stat_key', 'BB')
                    ->value('stat_value') ?? 0;

                $this->updatePlayerStreak($playerId, 'hitting_streak', $hits > 0, $game->id);
                $this->updatePlayerStreak($playerId, 'on_base_streak', ($hits > 0 || $walks > 0), $game->id);
            }
        });
    }

    protected function updatePlayerStreak(string $playerId, string $type, bool $isExtended, string $gameId): void
    {
        $streak = Streak::firstOrCreate(
            ['subject_type' => 'player', 'subject_id' => $playerId, 'streak_type' => $type],
            ['current_value' => 0, 'best_value' => 0]
        );

        if ($isExtended) {
            $streak->current_value++;
            if ($streak->current_value === 1) {
                $streak->start_game_id = $gameId;
            }
            if ($streak->current_value > $streak->best_value) {
                $streak->best_value = $streak->current_value;
            }
            $streak->end_game_id = null;
        } else {
            if ($streak->current_value > 0) {
                $streak->end_game_id = $gameId;
            }
            $streak->current_value = 0;
        }

        $streak->save();
    }
}