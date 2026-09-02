# Bulldog Product + Technical Blueprint v1  
## Authoritative Source of Truth

---

# 1. Vision

## 1.1 Bulldog umbrella
**Bulldog Stats and Sports Innovation** is the umbrella ecosystem.

It is not limited to the Statbook. It includes, over time:

- **Bulldog Identity**
- **Bulldog Statbook**
- **Bulldog Training**
- **Bulldog Marketplace**
- future sports innovations, tools, and services

## 1.2 Bulldog Statbook
**Bulldog Statbook** is the flagship product and the first major execution surface of the Bulldog ecosystem.

Its core purpose is:

> A free baseball/softball Statbook that turns every game into persistent player, team, season, and career history.

It is also aligned with these product standards:

- the easiest and most powerful sports Statbook
- the sports history book for players, teams, and leagues
- a fun, coach-friendly, player-friendly, parent-friendly experience

## 1.3 Core product thesis
Bulldog is **not** merely a scorekeeping app.

Bulldog is a **persistent sports identity, history, and progression platform**.

The Statbook is the primary engine that creates the data.

### Core chain
Game  
→ events  
→ statistics  
→ season history  
→ career history  
→ milestones  
→ records  
→ achievements  
→ cards / timelines / review / future insights

---

# 2. Product principles

## 2.1 Foundational principles
1. **One person = one permanent Bulldog User ID**
2. **User ≠ Player**
3. Player, Coach, Parent, Fan, Admin are contextual roles/relationships, not separate accounts
4. The Statbook records what happened; Bulldog determines what it means
5. Raw events are authoritative; derived statistics are recalculable
6. History persists across teams, seasons, leagues, and life stages
7. Privacy and youth safety are architectural, not add-ons
8. The free Statbook must fully replace a traditional paper Statbook
9. Coaches are the primary early adoption engine
10. Social is not MVP priority; the Statbook must shine first

## 2.2 UX philosophy
Bulldog should feel:
- easy to start
- familiar to traditional scorekeepers
- modern in presentation
- fun without being gimmicky
- trustworthy
- longitudinal
- nostalgic and meaningful

---

# 3. Ecosystem architecture

## 3.1 Bulldog Core
Shared ecosystem capabilities:
- identity
- authentication
- roles/relationships
- permissions
- privacy
- notifications
- account management

## 3.2 Sports Core
Shared sports domain capabilities:
- sport
- organization
- league
- tournament
- team
- season/team-season context
- player
- coach
- game
- event
- statistic
- milestone
- achievement
- record

## 3.3 Bulldog products
### Bulldog Statbook
Baseball/softball scoring engine and history platform.

### Bulldog Training
Training/development product sharing Bulldog identity.

### Bulldog Marketplace
Future product/services/coaching innovation marketplace sharing Bulldog identity.

---

# 4. Identity architecture

## 4.1 Permanent Bulldog identity
Every real person gets one permanent **Bulldog User ID**.

That identity persists regardless of role.

A single person may simultaneously be:
- player
- coach
- parent
- fan
- team admin
- league admin

## 4.2 Role identities
A person may have persistent role identities attached to the same underlying person account.

Examples:
- **Bulldog User ID**: BDG-123456
- **Player ID**: PLY-123456
- **Coach ID**: COA-123456

These are not separate accounts.

They are role identities attached to one person.

## 4.3 Relationship model
Bulldog must support:
- person identity
- role identity
- contextual membership

### Example
BDG-123456  
→ Player  
→ Baseball  
→ Team A  
→ 2026

BDG-123456  
→ Coach  
→ Softball  
→ Team B  
→ 2030

BDG-123456  
→ Parent of  
→ PLY-789012

This model avoids duplication and preserves longitudinal continuity.

---

# 5. User, role, and relationship model

## 5.1 Core distinction
- **User** = person/account identity
- **Player** = sports participation identity
- **Coach** = sports participation identity
- **Parent** = verified relationship
- **Fan** = following/activity relationship
- **Admin** = context-specific authority

## 5.2 Parent model
Parents/guardians have their own Bulldog identity and a verified relationship to a player identity.

They do not own the player’s identity as the primary account object.

### Model
Parent User ID  
→ verified guardian relationship  
→ Player ID

