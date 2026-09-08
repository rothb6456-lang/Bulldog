<?php

namespace App\Actions\Scoring;

use App\Models\GameEvent;
use App\Models\GamePlayerStat;
use App\Models\GameTeamStat;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class RecalculateGameStatsAction
{
    /**
     * Recalculates stats for a given game.
     */
    public function execute(string $gameId): void
    {
        DB::transaction(function () use ($gameId) {
            $game = Game::findOrFail($gameId);

            // 1. Clear existing statistics for this game session to ensure clean write
            GamePlayerStat::where('game_id', $gameId)->delete();
            GameTeamStat::where('game_id', $gameId)->delete();

            // 2. Load all active events chronologically
            $events = GameEvent::where('game_id', $gameId)
                ->where('is_voided', false)
                ->with('eventPlayers')
                ->orderBy('sequence_number', 'asc')
                ->get();

            $playerStats = [];
            $teamStats = [];

            // 3. Process events to compile metrics
            foreach ($events as $event) {
                $type = $event->event_type;

                // Fetch players attributed to the event
                $batter = $event->eventPlayers->firstWhere('role', 'batter');
                $pitcher = $event->eventPlayers->firstWhere('role', 'pitcher');

                if ($type === 'single') {
                    if ($batter) {
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'AB', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'H', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'PA', 1);
                    }
                    if ($pitcher) {
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'H_allowed', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BF', 1);
                    }
                } else if ($type === 'walk') {
                    if ($batter) {
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'BB', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'PA', 1);
                    }
                    if ($pitcher) {
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BB_allowed', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BF', 1);
                    }
                } else if ($type === 'strikeout') {
                    if ($batter) {
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'AB', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'SO', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'PA', 1);
                    }
                    if ($pitcher) {
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'SO_pitching', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'IP_outs', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BF', 1);
                    }
                } else if ($type === 'pitch') {
                    if ($pitcher) {
                        $pitchResult = $event->payload_json['pitch_result'] ?? 'ball';
                        if ($pitchResult === 'strike') {
                            $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'strikes_thrown', 1);
                        } else {
                            $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'balls_thrown', 1);
                        }
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'total_pitches', 1);
                    }
                }
            }

            // 4. Batch-insert calculated statistics into database tables
            foreach ($playerStats as $playerKey => $value) {
                [$playerId, $teamId, $statKey] = explode('::', $playerKey);
                GamePlayerStat::create([
                    'game_id' => $gameId,
                    'player_identity_id' => $playerId,
                    'team_id' => $teamId,
                    'stat_key' => $statKey,
                    'stat_value' => $value,
                ]);

                // Record parallel team totals [265]
                $this->increment($teamStats, $gameId, null, $teamId, $statKey, $value);
            }

            foreach ($teamStats as $teamKey => $value) {
                [,, $teamId, $statKey] = explode('::', 'null::null::' . $teamKey);
                GameTeamStat::create([
                    'game_id' => $gameId,
                    'team_id' => $teamId,
                    'stat_key' => $statKey,
                    'stat_value' => $value,
                ]);
            }
        });
    }

    private function increment(array &$statsArray, string $gameId, ?string $playerId, string $teamId, string $statKey, float $amount): void
    {
        $key = $playerId
            ? "{$playerId}::{$teamId}::{$statKey}"
            : "{$teamId}::{$statKey}";

        if (!isset($statsArray[$key])) {
            $statsArray[$key] = 0;
        }
        $statsArray[$key] += $amount;
    }
}