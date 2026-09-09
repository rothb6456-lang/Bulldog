# Bulldog Statbook — Technical Architecture & Architectural Decision Records (ADRs)

> **Document Version:** 1.0  
> **Last Updated:** September 2026  
> **Status:** Active (MVP Core Baseline)

---

## 1. System Vision & Core Chain

Bulldog Statbook is an event-driven sports history and progression platform. It translates on-field scorekeeping actions into longitudinal player, team, and league records.

```
Game Play-by-Play Event
  └──> Event Stream (game_events)
         ├──> Game State Snapshot (game_state_snapshots)
         ├──> Derived Projection Cache (game_player_stats / game_team_stats)
         ├──> Official Finalization Gate (game_finalization_records)
         ├──> Season & Career Aggregates (season_aggregates / career_aggregates)
         └──> Milestones, Streaks & Shareable Artifacts
```

---

## 2. Non-Negotiable Architectural Decision Records (ADRs)

### ADR-001: Separation of `User` Account and `PlayerIdentity`
* **Context:** Coaches often organize team rosters before youth athletes create personal online accounts.
* **Decision:** `User` (the authenticated account holder) and `PlayerIdentity` (the sports participant record) are strictly separate tables. A `PlayerIdentity` contains an `unclaimed` status and a null `user_id` when created by a coach.
* **Consequence:** When an athlete subsequently registers, they execute a secure claim workflow (`ClaimPlayerIdentityAction`), linking `player_identities.user_id` without altering or losing historical team performance data.

### ADR-002: Event-Driven Scoring & Rebuildable Projection Caches
* **Context:** Scorekeeping errors occur, requiring play-by-play corrections after games are saved or finalized.
* **Decision:** Raw play-by-play entries in `game_events` represent the sole authoritative source of truth. Derived tables (`game_player_stats`, `game_team_stats`, `season_aggregates`, `career_aggregates`) are projection caches.
* **Consequence:** When an event is corrected or voided, `RecalculateGameStatsAction` transactionally rebuilds the derived stat caches from the event stream, ensuring zero statistical drift.

### ADR-003: Sport-Agnostic Ruleset Engine (`config_json`)
* **Context:** Baseball and softball encompass diverse league structures (e.g., Continuous Batting Order in Little League, DP/Flex in NFHS Softball, Home Run caps in USSSA Slowpitch).
* **Decision:** `rulesets` uses a nullable `config_json` payload column rather than hard-coded database columns.
* **Consequence:** The scoring engine parses sport-specific rules dynamically on the fly, allowing the platform to support new sports or league rulesets without schema migrations.

### ADR-004: Historical CSV Imports & Provenance Firewall
* **Context:** Teams need to import historical seasons predating their use of Bulldog Statbook.
* **Decision:** All imported CSV stat lines are tagged with `historical_imports` provenance metadata, including uploader attribution, original filename, and fidelity level.
* **Consequence:**
  1. Imported stats contribute to career totals but render a visual disclaimer ("*Bulldog cannot independently verify offline imports*").
  2. Imported stats are **strictly firewalled from unlocking Experience Points (XP)** or gamification levels in the `xp_ledger`.

### ADR-005: Youth Safety, Minor Privacy, and Share Cards
* **Context:** Statbook records minor athletes whose information must be protected from public search engine scrapers.
* **Decision:** Player profiles, career views, and visual share cards are rendered under strict `noindex, nofollow` HTTP meta directives.
* **Consequence:** Share cards are accessible via tokenized links (`ShareLink`) without exposing athlete profiles to public web indexing.

### ADR-006: Transaction-Safe Duplicate Identity Merging
* **Context:** Multiple coaches may independently create unclaimed player profiles for the same athlete across different seasons or leagues.
* **Decision:** `MergePlayerIdentitiesAction` re-targets all foreign keys (`team_memberships`, `game_events`, `lineup_entries`, `imported_stat_lines`) from the duplicate profile onto a canonical target profile within a single database transaction.
* **Consequence:** All historical events are preserved, duplicate records are archived, and an audit trail is logged in `player_identity_merges`.

---

## 3. Database Schema Overview

The database uses PostgreSQL-portable data types and UUID primary/foreign keys:

```
identity/
  ├── users (id, email, password_hash, is_minor)
  ├── user_profiles (user_id, display_name)
  ├── player_identities (id, player_code, user_id [nullable], claim_status)
  └── guardian_relationships (guardian_user_id, player_identity_id)

teams/
  ├── sports (id, code, name)
  ├── teams (id, team_code, name, sport_id)
  ├── team_memberships (team_id, player_identity_id, jersey_number)
  └── team_role_assignments (team_id, user_id, role_type)

games/
  ├── rulesets (id, sport_id, name, config_json)
  ├── games (id, game_code, home_team_id, away_team_id, ruleset_id, status)
  ├── game_events (id, game_id, sequence_number, event_family, event_type, payload_json)
  ├── game_event_players (game_event_id, player_identity_id, role)
  └── game_state_snapshots (game_id, sequence_number, inning_number, base_state)

stats/
  ├── game_player_stats (game_id, player_identity_id, stat_key, stat_value)
  ├── game_team_stats (game_id, team_id, stat_key, stat_value)
  ├── season_aggregates (subject_id, season_label, stat_key, stat_value)
  └── career_aggregates (subject_id, stat_key, stat_value)

imports/
  ├── historical_imports (id, uploaded_by_user_id, source_label, fidelity_level)
  └── imported_stat_lines (historical_import_id, player_identity_id, stat_blob_json)

admin/
  ├── audit_logs (actor_user_id, action, target_type, target_id)
  └── player_identity_merges (canonical_player_id, merged_player_id, merged_by_user_id)
```

---

## 4. Policy & Authorization Model

Permission checks are enforced server-side using Laravel Policies based on team role contexts:

| Role | Manage Team / Roster | Schedule Games | Score Live Play | Merge Duplicates |
| :--- | :---: | :---: | :---: | :---: |
| **Team Admin** | ✅ | ✅ | ✅ | ❌ |
| **Coach** | ✅ | ✅ | ✅ | ❌ |
| **Scorekeeper** | ❌ | ❌ | ✅ | ❌ |
| **System Admin** | ✅ | ✅ | ✅ | ✅ |

---

## 5. Portability & Migration Strategy

To guarantee seamless future migration from MySQL on DreamHost to PostgreSQL:
1. **UUID Primary Keys:** All tables use string-formatted non-incrementing UUIDs.
2. **String Enums:** Status and role fields use string representations (`'active'`, `'unclaimed'`, `'team_admin'`) instead of DB-native enums.
3. **No Database Triggers/Stored Procedures:** All business logic, runner advancement algorithms, and stat aggregations reside entirely within Laravel Action classes.
