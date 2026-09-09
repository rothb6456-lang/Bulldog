# Bulldog Statbook — MVP Master Technical & Architectural Reference

> **Document Type:** Master Architecture Source Reference  
> **Status:** Active (Production Baseline - Waves 1 through 7 Complete)  
> **Audience:** Developers, AI Engineering Assistants, Technical Architects  
> **Target Subdomain:** `statbook.bulldogstats.com`

---

## 1. Executive Product Architecture Summary

Bulldog Statbook is the flagship software application within the **Bulldog Stats & Sports Innovation** ecosystem. It provides a free, digital baseball and softball Statbook that turns every game into persistent player, team, season, and career history.

### The Core Chain
`Game Play-by-Play Events` → `State Snapshots` → `Derived Projections` → `Game Finalization` → `Season & Career Aggregates` → `Milestones & Share Cards`

---

## 2. Core Architectural Pillars & Decision Records

### 1. Permanent Identity Model (`User` vs. `PlayerIdentity`)
- **User Account:** Represents the authenticated human user (`users` table).
- **Player Identity:** Represents the sports participant (`player_identities` table).
- **Unclaimed Workflow:** Coaches can add unclaimed player profiles (`user_id = null`, `claim_status = 'unclaimed'`) to team rosters before the athlete or parent registers an account.
- **Claim Workflow:** When an athlete registers, they run `ClaimPlayerIdentityAction`, linking `player_identities.user_id` without breaking or altering historical statistics.

### 2. Event-Driven Scoring Engine
- Raw play-by-play events (`game_events`) serve as the authoritative source of truth.
- `game_player_stats` and `game_team_stats` are derived projection caches.
- `RecalculateGameStatsAction` can transactionally reprocess any game's event stream at any time to recover from scoring errors without data drift.

### 3. Dynamic Ruleset Architecture (`config_json`)
- Competition rules (e.g., Continuous Batting Order, mercy rules, pitch limits, DP/Flex, home run caps) are stored in `rulesets.config_json`.
- This enables sport-agnostic flexibility across Little League, NFHS High School Baseball, NFHS Softball, USSSA, and USA Softball (ASA).

### 4. Historical CSV Import & Provenance Firewall
- Past offline seasons can be uploaded via `HistoricalImport`.
- Imported stat lines (`imported_stat_lines`) carry source labels and render fidelity disclaimers on player career pages.
- **XP Firewall:** Imported stat lines are strictly blocked from granting Experience Points (XP) in `xp_ledger`.

### 5. Youth Safety, Minor Privacy & Controlled Sharing
- Player profiles, career views, and visual share cards are protected by `noindex, nofollow` HTTP headers.
- Share cards use tokenized links (`ShareLink`) for controlled sharing without open search engine indexing.

### 6. Transaction-Safe Duplicate Identity Resolver
- Administrators can merge split player profiles using `MergePlayerIdentitiesAction`.
- All team memberships, game events, lineup entries, and CSV imports are re-linked to a canonical target identity within a single transaction, followed by an automated stat recalculation and audit logging.

---

## 3. Wave-by-Wave Implementation Overview

### Wave 1: Core Identity & Auth
- Implemented `User`, `UserProfile`, `PlayerIdentity`, `CoachIdentity`, `GuardianRelationship`.
- Integrated non-incrementing UUID traits and Sanctum auth token handling.

### Wave 2: Teams, Rosters & Roles
- Implemented `Sport`, `Team`, `TeamMembership`, `TeamRoleAssignment`.
- Configured team-level policies (`TeamPolicy`) granting contextual permissions for `team_admin`, `coach`, `scorekeeper`, and `viewer`.

### Wave 3: Games, Lineups & Ruleset Seeders
- Implemented `Game`, `Ruleset`, `GameRosterEntry`, `LineupEntry`, `DefensiveAssignment`.
- Built `RulesetSeeder` providing realistic configurations for Little League, NFHS Baseball, NFHS Softball, USSSA Slowpitch, and USA Softball.
- Created `GameController` and interactive Blade views (`games/create.blade.php`, `games/show.blade.php`).

### Wave 4: Live Event Scoring & Real-Time Stats
- Implemented `GameEvent`, `GameEventPlayer`, `GameStateSnapshot`, `GamePlayerStat`, `GameTeamStat`.
- Built `RecordScoringEventAction` and `RecalculateGameStatsAction`.
- Created live scoring interface (`games/score.blade.php`) and verified the entire play-by-play sequence with `ScoringIntegrationTest.php` (27 passing assertions).

