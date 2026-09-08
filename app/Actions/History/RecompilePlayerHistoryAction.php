<?php

namespace App\Actions\History;

use App\Models\PlayerIdentity;
use App\Models\GamePlayerStat;
use App\Models\ImportedStatLine;
use App\Models\SeasonAggregate;
use App\Models\CareerAggregate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecompilePlayerHistoryAction
{
    /**
     * Completely recompile high-performance stats for a specific athlete.
     * Guaranteed to be completely auditable and rebuildable on demand [148, 274].
     */
    public function execute(string $playerIdentityId): void
    {
        DB::transaction(function () use ($playerIdentityId) {
            // 1. Clear current cached aggregates to prevent calculation drift [153]
            SeasonAggregate::where('subject_type', 'player')->where('subject_id', $playerIdentityId)->delete();
            CareerAggregate::where('subject_type', 'player')->where('subject_id', $playerIdentityId)->delete();

            $compiledSeasons = [];
            $compiledCareer = [];

            // 2. Fetch authoritative live game statistics (e.g., from Live scoring engine) [141]
            $liveStats = GamePlayerStat::where('player_identity_id', $playerIdentityId)
                ->with(['game'])
                ->get();

            foreach ($liveStats as $stat) {
                // Determine season context dynamically (using season_label from associated Team context or fallback) [65]
                $season = $stat->game->season_label ?? 'Spring 2026';
                $key = $stat->stat_key;
                $val = (float)$stat->stat_value;

                $this->accumulate($compiledSeasons, $season, $key, $val, 'bulldog_derived', 'event_complete');
                $this->accumulate($compiledCareer, 'career', $key, $val, 'bulldog_derived', 'event_complete');
            }

            // 3. Fetch unverified approved historical imports [75, 114]
            $importedLines = ImportedStatLine::where('subject_type', 'player')
                ->where('subject_id', $playerIdentityId)
                ->whereHas('historicalImport', function ($query) {
                    $query->where('verification_status', 'approved'); // Only approved imports enter career totals [83]
                })
                ->with(['historicalImport'])
                ->get();

            foreach ($importedLines as $line) {
                $season = $line->season_key;
                $import = $line->historicalImport;

                $sourceType = $import->import_type === 'verified_import' ? 'verified_import' : 'uploaded_import';
                $fidelity = $import->fidelity_level;

                foreach ($line->stat_blob_json as $key => $val) {
                    $this->accumulate($compiledSeasons, $season, $key, (float)$val, $sourceType, $fidelity);
                    $this->accumulate($compiledCareer, 'career', $key, (float)$val, $sourceType, $fidelity);
                }
            }

            // 4. Flush Season aggregates to high-performance tables
            foreach ($compiledSeasons as $seasonLabel => $metrics) {
                foreach ($metrics as $statKey => $meta) {
                    SeasonAggregate::create([
                        'id' => Str::uuid()->toString(),
                        'subject_type' => 'player',
                        'subject_id' => $playerIdentityId,
                        'season_label' => $seasonLabel,
                        'stat_key' => $statKey,
                        'stat_value' => $meta['value'],
                        'source_type' => $meta['source_type'],
                        'fidelity_level' => $meta['fidelity'],
                    ]);
                }
            }

            // 5. Flush Career aggregates to high-performance tables
            foreach ($compiledCareer['career'] ?? [] as $statKey => $meta) {
                CareerAggregate::create([
                    'id' => Str::uuid()->toString(),
                    'subject_type' => 'player',
                    'subject_id' => $playerIdentityId,
                    'stat_key' => $statKey,
                    'stat_value' => $meta['value'],
                    'source_type' => $meta['source_type'],
                    'fidelity_level' => $meta['fidelity'],
                ]);
            }
        });
    }

    protected function accumulate(array &$compiled, string $scopeKey, string $statKey, float $value, string $sourceType, string $fidelity): void
    {
        if (!isset($compiled[$scopeKey])) {
            $compiled[$scopeKey] = [];
        }

        if (!isset($compiled[$scopeKey][$statKey])) {
            $compiled[$scopeKey][$statKey] = [
                'value' => 0.0,
                'source_type' => $sourceType,
                'fidelity' => $fidelity,
            ];
        } else {
            $compiled[$scopeKey][$statKey]['value'] += $value;

            // Source mixing logic: If data is mixed between live scoring and imported stats, label it mixed [150]
            if ($compiled[$scopeKey][$statKey]['source_type'] !== $sourceType) {
                $compiled[$scopeKey][$statKey]['source_type'] = 'mixed';
            }

            // Fidelity downgrade logic: The overall aggregate takes on the lowest common fidelity denominator
            if ($fidelity === 'season_totals' || $compiled[$scopeKey][$statKey]['fidelity'] === 'season_totals') {
                $compiled[$scopeKey][$statKey]['fidelity'] = 'season_totals';
            } else if ($fidelity === 'box_score' || $compiled[$scopeKey][$statKey]['fidelity'] === 'box_score') {
                $compiled[$scopeKey][$statKey]['fidelity'] = 'box_score';
            }
        }
    }
}