# Bulldog Technical Implementation Blueprint v1  
## Codex-Oriented Architecture and Build Guide

This is the technical companion to the authoritative Product + Technical Blueprint.

Its purpose is to translate the locked product architecture into a practical implementation structure for Codex.

---

# 1. Technical intent

This blueprint is designed to help implement Bulldog as:

- an event-driven sports data platform
- with persistent identity
- strong privacy defaults
- youth-safe visibility controls
- a baseball/softball-first Statbook
- future-ready for Training, Marketplace, and broader Bulldog ecosystem growth

It is **not** a final database schema, but it is intentionally close enough to guide:
- backend architecture
- data modeling
- API design
- service boundaries
- UI workflow design
- implementation sequencing

---

# 2. System architecture overview

Bulldog should be implemented as five major layers.

## 2.1 Identity layer
Handles:
- users
- authentication
- profiles
- role identities
- guardian relationships
- permissions
- privacy settings

## 2.2 Sports domain layer
Handles:
- sport
- organization
- league
- tournament
- team
- season/context
- memberships
- roster relationships

## 2.3 Statbook engine layer
Handles:
- game creation
- lineup
- game state
- event recording
- scoring logic
- review/finalization
- correction handling

## 2.4 History/recognition layer
Handles:
- aggregates
- milestones
- records
- achievements
- streaks
- timeline generation
- cards and celebration outputs

## 2.5 Presentation/access layer
Handles:
- APIs
- web app views
- shared cards
- visibility enforcement
- admin surfaces
- exports/imports

---

# 3. Recommended architectural style

## 3.1 Core approach
Use a **modular monolith** unless the current codebase strongly dictates otherwise.

Why:
- simpler to build and reason about early
- easier transactional integrity
- easier permission enforcement
- easier recalculation logic
- fewer premature distributed systems problems

This means:
- one deployable app is fine
- but with clear internal module boundaries

## 3.2 Internal module boundaries
Recommended modules:

- `identity`
- `permissions`
- `sports`
- `teams`
- `games`
- `events`
- `stats`
- `history`
- `recognition`
- `imports`
- `sharing`
- `notifications` (minimal in MVP)
- `admin`

---

# 4. Core domain model

Below is the recommended conceptual model translated into implementation-friendly objects.

---

# 5. Identity module

## 5.1 Core entities

### User
Represents a real person account.

Suggested fields:
- `id`
- `bulldog_user_id`
- `email`
- `phone` (optional)
- `password_hash` / auth provider refs
- `status`
- `date_of_birth` or age-band strategy
- `is_minor`
- `created_at`
- `updated_at`

### UserProfile
Suggested fields:
- `user_id`
- `display_name`
- `first_name`
- `last_name`
- `avatar_url`
- `bio` (optional)
- `home_location` (optional)
- `default_privacy_setting`
- `profile_metadata`

### RoleIdentity
If using a generic role identity layer.

Suggested fields:
- `id`
- `user_id`
- `role_type` (`player`, `coach`, `fan`, etc.)
- `status`
- `created_at`

### PlayerIdentity
Suggested fields:
- `id`
- `player_id_code` (e.g. `PLY-...`)
- `user_id` nullable until claimed
- `claim_status`
- `birth_year` or age-band if needed
- `primary_sport` optional
- `created_by_user_id`
- `created_at`
- `updated_at`

### CoachIdentity
Suggested fields:
- `id`
- `coach_id_code`
- `user_id`
- `created_at`

### GuardianRelationship
Suggested fields:
- `id`
- `guardian_user_id`
- `player_identity_id`
- `relationship_type`
- `verification_status`
- `created_at`

## 5.2 Key implementation rules
- one `User` may relate to many role identities
- `PlayerIdentity.user_id` may be null before claim
- a claimed player identity links to one user
- parent/guardian is modeled as relationship, not account ownership

---

# 6. Sports structure module

## 6.1 Entities

### Sport
Suggested fields:
- `id`
- `code` (`baseball`, `softball`)
- `name`
- `status`

### Organization
Suggested fields:
- `id`
- `name`
- `organization_type` (`club`, `school`, `association`, etc.)
- `location`
- `created_by_user_id`

### League
Suggested fields:
- `id`
- `name`
- `sport_id`
- `organization_id` nullable
- `season_label`
- `visibility`
- `created_by_user_id`

