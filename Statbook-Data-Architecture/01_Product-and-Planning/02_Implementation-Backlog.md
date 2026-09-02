

# Bulldog Codex Implementation Backlog v1

This backlog is organized to reflect:
- architectural dependencies
- MVP-first delivery
- coach-first usability
- protection against scope drift

It is intentionally phased so Codex can build in a practical sequence.

---

# 1. Delivery strategy

## Primary MVP objective
Ship a **free, excellent baseball/softball Statbook** that:
- supports team setup and rostering
- supports live game scoring
- derives traditional stats automatically
- finalizes games cleanly
- preserves player/team/season/career history
- supports imported prior stats
- supports milestones and simple cards
- enforces privacy and youth-safe visibility

## Product adoption priority
1. **Coach/team workflow**
2. **Parent-assisted player workflow**
3. **Player-facing history/cards**
4. Social later

---

# 2. Phase structure

I recommend 7 implementation phases:

- **Phase 0** — Foundation / repo / guardrails
- **Phase 1** — Identity, auth, and relationships
- **Phase 2** — Teams, rosters, and permissions
- **Phase 3** — Game creation, lineup, and state engine
- **Phase 4** — Event scoring + derived stats + finalization
- **Phase 5** — History, career aggregation, and imports
- **Phase 6** — Milestones, records, achievements, timeline, cards
- **Phase 7** — Hardening, admin, reporting, and MVP polish

---

# 3. Phase 0 — Foundation / guardrails

## Goals
Create implementation scaffolding that prevents drift.

## Work items
### 0.1 Architecture setup
- define module structure
- define coding conventions
- define domain boundaries
- define env/config strategy
- define migration strategy

### 0.2 Core technical decisions
- choose DB + ORM
- choose auth/session strategy
- choose background job strategy
- choose file upload/storage strategy
- choose audit logging approach

### 0.3 Shared platform utilities
- error handling
- validation layer
- authorization policy framework
- logging/monitoring
- feature flag system
- seed data strategy

### 0.4 Privacy and security guardrails
- add default no-index strategy for player pages/cards
- define role/policy middleware
- define minor-sensitive access rules in code structure
- define share-link token strategy

## Deliverables
- running app baseline
- migrations working
- module skeletons in place
- auth + policy scaffolding ready
- deployment pipeline stable

## Exit criteria
- developers can build by domain, not ad hoc
- permission checks have a consistent implementation path
- privacy is not deferred

---

# 4. Phase 1 — Identity, auth, and relationships

## Goals
Implement the permanent Bulldog identity model correctly first.

## Work items
### 1.1 User/auth
- user registration
- login/logout
- password reset or auth provider integration
- account status model
- session handling

### 1.2 User profile
- basic profile fields
- avatar
- display name
- profile settings
- account preferences

### 1.3 Player identity
- create `PlayerIdentity`
- unclaimed vs claimed model
- create player ID codes
- link player identity to user
- claim workflow foundations

### 1.4 Coach identity
- create `CoachIdentity`
- link to user
- coach role foundations

### 1.5 Guardian relationships
- create guardian relationship model
- guardian verification status flow
- permission policies for guardian access

### 1.6 Privacy primitives
- user privacy settings
- minor/adult distinction model
- visibility enum foundations

## UI surfaces
- signup/login
- account profile
- create/claim player identity
- guardian relationship management (basic)

## Deliverables
- one person = one Bulldog account
- player identities can exist before claim
- guardian relationship exists in system
- privacy settings foundations exist

## Exit criteria
- coach-created players can later be claimed
- minors/adults can be differentiated in policy checks
- user/profile/player/coach relationships are stable

---

# 5. Phase 2 — Teams, rosters, and permissions

## Goals
Support coach/team admin workflows before game scoring begins.

## Work items
### 2.1 Team creation
- create team
- sport selection
- basic team metadata
- age group / season label if applicable
- organization link optional

### 2.2 Team roles
- assign team admin
- assign coach
- assign scorekeeper
- assign viewer/assistant if needed

### 2.3 Roster management
- create roster slots
- attach existing player identity
- create new unclaimed player identity
- set jersey number
- set positions
- activate/deactivate roster membership

### 2.4 Team membership model
- player team memberships
- coach memberships
- history-safe membership records

### 2.5 Team visibility
- team-level visibility settings
- team-context view permissions
- role-aware roster visibility

### 2.6 Permissions engine
- can manage roster
- can assign team roles
- can upload history for team
- can start a game for team

## UI surfaces
- create team flow
- roster page
- team settings
- invite/assign roles
- player search/link flow

## Deliverables
- coaches can set up teams cleanly
- players can be added even before they register
- permissions are enforced in team context

## Exit criteria
- a coach can create a real usable roster
- team admin / coach / scorekeeper distinction exists
- team-context authority works

---

# 6. Phase 3 — Game creation, lineup, and state engine

## Goals
Create the minimal but real game engine foundation.