### Wave 5: Career History & Historical Imports
- Implemented `SeasonAggregate`, `CareerAggregate`, `HistoricalImport`, `ImportedStatLine`.
- Created multipage CSV upload wizard (`imports/create.blade.php`) and player career view (`profile/career.blade.php`).

### Wave 6: Milestones & Share Cards
- Implemented `Milestone`, `Record`, `AchievementDefinition`, `AchievementAward`, `Streak`.
- Built `ProcessPostGameAchievements` post-game finalization listener.
- Created minor-safe share card view (`sharing/share-card.blade.php`).

### Wave 7: Hardening & Duplicate Resolver
- Implemented `PlayerIdentityMerge` and `MergePlayerIdentitiesAction`.
- Built admin control panel (`admin/duplicates.blade.php`) for resolving split accounts.

---

## 4. Complete Database Schema Reference

```sql
-- Core Identity
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_minor BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP, updated_at TIMESTAMP
);

CREATE TABLE player_identities (
    id CHAR(36) PRIMARY KEY,
    player_code VARCHAR(50) UNIQUE NOT NULL,
    user_id CHAR(36) NULL,
    claim_status VARCHAR(50) DEFAULT 'unclaimed',
    display_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP, updated_at TIMESTAMP
);

-- Teams & Rosters
CREATE TABLE teams (
    id CHAR(36) PRIMARY KEY,
    team_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    sport_id CHAR(36) NOT NULL,
    created_at TIMESTAMP, updated_at TIMESTAMP
);

CREATE TABLE team_memberships (
    id CHAR(36) PRIMARY KEY,
    team_id CHAR(36) NOT NULL,
    player_identity_id CHAR(36) NOT NULL,
    jersey_number VARCHAR(10) NULL,
    membership_type VARCHAR(50) DEFAULT 'player',
    created_at TIMESTAMP, updated_at TIMESTAMP
);

-- Games & Play-by-Play
CREATE TABLE games (
    id CHAR(36) PRIMARY KEY,
    game_code VARCHAR(50) UNIQUE NOT NULL,
    home_team_id CHAR(36) NOT NULL,
    away_team_id CHAR(36) NOT NULL,
    ruleset_id CHAR(36) NOT NULL,
    status VARCHAR(50) DEFAULT 'draft',
    created_at TIMESTAMP, updated_at TIMESTAMP
);

CREATE TABLE game_events (
    id CHAR(36) PRIMARY KEY,
    game_id CHAR(36) NOT NULL,
    sequence_number INT NOT NULL,
    event_family VARCHAR(50) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    inning_number INT NOT NULL,
    half_inning VARCHAR(10) NOT NULL,
    base_state_after VARCHAR(10) DEFAULT '000',
    payload_json JSON NULL,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    UNIQUE(game_id, sequence_number)
);

-- Statistics Projection Cache
CREATE TABLE game_player_stats (
    id CHAR(36) PRIMARY KEY,
    game_id CHAR(36) NOT NULL,
    player_identity_id CHAR(36) NOT NULL,
    team_id CHAR(36) NOT NULL,
    stat_key VARCHAR(50) NOT NULL,
    stat_value DECIMAL(8,2) DEFAULT 0.00,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    UNIQUE(game_id, player_identity_id, stat_key)
);
```

---

## 5. Primary API Routes Map (`routes/api.php`)

```php
// Public Auth
Route::post('/v1/auth/register', [RegisterController::class, 'store']);
Route::post('/v1/auth/login', [LoginController::class, 'store']);

// Authenticated Endpoints
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Current Session
    Route::get('/auth/me', [MeController::class, 'show']);
    
    // Team & Roster
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::post('/teams/{team}/roster', [RosterController::class, 'store']);
    
    // Live Event Scoring
    Route::post('/games/{game}/events', [GameEventController::class, 'store']);
    Route::get('/games/{game}/events', [GameEventController::class, 'index']);
    
    // Historical CSV Imports
    Route::post('/imports', [ImportController::class, 'store']);
    
    // Duplicate Identity Merges
    Route::post('/admin/players/merge', [DuplicateMergeController::class, 'store']);
});
```

---

## 6. Verification & Test Suite Summary

The backend integration suite `tests/Feature/Scoring/ScoringIntegrationTest.php` runs against an in-memory SQLite database (`RefreshDatabase`) and validates:
1. Sequential pitch counts (balls/strikes tracking).
2. Base hit (single) execution and runner advancement.
3. Walk (Base on Balls) forced runner advancement.
4. Strikeout logic and pitcher out accounting (`IP_outs`).
5. Real-time statistical recalculations across `game_player_stats` and `game_team_stats`.

All 27 assertions pass cleanly with 0 failures.