### Tournament
Suggested fields:
- `id`
- `name`
- `sport_id`
- `organization_id` nullable
- `start_date`
- `end_date`

### Team
Suggested fields:
- `id`
- `team_code`
- `name`
- `sport_id`
- `organization_id` nullable
- `age_group` nullable
- `season_label` nullable
- `created_by_user_id`
- `status`

### TeamMembership
Represents a person/player/coach in a team context.

Suggested fields:
- `id`
- `team_id`
- `user_id` nullable
- `player_identity_id` nullable
- `coach_identity_id` nullable
- `membership_type` (`player`, `coach`, `assistant`, `manager`, etc.)
- `jersey_number`
- `positions_json` or normalized table
- `start_date`
- `end_date`
- `status`

## 6.2 Practical note
You may later split `Team` and `TeamSeasonContext`.
For MVP, if simpler, `Team` can represent a practical real-world team instance.

That matches the current architectural tolerance.

---

# 7. Team permissions module

## 7.1 TeamRoleAssignment
Suggested fields:
- `id`
- `team_id`
- `user_id`
- `role_type` (`team_admin`, `coach`, `scorekeeper`, `viewer`)
- `granted_by_user_id`
- `created_at`

## 7.2 Permission checks should answer
- can this user edit roster?
- can this user score this game?
- can this user upload history for this team?
- can this user finalize this game?
- can this user manage visibility settings for this team?

Avoid hardcoding permissions only in UI.

---

# 8. Game module

## 8.1 Core entities

### Game
Suggested fields:
- `id`
- `game_code`
- `sport_id`
- `home_team_id`
- `away_team_id`
- `league_id` nullable
- `tournament_id` nullable
- `scheduled_at`
- `location`
- `ruleset_id` nullable
- `status` (`draft`, `in_progress`, `review`, `finalized`, `corrected`, `suspended`)
- `ownership_team_id` default home team
- `created_by_user_id`
- `finalized_at`
- `finalized_by_user_id`

### GameParticipant
Useful if needed explicitly:
- `game_id`
- `team_id`
- `role` (`home`, `away`)

### GameEditor
Suggested fields:
- `id`
- `game_id`
- `user_id`
- `editor_role` (`scorekeeper`, `reviewer`, `owner`)
- `granted_by_user_id`

### Ruleset
Suggested fields:
- `id`
- `sport_id`
- `name`
- `config_json`

---

# 9. Roster, lineup, and alignment module

## 9.1 GameRosterEntry
Represents roster participation in a game.

Suggested fields:
- `id`
- `game_id`
- `team_id`
- `player_identity_id`
- `team_membership_id` nullable
- `roster_status`
- `eligible_to_play`

## 9.2 LineupEntry
Suggested fields:
- `id`
- `game_id`
- `team_id`
- `player_identity_id`
- `batting_order_slot`
- `lineup_status`
- `entered_at`
- `exited_at`

## 9.3 DefensiveAssignment
Suggested fields:
- `id`
- `game_id`
- `team_id`
- `player_identity_id`
- `position_code`
- `effective_from_event_id`
- `effective_to_event_id` nullable

## 9.4 SubstitutionEvent / PositionChangeEvent
These can be modeled either as specialized event types or dedicated tables.
My recommendation:
- keep them in event stream
- use helper projections/tables for fast lookup if needed

---

# 10. Event engine module

## 10.1 Central idea
Every scored game action should be captured as structured events.

## 10.2 Event entity
Suggested fields:
- `id`
- `game_id`
- `sequence_number`
- `event_type`
- `event_subtype`
- `team_at_bat_id`
- `offense_player_id`
- `defense_player_ids_json` or join table
- `pitcher_player_id`
- `inning_number`
- `half_inning`
- `outs_before`
- `outs_after`
- `balls_before`
- `strikes_before`
- `balls_after`
- `strikes_after`
- `base_state_before`
- `base_state_after`
- `score_before`
- `score_after`
- `payload_json`
- `created_by_user_id`
- `created_at`
- `superseded_by_event_id` nullable
- `is_voided`

## 10.3 Event type families
- plate appearance
- baserunning
- defensive
- pitching
- lineup/admin/state transition
- correction/review actions