## 5.3 Player claim model
A coach may create a player record before the player has an account.

Later:
- the player creates an account
- claims/connects the Player ID
- the identity becomes linked securely

This workflow is foundational.

---

# 6. Sports domain model

## 6.1 Initial sport scope
Initial product experience:
- **baseball**
- **softball**

## 6.2 Long-term sport architecture
The underlying identity/domain architecture must be **sport-agnostic**, even though the first product is baseball/softball-first.

Shared entities should not be hard-coded to baseball-only assumptions.

## 6.3 Core domain entities
- Sport
- Organization
- School
- League
- Tournament
- Team
- Season
- Team context / team season context
- Player
- Coach
- Roster membership
- Game
- Event
- Statistic
- Record
- Achievement
- Milestone

---

# 7. Organization, league, team, and season model

## 7.1 MVP philosophy
Do not attempt to prebuild a national master graph.

Instead:
- teams can create themselves
- teams can create/join leagues/tournaments
- relationships grow through usage

## 7.2 Team identity
A team is a persistent sports object, but MVP should remain flexible enough to support practical real-world structures.

Examples:
- 2026 Bulldogs 14U
- 2026 Bulldogs 15U
- 2026 Bulldogs 16U

These may be distinct team identities under a broader organization/program structure.

## 7.3 Practical architecture guidance
Bulldog should preserve distinction between:
- organization
- team
- season/team context

But MVP should not overcomplicate this if leagues/teams operate informally.

## 7.4 League model
Leagues should eventually support:
- teams
- schedules
- standings
- leaders
- records
- newsletters
- historical seasons

League depth beyond basic onboarding may be deferred.

---

# 8. Team membership and player continuity

## 8.1 Relationship model, not simple roster model
A player should not merely have:
Player → Team

Instead the platform must support:
Player  
→ Team membership  
→ Sport  
→ Organization  
→ Season/context  
→ Position(s)  
→ Jersey number  
→ Start/end dates  
→ Games  
→ Statistics

## 8.2 Multiple teams simultaneously
A player may validly belong to multiple teams in the same year.

Examples:
- high school team
- AAU team
- travel team
- summer league team

This is a core requirement.

## 8.3 Former players remain historical
When a player leaves a team, the historical relationship remains.

Team pages should eventually preserve:
- former players
- former coaches
- past rosters
- season history

---

# 9. Bulldog Statbook definition

## 9.1 MVP promise
The MVP is the **free, excellent baseball/softball Statbook**.

The free version must provide anything a traditional paper Statbook provides.

## 9.2 Product role
The Statbook is the engine for:
- game scoring
- stat derivation
- season aggregation
- career aggregation
- team history
- milestones/records/achievements

## 9.3 Core scoring philosophy
The scorer records what happened.

Bulldog:
- tracks game state
- interprets rules
- derives statistics
- recalculates downstream effects
- reconstructs scorebook and play-by-play outputs

---

# 10. Event engine architecture

## 10.1 Locked event groups
### Group 1
Plate Appearance Events

### Group 2
Baserunning Events

### Group 3
Defensive Events

### Group 4
Pitching Events

### Group 5
Game State, Lineup, and Administration

### Group 6
Derived Statistics and Scorebook Interpretation

### Group 7
Milestones, Records, Achievements, and Gamification

### Group 8
Privacy, Permissions, Visibility, and Ownership

## 10.2 Event-driven architecture
Game state  
→ events  
→ rules interpretation  
→ derived stats  
→ review/finalization  
→ historical aggregation  
→ milestones/records/achievements

This is the foundational architecture of Bulldog Statbook.

---

# 11. Game state and lifecycle model

## 11.1 Game state
Bulldog must track:
- inning
- half inning
- outs
- count
- score
- runners
- batter
- pitcher
- defensive alignment
- lineup state
- substitutions
- ruleset context

## 11.2 Game lifecycle
- Draft
- Review
- Finalized
- Corrected (if necessary)

## 11.3 Core principle
Only finalized games should feed canonical historical records.

Draft/review may show provisional outputs.

---

# 12. Derived statistics model

## 12.1 Core principle
Raw events are authoritative.  
Derived statistics are recalculated from:
- event chain
- game state
- rule set

