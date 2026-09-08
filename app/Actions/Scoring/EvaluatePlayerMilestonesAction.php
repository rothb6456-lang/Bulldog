<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\Milestone;
use App\Models\TimelineEntry;
use App\Models\CareerAggregate;
use App\Models\GamePlayerStat;
use Illuminate\Support\Facades\DB;

class EvaluatePlayerMilestonesAction
{
    protected array $thresholds = [
        'H' => [10 => '10 Career Hits', 50 => '50 Career Hits', 100 => '100 Career Hits'],
        'SO_pitching' => [25 => '25 Career Strikeouts', 100 => '100 Career Strikeouts'],
    ];

    public function execute(Game $game): void
    {
        DB::transaction(function () use ($game) {
            $boxStats = GamePlayerStat::where('game_id', $game->id)->get();

            foreach ($boxStats as $stat) {
                $playerId = $stat->player_identity_id;
                $key = $stat->stat_key;

                if (!array_key_exists($key, $this->thresholds)) {
                    continue;
                }

                // Query current total aggregate
                $totalValue = CareerAggregate::where('scope_type', 'player')
                    ->where('scope_id', $playerId)
                    ->where('stat_key', $key)
                    ->value('stat_value') ?? 0.00;

                foreach ($this->thresholds[$key] as $limit => $label) {
                    if ($totalValue >= $limit) {
                        // Ensure milestone has not been logged already
                        $exists = Milestone::where('subject_type', 'player')
                            ->where('subject_id', $playerId)
                            ->where('milestone_type', "career_{$key}_threshold")
                            ->where('value_reached', $limit)
                            ->exists();

                        if (!$exists) {
                            $milestone = Milestone::create([
                                'subject_type' => 'player',
                                'subject_id' => $playerId,
                                'milestone_type' => "career_{$key}_threshold",
                                'scope_type' => 'career',
                                'value_reached' => $limit,
                                'official_status' => 'official',
                                'source_type' => 'bulldog_derived',
                                'metadata_json' => ['game_id' => $game->id, 'label' => $label],
                                'detected_at' => now(),
                            ]);

                            // Auto-generate timeline highlight
                            TimelineEntry::create([
                                'subject_type' => 'player',
                                'subject_id' => $playerId,
                                'entry_type' => 'milestone_reached',
                                'entry_date' => now(),
                                'title' => "Milestone Reached: {$label}!",
                                'description' => "Hit a career milestone of {$limit} total {$key} during play.",
                                'source_type' => 'bulldog_derived',
                                'source_ref_id' => $milestone->id,
                                'visibility' => 'team',
                            ]);
                        }
                    }
                }
            }
        });
    }
}