## 10.4 Design note
Use structured fields for high-frequency, high-value items.
Use `payload_json` for event-specific variability.
This gives flexibility without losing queryability.

---

# 11. Game state snapshot module

## 11.1 Why snapshots
The event stream is the source of truth, but snapshots help with:
- fast reconstruction
- resume suspended games
- debugging
- replay
- integrity checking

## 11.2 GameStateSnapshot
Suggested fields:
- `id`
- `game_id`
- `event_id`
- `inning_number`
- `half_inning`
- `outs`
- `balls`
- `strikes`
- `home_score`
- `away_score`
- `bases_json`
- `current_batter_id`
- `current_pitcher_id`
- `defensive_alignment_json`
- `lineup_pointer_json`
- `created_at`

## 11.3 Snapshot strategy
Do not snapshot every pitch unless needed.
Snapshot:
- inning transitions
- substitutions
- pitching changes
- suspensions
- review checkpoints
- possibly every N events

---

# 12. Stats module

## 12.1 Principle
Stats are projections/aggregates derived from events.

## 12.2 Recommended model
Maintain:
- raw events as truth
- derived stat tables or materialized projections for performance
- ability to rebuild projections from event history

## 12.3 Suggested aggregate entities

### GamePlayerStat
- `game_id`
- `player_identity_id`
- `team_id`
- `stat_key`
- `stat_value`
- `source_status`

### GameTeamStat
- `game_id`
- `team_id`
- `stat_key`
- `stat_value`

### SeasonAggregate
- `scope_type` (`player`, `team`, `coach`)
- `scope_id`
- `season_key`
- `stat_key`
- `stat_value`
- `official_status`

### CareerAggregate
- `scope_type`
- `scope_id`
- `stat_key`
- `stat_value`
- `official_status`

## 12.4 Formula stats
For values like AVG/ERA/OPS:
- either compute on read
- or cache as projection values
- but never treat them as independent raw truth

---

# 13. Recognition module

## 13.1 Milestone
Suggested fields:
- `id`
- `subject_type` (`player`, `team`, `coach`)
- `subject_id`
- `milestone_type`
- `scope_type`
- `value_reached`
- `detected_at`
- `official_status`
- `source_type`
- `fidelity_level`
- `metadata_json`

## 13.2 Record
Suggested fields:
- `id`
- `record_scope` (`personal`, `team`, `season`, `career`, etc.)
- `subject_type`
- `subject_id`
- `stat_key`
- `record_value`
- `effective_date`
- `official_status`
- `source_type`
- `metadata_json`

## 13.3 AchievementDefinition
Suggested fields:
- `id`
- `code`
- `name`
- `description`
- `category` (`performance`, `season`, etc.)
- `rule_json`
- `xp_value` optional
- `is_repeatable`
- `visibility_default`

## 13.4 AchievementAward
Suggested fields:
- `id`
- `achievement_definition_id`
- `subject_type`
- `subject_id`
- `awarded_at`
- `official_status`
- `source_type`
- `metadata_json`

## 13.5 Streak
Suggested fields:
- `id`
- `subject_type`
- `subject_id`
- `streak_type`
- `current_value`
- `best_value`
- `start_game_id`
- `end_game_id` nullable
- `official_status`

---

# 14. Timeline module

## 14.1 TimelineEntry
Suggested fields:
- `id`
- `subject_type`
- `subject_id`
- `entry_type`
- `entry_date`
- `title`
- `description`
- `source_type`
- `source_ref_id`
- `official_status`
- `visibility`

## 14.2 Timeline generation
Initial implementation should be rule-driven:
- first game
- first hit
- joined team
- championship
- milestone reached
- season completed
- coaching role started

Later it can be enriched.

---

# 15. Import module

## 15.1 HistoricalImport
Suggested fields:
- `id`
- `import_type` (`season_stats`, `game_log`, `team_history`, etc.)
- `uploaded_by_user_id`
- `context_type` (`player`, `team`, `league`)
- `context_id`
- `source_label`
- `fidelity_level`
- `verification_status`
- `notes`
- `created_at`

## 15.2 ImportedStatLine
Suggested fields:
- `id`
- `historical_import_id`
- `subject_type`
- `subject_id`
- `season_key`
- `game_date` nullable
- `stat_blob_json`
- `source_row_identifier`