## 12.2 Derived outputs include
### Batting
- PA
- AB
- H
- 1B / 2B / 3B / HR
- R
- RBI
- BB
- SO
- HBP
- SB / CS
- AVG
- OBP
- SLG
- OPS

### Pitching
- IP
- H allowed
- R
- ER
- BB
- SO
- HBP
- WP
- BK
- ERA
- WHIP

### Fielding
- PO
- A
- E
- DP involvement
- fielding %

### Team outputs
- line score
- hits
- errors
- LOB
- box score
- game summary

## 12.3 Correction principle
Corrections to the event chain must propagate through:
- game stats
- player stats
- team stats
- season totals
- career totals
- milestones
- records
- achievements

---

# 13. Reconstruction model

## 13.1 Required outputs
Finalized event data must support:
- traditional scorebook reconstruction
- box score reconstruction
- play-by-play reconstruction
- future visual replay opportunities

## 13.2 Scorebook and game reconstruction
Bulldog should be able to recreate a game from structured event history, not just display final stat lines.

---

# 14. Historical data imports and fidelity model

## 14.1 Historical uploads are required
Coaches, parents, and appropriate users must be able to upload/import prior seasons/history.

This preserves longitudinal data that predates Bulldog.

## 14.2 Source transparency
Bulldog must label source type, such as:
- Bulldog-derived
- uploaded/imported
- manually entered historical
- verified team/league import

## 14.3 Fidelity awareness
Different historical data may have different fidelity levels.

Examples:
- season totals only
- box score level
- game log level
- play-by-play complete
- event-chain complete

## 14.4 Limitation language
Bulldog should communicate that it values longitudinal history but cannot independently vouch for the accuracy of imported/non-official entries.

## 14.5 Historical data effects
Imported data may contribute to:
- career totals
- milestones
- records
- timelines
- eligible achievements

But only where the fidelity of the source supports the claim.

---

# 15. Milestones, records, achievements, and gamification

## 15.1 Core principle
These systems must derive from the same historical/statistical truth system as the Statbook.

They are not disconnected add-ons.

## 15.2 Milestones
Bulldog should detect:
- firsts
- thresholds
- official milestone moments
- provisional milestone signals during live play where appropriate

## 15.3 Records
MVP record scopes:
- personal
- team
- season
- career

Broader scopes later:
- league
- school
- tournament
- regional
- state
- national

## 15.4 Achievements
MVP achievements should be:
- system-defined
- automatic
- rule-based

Custom coach-defined goals are post-MVP.

## 15.5 XP / progression
XP is tied to Bulldog usage and activity in Bulldog.

Imported historical stats **do not** unlock XP.

Early XP unlocks should be cosmetic first, not core stat truth.

## 15.6 Celebration surfaces
MVP may include:
- postgame milestone summary
- player profile milestone section
- team highlights
- timeline entries
- simple shareable cards

---

# 16. Career, team history, and longitudinal views

## 16.1 Player career
A player profile is effectively a **career page**, not just a profile page.

It should eventually support:
- current info
- season history
- team history
- game history
- career totals
- highlights
- awards
- milestones
- records
- achievements
- media
- shareable cards
- career timeline

## 16.2 Coach career
A coach should eventually have:
- teams coached
- seasons coached
- record/history
- milestones
- achievements
- development-related history later

## 16.3 Team history
A team should eventually support:
- current roster
- past rosters
- former players
- former coaches
- game history
- season history
- championships
- records
- team milestone history

---

# 17. Privacy architecture

## 17.1 Privacy posture
Player-related data defaults to restricted visibility, especially for minors.

Players must not be search-engine discoverable by default.

## 17.2 Visibility concepts
Bulldog must distinguish:
- search-engine discoverability
- internal Bulldog lookup
- team-context visibility
- explicit shared-link visibility

These are separate concepts.

## 17.3 MVP visibility model
Use a simple model centered on:
- private
- team-context visible
- controlled share visibility
- authorized official/admin visibility

## 17.4 Minor protections
Minors receive stricter defaults and controls than adults.

---

# 18. Permissions model

## 18.1 Contextual authority
Permissions are identity-based, context-aware, and lifecycle-aware.

## 18.2 Core MVP role concepts
- user
- player
- parent/guardian
- coach
- team admin
- scorekeeper
- fan/viewer
- league admin (limited or deferred)