## Work items
### 3.1 Game creation
- create game
- select sport
- select home/away team
- set date/time/location
- set league/tournament optional
- default ownership to home team

### 3.2 Ruleset support
- basic ruleset object
- baseball/softball defaults
- room for later variations

### 3.3 Game roster loading
- load roster into game context
- mark eligible players
- support roster adjustments

### 3.4 Lineup management
- set batting order
- set starters
- separate roster from lineup

### 3.5 Defensive alignment
- assign fielding positions
- support Diamond UI later or initially simple form
- preserve position state

### 3.6 Game state engine
- inning
- half inning
- outs
- score
- batter
- pitcher
- runner base state
- lineup pointers

### 3.7 State snapshots
- create `GameStateSnapshot` strategy
- store meaningful checkpoints

### 3.8 Game editor permissions
- assign game editors
- assign scorekeepers
- assign reviewer/finalizer roles if needed

## UI surfaces
- start game flow
- lineup screen
- defensive alignment screen
- basic live game shell

## Deliverables
- a valid game can be created and prepared
- state model exists before scoring events
- home team ownership is enforced

## Exit criteria
- users can enter a game with lineups and state initialized
- game has enough structure for event scoring

---

# 7. Phase 4 — Event scoring + derived stats + finalization

## Goals
This is the heart of MVP.

## Work items
### 4.1 Event stream implementation
- canonical event entity
- sequence ordering
- event payload structure
- correction/supersede support

### 4.2 Group 1–4 event coverage
Implement event families for:
- plate appearance events
- baserunning events
- defensive events
- pitching events

### 4.3 Group 5 state transitions
- substitutions
- position changes
- pitching changes
- inning transitions
- suspension/resumption
- review/finalization workflow

### 4.4 Derived statistics engine
Build projection logic for:
- batting
- pitching
- fielding
- team stats
- line score
- LOB
- box score outputs

### 4.5 Review engine
- create review items
- integrity checks
- scoring mismatch detection
- unresolved issue surfacing

### 4.6 Finalization workflow
- draft → review → finalized
- authorization for finalization
- prevent ordinary edits after finalization

### 4.7 Correction workflow
- undo/edit/correct play
- rebuild downstream projections
- preserve audit trail

### 4.8 Reconstruction outputs
- scorebook reconstruction
- play-by-play reconstruction
- box score rendering

## UI surfaces
- live scoring interface
- event entry controls
- review screen
- finalize game screen
- corrected game audit view (basic)

## Deliverables
- coaches/scorekeepers can score a game end-to-end
- stats calculate automatically
- finalized games are official
- corrections recalculate downstream outputs

## Exit criteria
- paper Statbook replacement is real
- score integrity is believable
- official games feed history safely

---

# 8. Phase 5 — History, career aggregation, and imports

## Goals
Turn scored games into persistent Bulldog history, and let users bring prior history in.

## Work items
### 5.1 Aggregation layer
- game-to-season projections
- season-to-career projections
- team history aggregation
- player history aggregation

### 5.2 Player career page foundation
- season summaries
- career totals
- team history
- recent game log

### 5.3 Team history foundation
- roster history
- game history
- season summaries
- former player relationships

### 5.4 Historical import pipeline
- upload file/manual import entry
- import parsing
- field mapping
- source labeling
- uploader attribution
- fidelity level assignment

### 5.5 Import validation
- validate minimum fields
- show import preview
- accept/reject import
- context permission checks

### 5.6 Import effects on history
- imported stats roll into career/season totals
- imported stats affect milestones/records where fidelity allows
- imported stats do not grant XP

### 5.7 Import disclosure
- “Bulldog cannot independently verify uploaded/non-official data”
- source/fidelity display on relevant views

## UI surfaces
- player career page v1
- team history page v1
- import wizard
- import review/confirmation UI

## Deliverables
- scored games now produce longitudinal history
- pre-Bulldog seasons can be brought in
- users can trust source labeling

## Exit criteria
- a player’s history is not trapped in one season
- prior stats can be imported with visible provenance
- history surfaces are usable and meaningful

---

# 9. Phase 6 — Milestones, records, achievements, timeline, cards

## Goals
Make Bulldog feel special without bloating MVP.

## Work items
### 6.1 Milestone engine
- firsts
- thresholds
- official vs provisional distinction
- source-aware milestone detection

### 6.2 Record engine
- personal records
- team records
- season records
- career records

### 6.3 Achievement engine
- system-defined achievements only
- automatic unlock rules
- source/fidelity-aware eligibility

### 6.4 Timeline generation
- auto-generated timeline entries
- join team
- first game
- first hit
- season completion
- milestone reached
- championship

### 6.5 Basic streaks
- hitting streak
- on-base streak
- team win streak

### 6.6 Shareable cards foundation
- player card
- milestone card
- game-performance card basic form
- controlled share links
- no indexing

### 6.7 Cosmetic progression foundation
- XP/event hooks for Bulldog usage only
- cosmetic-only unlock plumbing if included at MVP edge
- imported data excluded from XP

