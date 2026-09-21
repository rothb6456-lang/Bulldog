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
use App\Models\PlayerTrainingProfile;
use App\Models\PlayerTrainingGoal;
use App\Models\PlayerEquipmentAccess;
use App\Models\PlayerTrainingGuardrail;
use App\Models\PlayerCoachLink;
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

            // 8.5. Re-target Momentum training profile, goals, equipment access, guardrails, and coach links.
            // This must happen before the duplicate identity is deleted below (step 10) -- these tables'
            // player_identity_id foreign keys cascadeOnDelete, so skipping this step would silently
            // destroy the athlete's training data on merge.

            // Profile is 1:1; canonical's copy wins if both exist, duplicate's copy is discarded.
            if (PlayerTrainingProfile::where('player_identity_id', $canonicalId)->exists()) {
                $removedProfile = PlayerTrainingProfile::where('player_identity_id', $duplicateId)->first();
                if ($removedProfile) {
                    $removedProfile->delete();
                    $manifest['removed_duplicates'][] = "player_training_profile:{$removedProfile->id}";
                }
            } else {
                $retargetedProfile = PlayerTrainingProfile::where('player_identity_id', $duplicateId)
                    ->update(['player_identity_id' => $canonicalId]);
                if ($retargetedProfile) {
                    $manifest['re-targeted'][] = "player_training_profile:{$retargetedProfile}";
                }
            }

            // Goals and guardrails intentionally preserve history (e.g., a resolved guardrail years
            // before a new, unrelated one) -- no dedup, straightforward bulk re-target.
            $retargetedGoals = PlayerTrainingGoal::where('player_identity_id', $duplicateId)
                ->update(['player_identity_id' => $canonicalId]);
            $manifest['re-targeted'][] = "player_training_goals:{$retargetedGoals}";

            $retargetedGuardrails = PlayerTrainingGuardrail::where('player_identity_id', $duplicateId)
                ->update(['player_identity_id' => $canonicalId]);
            $manifest['re-targeted'][] = "player_training_guardrails:{$retargetedGuardrails}";

            // Equipment access is hard-unique on (player_identity_id, equipment_id); a blind bulk
            // update would violate that constraint on overlapping equipment, so check per-row.
            // Custom (non-canonical) entries have a null equipment_id, so those are matched on
            // custom_label instead — dropping true duplicates, keeping genuinely distinct custom entries.
            $dupEquipment = PlayerEquipmentAccess::where('player_identity_id', $duplicateId)->get();
            foreach ($dupEquipment as $dupItem) {
                $exists = PlayerEquipmentAccess::where('player_identity_id', $canonicalId)
                    ->when(
                        $dupItem->equipment_id,
                        fn ($q) => $q->where('equipment_id', $dupItem->equipment_id),
                        fn ($q) => $q->whereNull('equipment_id')->where('custom_label', $dupItem->custom_label)
                    )
                    ->first();

                if ($exists) {
                    $dupItem->delete();
                    $manifest['removed_duplicates'][] = "player_equipment_access:{$dupItem->id}";
                } else {
                    $dupItem->player_identity_id = $canonicalId;
                    $dupItem->save();
                    $manifest['re-targeted'][] = "player_equipment_access:{$dupItem->id}";
                }
            }

            // Coach links are unique on (coach_user_id, player_identity_id); same per-row check,
            // preferring an already-active link over a merged-in pending/revoked duplicate.
            $dupCoachLinks = PlayerCoachLink::where('player_identity_id', $duplicateId)->get();
            foreach ($dupCoachLinks as $dupLink) {
                $exists = PlayerCoachLink::where('player_identity_id', $canonicalId)
                    ->where('coach_user_id', $dupLink->coach_user_id)
                    ->first();

                if ($exists) {
                    if ($exists->status !== 'active' && $dupLink->status === 'active') {
                        $exists->status = 'active';
                        $exists->responded_at = $dupLink->responded_at;
                        $exists->save();
                    }
                    $dupLink->delete();
                    $manifest['removed_duplicates'][] = "player_coach_link:{$dupLink->id}";
                } else {
                    $dupLink->player_identity_id = $canonicalId;
                    $dupLink->save();
                    $manifest['re-targeted'][] = "player_coach_link:{$dupLink->id}";
                }
            }

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