## 18.3 Coach permissions
Coaches may have strong authority within team context, but do not own a player’s global Bulldog identity.

## 18.4 Team admin vs coach vs scorekeeper
These are distinct authority types even if MVP UI simplifies them.

## 18.5 Player editing
Players may manage their own identity/presentation where allowed, but should not casually edit official game statistics.

---

# 19. Ownership and official authority

## 19.1 Game ownership
MVP should treat the **home team** as the default authoritative owner of the official game record.

## 19.2 Delegated editors
Authorized scorekeepers/editors may contribute to the same game.

## 19.3 Lifecycle-aware editing
- Draft: editable by assigned editors
- Review: editable by authorized reviewers
- Finalized: locked except through correction workflow

## 19.4 Auditability
Corrections must preserve audit history.

---

# 20. Historical upload permissions

Historical uploads must be:
- context-permissioned
- attributable to a user
- source-labeled
- auditable

Examples:
- adult player uploads own history
- parent uploads child-related history where authorized
- coach/team admin uploads team-context history

Uploads must not exceed the uploader’s legitimate authority.

---

# 21. Social and communication policy

## 21.1 MVP exclusions
MVP explicitly excludes:
- direct messaging
- open chat
- friend requests
- unrestricted adult-to-child communication

## 21.2 Future social direction
Possible future social features:
- follows
- reactions
- announcements
- team/league communications
- controlled sharing

But these are not early priority.

## 21.3 Strategic priority
The Statbook must win first.

Coaches must be excited to use it.  
Parents may also drive adoption.  
Player-facing social delight follows the data foundation.

---

# 22. Shareable cards and public visibility

## 22.1 Card philosophy
Cards are a core fun/identity surface:
- player cards
- milestone cards
- game cards
- achievement cards
- season review cards later

## 22.2 Visibility rule
Cards may be shareable in controlled ways without being:
- publicly indexed
- openly searchable
- broadly discoverable

This is especially important for minors.

---

# 23. MVP feature set

## 23.1 MVP goal
Deliver a free, excellent baseball/softball Statbook that:
- replaces paper scoring
- is easy to use
- automatically derives stats
- preserves player/team/season/career history
- supports milestones and simple celebrations
- respects privacy and youth safety

## 23.2 MVP includes
### Identity and structure
- permanent Bulldog identity
- player claim/link workflow
- coach/team/player relationships
- parent relationship model
- team creation and roster management
- league/team/tournament basic association

### Statbook
- game setup
- lineup/defensive alignment
- substitutions/position changes
- event scoring engine
- game-state management
- postgame review/finalization
- derived stats
- box score
- scorebook/play-by-play reconstruction

### History
- player season/career aggregation
- team history foundations
- historical uploads/imports with source labels
- milestones
- system-defined achievements
- personal/team/season/career records
- timeline foundations
- simple shareable cards

### Privacy/permissions
- restricted defaults
- minors protected
- team-context visibility
- controlled sharing
- no search-engine player discoverability
- no DMs/open chat/friend requests

---

# 24. Post-MVP feature set

## 24.1 Enhanced Statbook
- deeper streak systems
- advanced splits
- richer trend analysis
- comparative analysis
- broader league views

## 24.2 Gamification and progression
- richer XP economy
- unlock systems beyond cosmetics if ever appropriate
- coach-defined goals
- development achievements
- training-linked progression
- merch/discount redemption

## 24.3 Social
- follows
- announcements
- reactions
- team/league news surfaces

## 24.4 League depth
- standings
- leaders
- newsletters
- historical seasons
- broader record scopes

## 24.5 Bulldog Intelligence
- advanced analytics
- coaching recommendations
- lineup recommendations
- player development insights
- trend explanation
- recruiting/scouting tools

## 24.6 Bulldog Training integration
Unified identity, shared progression/history where appropriate.

## 24.7 Marketplace
Coaching innovation and future product/service layers.

---

# 25. Deliberate non-goals / not building yet

To avoid scope drift, Bulldog MVP is **not** attempting to fully build:

- a national master sports graph
- open social networking
- direct messaging
- public player discoverability
- fully custom coach-defined goal systems
- broad league/state/national comparisons
- full AI coaching stack
- all advanced sabermetrics/analytics
- marketplace functionality
- fully integrated training product
- every future sport

