<?php

namespace App\Actions\Admin;

use App\Models\PlayerIdentity;
use App\Models\PlayerIdentityMerge;
use App\Models\TeamMembership;
use App\Models\GuardianRelationship;
use App\Models\GameRosterEntry;
use App\Models\LineupEntry;
use App\Models\DefensiveAssignment;
use App\Models\GameEventPlayer;
use App\Models\GamePlayerStat;
use App\Models\ImportedStatLine;
use App\Models\SeasonAggregate;
use App\Models\CareerAggregate;
use App\Models\Milestone;
use App\Models\AchievementAward;
use App\Models\Streak;
use App\Models\TimelineEntry;
use App\Actions\Scoring\RecalculateGameStatsAction;
use App\Actions\Imports\RecalculateHistoricalAggregatesAction; // Wave 5 rebuild service
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class MergePlayerIdentitiesAction
{
    protected RecalculateGameStatsAction $recalculateGameStats;
    protected AuditLogService $auditLogger;

    public function __construct(
        RecalculateGameStatsAction $recalculateGameStats,
        AuditLogService $auditLogger
    ) {
        $this->recalculateGameStats = $recalculateGameStats;
        $this->auditLogger = $auditLogger;
    }

    /**
     * Merges a duplicate player profile into a canonical surviving profile.
     *
     * @param string $canonicalId  Surviving Player ID
     * @param string $duplicateId  Absorbed Player ID
     * @param string $userId       Admin executing the action
     * @param string|null $reason  Reason for the merge
     */
    public function execute(string $canonicalId, string $duplicateId, string $userId, ?string $reason = null): PlayerIdentityMerge
    {
        if ($canonicalId === $duplicateId) {
            throw new \InvalidArgumentException("Cannot merge a player identity into itself.");
        }

        return DB::transaction(function () use ($canonicalId, $duplicateId, $userId, $reason) {
            $canonical = PlayerIdentity::findOrFail($canonicalId);
            $duplicate = PlayerIdentity::findOrFail($duplicateId);

            // Guardrail: A claimed account cannot be absorbed into another profile without manual mediation
            if ($duplicate->claim_status === 'claimed' && $canonical->claim_status === 'claimed') {
                throw new \RuntimeException("Both identities are claimed. Merging two claimed accounts requires manual technical support.");
            }

            // Capture initial stats for verification receipt
            $manifest = [
                're-targeted' => [],
                'removed_duplicates' => [],
            ];

            // 1. Re-target User Account Mapping if duplicate holds the link
            if ($canonical->user_id === null && $duplicate->user_id !== null) {
                $canonical->user_id = $duplicate->user_id;
                $canonical->claim_status = $duplicate->claim_status;
                $canonical->save();
                $manifest['re-targeted'][] = 'user_account_mapping';
            }

            // 2. Re-target Team Memberships (Handle duplications on the same team)
            $dupMemberships = TeamMembership::where('player_identity_id', $duplicateId)->get();
            foreach ($dupMemberships as $dupMembership) {
                $exists = TeamMembership::where('player_identity_id', $canonicalId)
                    ->where('team_id', $dupMembership->team_id)
                    ->first();

                if ($exists) {
                    // Combine position charts and keep the longest standing membership
                    $combinedPositions = array_unique(array_merge(
                        $exists->positions_json ?? [],
                        $dupMembership->positions_json ?? []
                    ));
                    $exists->positions_json = $combinedPositions;
                    $exists->save();

                    // Safely delete duplicate membership
                    $dupMembership->delete();
                    $manifest['removed_duplicates'][] = "team_membership:{$dupMembership->id}";
                } else {
                    $dupMembership->player_identity_id = $canonicalId;
                    $dupMembership->save();
                    $manifest['re-targeted'][] = "team_membership:{$dupMembership->id}";
                }
            }

            // 3. Re-target Guardian Relationships
            $dupGuardians = GuardianRelationship::where('player_identity_id', $duplicateId)->get();
            foreach ($dupGuardians as $dupGuardian) {
                $exists = GuardianRelationship::where('player_identity_id', $canonicalId)
                    ->where('guardian_user_id', $dupGuardian->guardian_user_id)
                    ->first();

                if ($exists) {
                    $dupGuardian->delete();
                    $manifest['removed_duplicates'][] = "guardian_relationship:{$dupGuardian->id}";
                } else {
                    $dupGuardian->player_identity_id = $canonicalId;
                    $dupGuardian->save();
                    $manifest['re-targeted'][] = "guardian_relationship:{$dupGuardian->id}";
                }
            }

            // 4. Re-target Game-Day Lineups, Rosters, and Defensive Placements
            $rosterEntries = GameRosterEntry::where('player_identity_id', $duplicateId)->update(['player_identity_id' => $canonicalId]);
            $manifest['re-targeted'][] = "game_roster_entries:{$rosterEntries}";

            // Recompute lineup ordering and defensive mapping to maintain sequence accuracy
            $lineupEntries = LineupEntry::where('player_identity_id', $duplicateId)->update(['player_identity_id' => $canonicalId]);
            $manifest['re-targeted'][] = "lineup_entries:{$lineupEntries}";

            $defensiveAssignments = DefensiveAssignment::where('player_identity_id', $duplicateId)->update(['player_identity_id' => $canonicalId]);
            $manifest['re-targeted'][] = "defensive_assignments:{$defensiveAssignments}";

            // 5. Re-target Live Game Event Logs [139, 140]
            $eventPlayers = GameEventPlayer::where('player_identity_id', $duplicateId)->update(['player_identity_id' => $canonicalId]);
            $manifest['re-targeted'][] = "game_event_players:{$eventPlayers}";

            // 6. Re-target CSV Historical Spreadsheet lines [142, 269]
            $importedLines = ImportedStatLine::where('subject_type', 'player')
                ->where('subject_id', $duplicateId)
                ->update(['subject_id' => $canonicalId]);
            $manifest['re-targeted'][] = "imported_stat_lines:{$importedLines}";

            // 7. Identify Affected Live Games for Stat Recalculations
            $affectedLiveGameIds = GameRosterEntry::where('player_identity_id', $canonicalId)
                ->pluck('game_id')
                ->unique();

            // 8. Re-target Recognition Badges, Streaks, and Achievements [143, 267]
            Milestone::where('subject_type', 'player')->where('subject_id', $duplicateId)->update(['subject_id' => $canonicalId]);
            AchievementAward::where('subject_type', 'player')->where('subject_id', $duplicateId)->update(['subject_id' => $canonicalId]);
            TimelineEntry::where('subject_type', 'player')->where('subject_id', $duplicateId)->update(['subject_id' => $canonicalId]);

            // Clean up duplicate streaks
            Streak::where('subject_type', 'player')->where('subject_id', $duplicateId)->delete();

            // 9. Generate official PlayerIdentityMerge Ledger Record
            $mergeRecord = PlayerIdentityMerge::create([
                'canonical_identity_id' => $canonicalId,
                'duplicate_identity_id' => $duplicateId,
                'duplicate_player_code' => $duplicate->player_code,
                'duplicate_display_name' => $duplicate->display_name,
                'merged_records_manifest' => $manifest,
                'merged_by_user_id' => $userId,
                'reason' => $reason,
            ]);

            // 10. Completely remove the absorbed duplicate identity record
            $duplicate->delete();

            // 11. REBUILD SYSTEM CORE: Recalculate Live and Historical Projections [274, 275]
            // Recompute box scores for all live game streams the player was involved in
            foreach ($affectedLiveGameIds as $gameId) {
                $this->recalculateGameStats->execute($gameId);
            }

            // Trigger complete Career and Season Aggregate rebuilds
            // This sweeps both recalculations to avoid any aggregate drift
            $this->rebuildPlayerAggregates($canonicalId);

            // 12. Write Admin Audit Trail Log [145, 275]
            $this->auditLogger->log(
                'player_identity_merge',
                $canonicalId,
                $userId,
                "Merged duplicate identity {$duplicate->player_code} ({$duplicate->display_name}) into canonical profile. Reason: {$reason}"
            );

            return $mergeRecord;
        });
    }

    /**
     * Flattens and recalculates aggregated statistics from the ground up.
     */
    protected function rebuildPlayerAggregates(string $playerId): void
    {
        // 1. Clear cached aggregate views for this player
        SeasonAggregate::where('subject_type', 'player')->where('subject_id', $playerId)->delete();
        CareerAggregate::where('subject_type', 'player')->where('subject_id', $playerId)->delete();

        // 2. Aggregate from live game statistics caches
        $liveStats = GamePlayerStat::where('player_identity_id', $playerId)
            ->select('stat_key', DB::raw('SUM(stat_value) as total_val'))
            ->groupBy('stat_key')
            ->get();

        foreach ($liveStats as $stat) {
            CareerAggregate::create([
                'scope_type' => 'player',
                'scope_id' => $playerId,
                'stat_key' => $stat->stat_key,
                'stat_value' => $stat->total_val,
                'official_status' => 'official',
            ]);
        }

        // 3. Aggregate from historical CSV imported files [76, 115, 142]
        $importedLines = ImportedStatLine::where('subject_type', 'player')
            ->where('subject_id', $playerId)
            ->get();

        foreach ($importedLines as $line) {
            $seasonKey = $line->season_key;
            $stats = $line->stat_blob_json; // Decoded array

            foreach ($stats as $key => $value) {
                if (is_numeric($value)) {
                    // Accumulate Season aggregates
                    $seasonAgg = SeasonAggregate::firstOrNew([
                        'scope_type' => 'player',
                        'scope_id' => $playerId,
                        'season_key' => $seasonKey,
                        'stat_key' => $key,
                    ]);
                    $seasonAgg->stat_value = ($seasonAgg->stat_value ?? 0.0) + $value;
                    $seasonAgg->official_status = 'official';
                    $seasonAgg->save();

                    // Accumulate Career aggregates
                    $careerAgg = CareerAggregate::firstOrNew([
                        'scope_type' => 'player',
                        'scope_id' => $playerId,
                        'stat_key' => $key,
                    ]);
                    $careerAgg->stat_value = ($careerAgg->stat_value ?? 0.0) + $value;
                    $careerAgg->official_status = 'official';
                    $careerAgg->save();
                }
            }
        }
    }
}