## 15.3 Import rules
- import source must be preserved
- uploader must be preserved
- imports must be auditable
- imports may affect aggregates and recognition only where fidelity supports it
- imports do not grant XP

---

# 16. Sharing and visibility module

## 16.1 VisibilityRule
Suggested fields:
- `id`
- `object_type`
- `object_id`
- `visibility_level`
- `effective_for_minor`
- `set_by_user_id`
- `created_at`

## 16.2 ShareLink
Suggested fields:
- `id`
- `object_type`
- `object_id`
- `token`
- `created_by_user_id`
- `expires_at` nullable
- `is_active`
- `allow_authenticated_only`
- `metadata_json`

## 16.3 MVP recommendation
Support:
- private
- team-context
- controlled share link

Keep public non-indexed modes optional or deferred.

---

# 17. Permissions architecture

## 17.1 Recommended implementation style
Use policy/service-based authorization checks, not only role flags.

Example policies:
- `canViewPlayerProfile(user, playerIdentity, context)`
- `canEditTeamRoster(user, team)`
- `canScoreGame(user, game)`
- `canFinalizeGame(user, game)`
- `canUploadHistoricalStats(user, context)`
- `canShareCard(user, cardObject)`

## 17.2 Why
This avoids brittle permission logic and supports contextual decisions:
- minor vs adult
- coach of this team vs another team
- parent of this player vs unrelated user
- finalized vs draft game

---

# 18. Notification module

## 18.1 MVP scope
Keep lightweight.

Possible MVP notifications:
- milestone reached
- review needed
- game finalized
- roster invite
- player identity claim request

## 18.2 Avoid in MVP
- social spam
- broad feed notifications
- open messaging infrastructure

---

# 19. API design recommendations

## 19.1 API style
Either REST or typed RPC/GraphQL can work.
What matters most:
- clear permission enforcement
- transactional integrity
- predictable domain boundaries

## 19.2 Suggested API domains

### Identity APIs
- register/login
- profile
- guardian relationship
- claim player identity

### Team APIs
- create team
- manage roster
- assign team roles
- join league/tournament

### Game APIs
- create game
- manage lineup
- record event
- correct event
- review game
- finalize game
- resume suspended game

### Stats/history APIs
- player game log
- season stats
- career stats
- team history
- milestones
- achievements
- timeline

### Import APIs
- upload file
- map import
- validate
- confirm import
- view import audit

### Sharing APIs
- create share link
- revoke share link
- fetch shared card/object

---

# 20. Recalculation strategy

## 20.1 Mandatory capability
Bulldog must be able to recalculate downstream outputs when:
- event corrected
- game finalized
- game reopened and corrected
- historical import added
- historical import amended
- player identities merged later

## 20.2 Practical recommendation
Implement projection rebuild jobs or services for:
- game stats
- season aggregates
- career aggregates
- milestones/records/achievements
- timeline entries

## 20.3 Initial strategy
Start with synchronous recalculation for game-scoped updates where feasible.
Move heavier history recalculations to background jobs if needed.

---

# 21. Audit and integrity model

## 21.1 Audit requirements
Track:
- who created/edited events
- who finalized a game
- who uploaded historical data
- who changed visibility
- who granted permissions
- correction history

## 21.2 Integrity review objects
### ReviewItem
Suggested fields:
- `id`
- `game_id`
- `review_type`
- `severity`
- `description`
- `status`
- `created_at`
- `resolved_at`
- `resolved_by_user_id`

These support:
- stat mismatch warnings
- incomplete game state warnings
- questionable scoring items

---

# 22. Privacy and safety implementation notes

## 22.1 Search indexing
Ensure player pages/cards are not search-engine indexable.

Implementation options:
- no public routes for player identity by default
- `noindex`
- authenticated access or signed links
- guarded share routes

## 22.2 Minors
Minor handling should be represented in auth/policy layer, not just UI copy.

## 22.3 Messaging
Do not scaffold direct messaging into MVP architecture unless there is a very strong reason.
Keep social out of the critical path.

---

# 23. Frontend architecture guidance

## 23.1 Key surfaces
### Identity/account
- signup/login
- profile
- claim player identity
- guardian relationship management

### Team management
- team creation
- roster
- permissions
- lineup prep

### Game scoring
- create game
- live scoring interface
- Diamond-based defense/base state UI
- substitutions
- review/finalization