These are deferred by design.

---

# 26. Monetization strategy

## 26.1 Core strategy
- **Acquire** with free Statbook
- **Retain** with persistent history and identity
- **Monetize** with deeper intelligence, enhanced views, and optional premium layers

## 26.2 Pricing philosophy
Affordable monthly subscription philosophy, potentially:
- low-cost individual
- team-wide
- league-wide

## 26.3 Free tier rule
The free version must include the complete traditional Statbook experience.

Premium should add depth, not cripple the core.

---

# 27. Technical architecture principles

## 27.1 Primary technical principles
1. Event-driven core
2. Raw facts as source of truth
3. Derived statistics recalculable
4. Lifecycle-aware records
5. Auditability for corrections
6. Fidelity-aware historical imports
7. Privacy-by-default
8. Contextual permissions
9. Sport-agnostic domain core
10. Baseball/softball-specific scoring module first

## 27.2 Data architecture guidance
Bulldog should separate:
- identity data
- relationship/context data
- event data
- derived/statistical outputs
- presentation/experience outputs

## 27.3 Avoid
Avoid making authoritative truth out of:
- stored formulas only
- manually duplicated totals
- hard-coded baseball-only assumptions in ecosystem identity/domain layers

---

# 28. Suggested high-level entity model

This is conceptual, not final schema.

## 28.1 Identity
- User
- UserProfile
- RoleIdentity
- PlayerIdentity
- CoachIdentity
- GuardianRelationship
- FanRelationship

## 28.2 Sports structure
- Sport
- Organization
- League
- Tournament
- Team
- Season
- TeamContext / TeamSeasonContext
- Membership

## 28.3 Game
- Game
- GameParticipant
- GameStateSnapshot
- Lineup
- DefensiveAlignment
- Substitution
- Event
- EventOutcome
- ReviewItem
- FinalizationRecord
- CorrectionAudit

## 28.4 Statistics/history
- StatAggregate
- SeasonAggregate
- CareerAggregate
- Record
- Milestone
- Achievement
- TimelineEntry
- Streak

## 28.5 Imports
- HistoricalImport
- ImportedStatLine
- ImportSource
- FidelityLevel
- ImportAudit

## 28.6 Presentation
- PlayerCard
- TeamCard
- GameCard
- ShareLink
- VisibilityRule

---

# 29. API / implementation guidance for Codex

## 29.1 Development context
This blueprint is for **Codex-oriented implementation**, not Lovable or other earlier vendor artifacts.

## 29.2 Recommended implementation sequence
1. identity/auth foundations
2. team/player/roster relationships
3. game setup and state engine
4. event engine
5. derived statistics
6. review/finalization workflow
7. season/career aggregation
8. milestones/records/achievements foundations
9. historical import pipeline
10. cards/timeline/privacy surfaces

## 29.3 Build discipline
Implementation should preserve:
- conceptual architecture first
- schema aligned to behavior
- permission checks early
- auditability from the start
- separation of core truth from presentation surfaces

---

# 30. Open questions / deferred decisions

These do not block v1, but should remain visible.

1. Final concrete schema for team vs team-season modeling
2. Exact age thresholds and parent/guardian control rules
3. Exact internal search behavior for player lookup
4. Which cards/celebration surfaces are included in the very first shipped MVP
5. Exact historical import formats and validation UX
6. Degree of league functionality in first release
7. Exact premium packaging after MVP
8. Exact XP mechanics and cosmetic unlock system
9. Advanced stat taxonomy for Enhanced vs Intelligence
10. Training integration timeline

---

# 31. Final statement of intent

## Bulldog Product + Technical Blueprint v1
This document is now the **authoritative source of truth** for Bulldog’s product and architecture direction at this stage.

It establishes that:

- Bulldog is an ecosystem, not just a Statbook
- Bulldog Statbook is the flagship MVP product
- identity is permanent and longitudinal
- scoring is event-driven
- stats are derived
- history is preserved across teams and seasons
- milestones/records/achievements are first-class outputs
- historical imports matter and must be source-labeled
- privacy and youth safety are foundational
- the free Statbook must fully replace paper
- coaches are the primary early adoption engine
- social is not the MVP priority

---