## UI surfaces
- milestone panel
- records panel
- achievement section
- timeline section
- card preview/share UI

## Deliverables
- Bulldog history becomes celebratory
- milestones and records feel alive
- cards/timeline reinforce identity without unsafe exposure

## Exit criteria
- a player/team can see meaningful history moments
- shareable artifacts exist in controlled ways
- no open public discoverability is introduced

---

# 10. Phase 7 — Hardening, admin, reporting, MVP polish

## Goals
Make MVP reliable, safe, and coach-friendly.

## Work items
### 7.1 Permissions hardening
- verify all role/context rules
- minors policy testing
- guardian access validation
- share-link visibility validation

### 7.2 Audit/admin tools
- audit logs
- correction history views
- import audit views
- support tools for duplicate player handling later

### 7.3 QA on scoring edge cases
- substitutions
- pitcher changes
- correction propagation
- suspended/resumed games
- lineup edge cases

### 7.4 Coach-first UX polish
- reduce clicks in game scoring
- improve event entry clarity
- optimize lineup flow
- improve review/finalization experience

### 7.5 Parent-friendly history polish
- simpler cards
- readable career/history views
- import explanation copy

### 7.6 Reporting/export basics
- export box score
- export season summary basic
- printable/shareable outputs where useful

### 7.7 Launch readiness
- onboarding copy
- privacy copy
- historical import limitation copy
- “social coming soon” optional placeholder copy

## Deliverables
- MVP is stable enough to test with real coaches/teams/parents
- trust and usability are improved
- launch messaging aligns with architecture

## Exit criteria
- pilot-ready
- safe
- reliable
- understandable to real users

---

# 11. Priority ranking inside MVP

If Codex needs even tighter prioritization, use this order:

## Tier 1 — Must have
- auth/user/profile
- player identity + claim model
- team creation + roster
- team roles/permissions
- game creation
- lineup/defensive state
- event scoring
- derived stats
- review/finalization
- player/team season/career aggregation
- privacy defaults and minors protections

## Tier 2 — Strong MVP features
- historical imports
- milestones
- records
- timeline basics
- controlled share cards
- team history basics

## Tier 3 — Nice-to-have within MVP if capacity allows
- basic streaks
- achievement cabinet
- better card styling
- export/reporting polish
- cosmetic XP plumbing

## Tier 4 — Explicitly later
- custom coach goals
- development achievements
- rich XP economy
- feed/social layer
- messaging
- advanced analytics
- league-wide deep functionality
- training integration
- marketplace

---

# 12. Suggested backlog format for Codex

Use backlog items with:
- **ID**
- **title**
- **phase**
- **description**
- **dependencies**
- **acceptance criteria**
- **notes / architecture constraints**

Example:

### `ID-PLAYER-001`
**Title:** Create unclaimed PlayerIdentity  
**Phase:** 1  
**Description:** Allow coach/team admin to create a player identity without linked user account.  
**Dependencies:** user auth, team permissions  
**Acceptance criteria:**  
- coach can create player record from roster workflow  
- player gets unique Player ID  
- `user_id` remains null until claimed  
- player can later be linked without losing team history  
**Constraints:**  
- do not make PlayerIdentity identical to User  
- audit creator

---

# 13. Recommended first 20 backlog items

Here are the first 20 I would create immediately.

## Foundation
1. Project/module architecture scaffold
2. Auth/session setup
3. Policy-based authorization scaffold
4. Audit logging foundation

## Identity
5. User + profile models
6. PlayerIdentity model with claim status
7. CoachIdentity model
8. GuardianRelationship model
9. Claim PlayerIdentity flow

## Teams
10. Team model and create-team flow
11. TeamRoleAssignment model
12. Team roster management flow
13. Add existing player to roster flow
14. Create unclaimed player from roster flow

## Games
15. Game model and create-game flow
16. Game editor permission model
17. Game roster loading
18. Lineup entry model and lineup setup flow
19. Defensive assignment model
20. Core game state engine

That would be the best place to begin.

---

# 14. Architecture constraints Codex should not violate

These are non-negotiable backlog constraints.

## Identity
- do not merge User and PlayerIdentity into one table/object without preserving separate semantics
- do not make parent the owner of player identity
- do not assume one player = one team

## Permissions
- do not rely on UI-only permission checks
- do not allow player pages/cards to become publicly indexed
- do not add open messaging/social into MVP critical path

## Scoring/stat engine
- do not store formula stats as only authoritative truth
- do not bypass event history when making stat corrections
- do not allow finalized games to be casually edited

## Imports/history
- do not import historical stats without source labeling
- do not let imported stats create XP
- do not overstate fidelity of imported data

---

# 15. Recommended immediate next action

My recommendation is:

## Step 1
Turn this backlog into a machine-usable task board, likely:
- Epic
- Feature
- Story / task

## Step 2
Start with **Phase 1 and Phase 2 only**

Do **not** jump straight into cards, achievements, or imports before:
- identity
- permissions
- team/roster model
- game model

are solid.

---