### History
- player career page
- team history page
- milestones
- achievements
- timeline

### Imports
- upload history
- map fields
- confirm source/fidelity labeling

### Sharing
- share card creation
- controlled link management

## 23.2 UI philosophy
- simplify for coaches first
- do not expose raw complexity unless needed
- allow “simple mode” feel over sophisticated engine

---

# 24. Recommended implementation sequence

## Phase 1: Identity and team foundations
Build:
- auth
- user/profile
- player identity
- coach identity
- guardian relationships
- team creation
- roster and team roles

## Phase 2: Game foundation
Build:
- game creation
- game state model
- lineup/defensive alignment
- event stream
- substitution/position changes
- snapshots

## Phase 3: Derived stats and review
Build:
- stat derivation engine
- review items
- finalization workflow
- correction handling
- box score and play-by-play reconstruction

## Phase 4: History and imports
Build:
- season aggregates
- career aggregates
- historical import flow
- source/fidelity labeling
- team history foundations

## Phase 5: Recognition and sharing
Build:
- milestones
- records
- system achievements
- timeline
- simple shareable cards

## Phase 6: Premium depth later
Build:
- advanced analytics
- richer progression
- social/following
- training integration
- marketplace

---

# 25. Practical technical decisions to preserve

## 25.1 Do not over-normalize too early
Some event payloads and scoring variations justify structured JSON plus typed projections.

## 25.2 Do not under-model identity
Identity, claim workflows, guardian relationships, and permission context deserve real structure from day one.

## 25.3 Do not trust cached stats as truth
Stats may be cached/projected, but events remain the truth.

## 25.4 Do not conflate player identity with user account
Keep them separable and linkable.

## 25.5 Do not make sharing equal public exposure
Controlled sharing only.

---

# 26. Suggested service boundaries in code

If organizing by domain folders/modules, consider:

```text
/modules
  /identity
  /auth
  /permissions
  /sports
  /teams
  /games
  /events
  /stats
  /history
  /recognition
  /imports
  /sharing
  /notifications
  /admin
```

Within each:
- models/entities
- services/use-cases
- policies
- repositories/data access
- DTOs/schemas
- controllers/routes

---

# 27. Example critical flows

## 27.1 Coach creates player before player account exists
1. Coach creates roster slot
2. Coach creates or links `PlayerIdentity`
3. `PlayerIdentity.user_id = null`
4. Player later registers
5. Claim workflow verifies identity
6. `PlayerIdentity.user_id` linked
7. History remains intact

## 27.2 Score a game
1. User creates game
2. Home/away teams selected
3. Lineup and defense set
4. Events recorded in sequence
5. Snapshots created periodically
6. Stats projected
7. Review items generated
8. Game finalized
9. Season/career aggregates updated
10. Milestones/records/achievements recalculated

## 27.3 Import previous season stats
1. Authorized user uploads file
2. Import mapped and validated
3. Import stored with source/fidelity/uploader
4. Imported stats linked to player/team context
5. Aggregates updated
6. Milestones/timeline evaluated where fidelity supports
7. XP unchanged

---

# 28. Testing priorities

## 28.1 Must-test domains
- permission policy tests
- event-to-stat derivation tests
- correction propagation tests
- game finalization rules
- import fidelity handling
- player identity claim workflow
- guardian relationship permissions
- visibility/share-link enforcement

## 28.2 High-risk areas
- duplicate player identity handling
- stat recalculation after correction
- lineup/substitution edge cases
- imported data causing false achievements
- minors visibility leakage
- unauthorized game edits after finalization

---

# 29. Open implementation questions

These remain for codebase-specific decisions:
- exact persistence tech and ORM model
- event storage structure details
- projection refresh strategy
- exact route/API style
- exact UI framework patterns
- exact age/guardian verification approach
- exact import format support in v1
- exact share-link security mechanics

---

# 30. Final implementation stance

This technical blueprint should guide Codex implementation around these non-negotiables:

1. **Permanent identity**
2. **Contextual permissions**
3. **Event-driven scoring**
4. **Derived stat recalculation**
5. **Historical continuity**
6. **Source-labeled imports**
7. **Youth-safe privacy**
8. **Controlled sharing**
9. **Free MVP replacing paper Statbook**
10. **Coach-first usability**

---
