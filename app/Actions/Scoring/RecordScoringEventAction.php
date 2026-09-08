<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\GameEvent;
use App\Models\GameStateSnapshot;
use Illuminate\Support\Facades\DB;

class RecordScoringEventAction
{
    protected RecalculateGameStatsAction $recalculateStats;

    public function __construct(RecalculateGameStatsAction $recalculateStats)
    {
        $this->recalculateStats = $recalculateStats;
    }

    /**
     * Execute appending a live game scoring event.
     */
    public function execute(string $gameId, array $data, string $userId): GameEvent
    {
        return DB::transaction(function () use ($gameId, $data, $userId) {
            $game = Game::findOrFail($gameId);

            // 1. Fetch latest state snapshot to get baseline tracking
            $latestSnapshot = GameStateSnapshot::where('game_id', $gameId)
                ->orderBy('sequence_number', 'desc')
                ->first();

            $nextSequence = $latestSnapshot ? ($latestSnapshot->sequence_number + 1) : 1;

            $ballsBefore = $latestSnapshot ? $latestSnapshot->balls : 0;
            $strikesBefore = $latestSnapshot ? $latestSnapshot->strikes : 0;
            $outsBefore = $latestSnapshot ? $latestSnapshot->outs : 0;
            $baseStateBefore = $latestSnapshot ? $latestSnapshot->base_state : '000';

            $homeScoreBefore = $latestSnapshot ? $latestSnapshot->score_home : 0;
            $awayScoreBefore = $latestSnapshot ? $latestSnapshot->score_away : 0;

            $inning = $latestSnapshot ? $latestSnapshot->inning_number : 1;
            $halfInning = $latestSnapshot ? $latestSnapshot->half_inning : 'top';

            // 2. Resolve on-field events and update counts
            $ballsAfter = $ballsBefore;
            $strikesAfter = $strikesBefore;
            $outsAfter = $outsBefore;
            $baseStateAfter = $baseStateBefore;
            $homeScoreAfter = $homeScoreBefore;
            $awayScoreAfter = $awayScoreBefore;

            $type = $data['event_type']; // 'pitch', 'single', 'walk', 'strikeout'

            if ($type === 'pitch') {
                $pitchResult = $data['payload']['pitch_result'] ?? 'ball';
                if ($pitchResult === 'strike') {
                    $strikesAfter++;
                } else if ($pitchResult === 'ball') {
                    $ballsAfter++;
                }
            } else if ($type === 'single') {
                // Single: Empty count, place runner on 1st, advance others
                $ballsAfter = 0;
                $strikesAfter = 0;
                $baseStateAfter = $this->advanceRunners($baseStateBefore, 1, $homeScoreAfter, $awayScoreAfter, $halfInning);
            } else if ($type === 'walk') {
                $ballsAfter = 0;
                $strikesAfter = 0;
                $baseStateAfter = $this->advanceRunners($baseStateBefore, 1, $homeScoreAfter, $awayScoreAfter, $halfInning, true);
            } else if ($type === 'strikeout') {
                $ballsAfter = 0;
                $strikesAfter = 0;
                $outsAfter++;
            }

            // Inning transition check (3 outs)
            if ($outsAfter >= 3) {
                $outsAfter = 0;
                $ballsAfter = 0;
                $strikesAfter = 0;
                $baseStateAfter = '000';

                if ($halfInning === 'top') {
                    $halfInning = 'bottom';
                } else {
                    $halfInning = 'top';
                    $inning++;
                }
            }

            // 3. Create the authoritative Game Event record
            $event = GameEvent::create([
                'game_id' => $gameId,
                'sequence_number' => $nextSequence,
                'event_family' => $data['event_family'],
                'event_type' => $type,
                'inning_number' => $inning,
                'half_inning' => $halfInning,
                'outs_before' => $outsBefore,
                'outs_after' => $outsAfter,
                'balls_before' => $ballsBefore,
                'strikes_before' => $strikesBefore,
                'balls_after' => $ballsAfter,
                'strikes_after' => $strikesAfter,
                'base_state_before' => $baseStateBefore,
                'base_state_after' => $baseStateAfter,
                'score_home_before' => $homeScoreBefore,
                'score_home_after' => $homeScoreAfter,
                'score_away_before' => $awayScoreBefore,
                'score_away_after' => $awayScoreAfter,
                'payload_json' => $data['payload'] ?? null,
                'created_by_user_id' => $userId,
            ]);

            // 4. Map attributed players participating in this event
            if (!empty($data['players'])) {
                foreach ($data['players'] as $player) {
                    $event->eventPlayers()->create([
                        'player_identity_id' => $player['player_identity_id'],
                        'role' => $player['role'],
                    ]);
                }
            }

            // 5. Save the updated Game State Snapshot
            GameStateSnapshot::create([
                'game_id' => $gameId,
                'game_event_id' => $event->id,
                'sequence_number' => $nextSequence,
                'inning_number' => $inning,
                'half_inning' => $halfInning,
                'outs' => $outsAfter,
                'balls' => $ballsAfter,
                'strikes' => $strikesAfter,
                'score_home' => $homeScoreAfter,
                'score_away' => $awayScoreAfter,
                'base_state' => $baseStateAfter,
                'current_batter_id' => $data['current_batter_id'] ?? ($latestSnapshot->current_batter_id ?? null),
                'current_pitcher_id' => $data['current_pitcher_id'] ?? ($latestSnapshot->current_pitcher_id ?? null),
                'lineup_pointers' => $latestSnapshot ? $latestSnapshot->lineup_pointers : null,
                'defensive_alignment' => $latestSnapshot ? $latestSnapshot->defensive_alignment : null,
            ]);

            // 6. Trigger synchronous real-time statistical projection updates [275]
            $this->recalculateStats->execute($gameId);

            return $event;
        });
    }

    /**
     * Minimal helper to advance runners on a single or walk.
     */
    protected function advanceRunners(string $baseState, int $bases, int &$homeScore, int &$awayScore, string $halfInning, bool $isForced = false): string
    {
        $first = $baseState[0] === '1';
        $second = $baseState[1] === '1';
        $third = $baseState[2] === '1';

        if ($isForced) {
            // For walks (forced advancement rules)
            if ($first) {
                if ($second) {
                    if ($third) {
                        $this->awardRun($homeScore, $awayScore, $halfInning);
                    }
                    $third = true;
                }
                $second = true;
            }
            $first = true;
        } else {
            // For standard hit (advancing everyone by N bases)
            for ($i = 0; $i < $bases; $i++) {
                if ($third) {
                    $this->awardRun($homeScore, $awayScore, $halfInning);
                    $third = false;
                }
                if ($second) {
                    $third = true;
                    $second = false;
                }
                if ($first) {
                    $second = true;
                    $first = false;
                }
                if ($i === 0) {
                    $first = true;
                }
            }
        }

        return ($first ? '1' : '0') . ($second ? '1' : '0') . ($third ? '1' : '0');
    }

    protected function awardRun(int &$homeScore, int &$awayScore, string $halfInning): void
    {
        if ($halfInning === 'top') {
            $awayScore++;
        } else {
            $homeScore++;
        }
    }
}
