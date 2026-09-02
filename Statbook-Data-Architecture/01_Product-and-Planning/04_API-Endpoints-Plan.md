# Bulldog API / Endpoints Plan v1  
## MVP-Oriented, Codex-Aligned

This plan translates the blueprint and schema draft into a practical API surface.

I’ll keep this at the level of:
- domains
- endpoint groups
- core commands/queries
- permission notes
- workflow intent

Not every endpoint must exist on day one, but this gives you the target shape.

---

# 1. API design principles

## 1.1 Core principles
1. APIs should reflect domain workflows, not just CRUD tables
2. Permission checks must be enforced server-side
3. Official vs provisional state must be respected
4. Events are the source of truth
5. Aggregates and cards are query surfaces, not raw-authority writes
6. Sharing must not imply public discoverability
7. Historical imports must preserve provenance

## 1.2 Recommended shape
A REST-style API is perfectly workable for MVP.

Suggested top-level route structure:

```text
/api/v1
  /auth
  /users
  /profiles
  /players
  /coaches
  /guardians
  /sports
  /organizations
  /leagues
  /tournaments
  /teams
  /games
  /stats
  /history
  /imports
  /recognition
  /cards
  /sharing
  /admin
```

---

# 2. Auth and account APIs

## 2.1 Auth
### `POST /api/v1/auth/register`
Create account.

**Body**
- email
- password
- date_of_birth or age metadata
- display_name

**Returns**
- user
- session/token

---

### `POST /api/v1/auth/login`
Login.

---

### `POST /api/v1/auth/logout`
Logout.

---

### `POST /api/v1/auth/password/forgot`
Password reset request.

---

### `POST /api/v1/auth/password/reset`
Complete password reset.

---

## 2.2 Current session
### `GET /api/v1/auth/me`
Return current user, roles, high-level permissions, linked identities.

---

# 3. User/profile APIs

## 3.1 Current user profile
### `GET /api/v1/profile`
Fetch current user profile.

### `PATCH /api/v1/profile`
Update:
- display name
- avatar
- bio
- location
- account preferences

---

## 3.2 Privacy settings
### `GET /api/v1/profile/privacy`
Get user-level privacy settings.

### `PATCH /api/v1/profile/privacy`
Update user-level privacy defaults.

**Notes**
- some values constrained by age/minor rules
- minors may require guardian-controlled limitations

---

# 4. Player identity APIs

## 4.1 Create player identity
### `POST /api/v1/players`
Create player identity.

**Use cases**
- adult creates own player profile
- coach creates unclaimed player identity
- parent creates child-linked player profile if allowed

**Body**
- display_name or name fields
- birth_year optional
- primary_sport optional
- user_id optional only if self-created/linked in allowed workflow

**Returns**
- player identity
- player code

**Permission**
- authenticated user only
- context-specific if created from team flow

---

## 4.2 Get player identity
### `GET /api/v1/players/{playerId}`
Fetch player identity summary, subject to visibility policy.

---

## 4.3 Claim player identity
### `POST /api/v1/players/{playerId}/claim`
Claim a pre-created player identity.

**Body**
- verification inputs as required

**Returns**
- updated claim status

**Notes**
- should support pending/manual review later
- must not silently reassign already-claimed identities

---

## 4.4 Player profile/career view
### `GET /api/v1/players/{playerId}/career`
Returns:
- current profile summary
- season summaries
- career totals
- team history
- milestones
- achievements
- timeline snippets

**Permission**
- privacy-sensitive
- minors restricted
- not public-search API

---

## 4.5 Player game log
### `GET /api/v1/players/{playerId}/games`
Query params:
- season
- team_id
- sport
- opponent
- limit/offset

---

## 4.6 Player cards
### `GET /api/v1/players/{playerId}/card`
Fetch rendered card data, subject to visibility rules.

---

# 5. Coach identity APIs

## 5.1 Create coach identity
### `POST /api/v1/coaches`
Create or initialize coach identity for current user.

---

## 5.2 Get coach profile/history
### `GET /api/v1/coaches/{coachId}`
Returns:
- identity summary
- teams coached
- season summaries
- milestones/records if applicable later

---

# 6. Guardian APIs

## 6.1 Create guardian relationship request
### `POST /api/v1/guardians/relationships`
Create guardian relationship.

**Body**
- player_id
- relationship_type

---

## 6.2 List guardian relationships
### `GET /api/v1/guardians/relationships`
Get relationships for current user.

---

## 6.3 Verify/update guardian relationship
### `PATCH /api/v1/guardians/relationships/{relationshipId}`
Update verification status or relationship metadata.

**Permission**
- highly restricted
- may be admin/system-assisted in some cases

---

# 7. Sports / org / league / tournament APIs

## 7.1 Sports
### `GET /api/v1/sports`
List sports.

---

## 7.2 Organizations
### `POST /api/v1/organizations`
Create organization.

### `GET /api/v1/organizations/{organizationId}`
Fetch organization summary.

---

## 7.3 Leagues
### `POST /api/v1/leagues`
Create league.

### `GET /api/v1/leagues/{leagueId}`
Get league summary.

### `POST /api/v1/leagues/{leagueId}/teams`
Add team to league.

### `GET /api/v1/leagues/{leagueId}/teams`
List teams in league.

---

## 7.4 Tournaments
### `POST /api/v1/tournaments`
Create tournament.

### `GET /api/v1/tournaments/{tournamentId}`
Get tournament summary.

### `POST /api/v1/tournaments/{tournamentId}/teams`
Add team to tournament.

---

# 8. Team APIs

## 8.1 Create team
### `POST /api/v1/teams`
Create a team.

**Body**
- name
- sport_id
- age_group optional
- season_label optional
- organization_id optional
- location optional

---

## 8.2 List teams for current user
### `GET /api/v1/teams`
Query params:
- mine=true
- sport_id
- role_type

---

## 8.3 Get team
### `GET /api/v1/teams/{teamId}`
Returns:
- team summary
- visibility-aware metadata
- current role of requester
- basic history counts

---

## 8.4 Update team
### `PATCH /api/v1/teams/{teamId}`
Update team metadata/settings.

**Permission**
- team admin or equivalent

---

## 8.5 Team roles
### `GET /api/v1/teams/{teamId}/roles`
List assigned team roles.

### `POST /api/v1/teams/{teamId}/roles`
Assign team role.

**Body**
- user_id
- role_type

### `DELETE /api/v1/teams/{teamId}/roles/{roleAssignmentId}`
Remove team role.

---

## 8.6 Team roster
### `GET /api/v1/teams/{teamId}/roster`
Fetch current roster.

### `POST /api/v1/teams/{teamId}/roster`
Add roster entry.

**Use cases**
- add existing player identity
- create new unclaimed player identity and add to team

**Body**
- player_id optional
- create_player_payload optional
- jersey_number optional
- positions optional

### `PATCH /api/v1/teams/{teamId}/roster/{membershipId}`
Update membership metadata.

### `DELETE /api/v1/teams/{teamId}/roster/{membershipId}`
Deactivate/remove roster membership.

---

## 8.7 Team history
### `GET /api/v1/teams/{teamId}/history`
Returns:
- past rosters
- former players
- season summaries
- championships
- records/milestones if available

---

# 9. Game APIs

## 9.1 Create game
### `POST /api/v1/games`
Create game.

**Body**
- sport_id
- home_team_id
- away_team_id
- league_id optional
- tournament_id optional
- scheduled_at optional
- location optional
- ruleset_id optional

**Rules**
- ownership defaults to home team
- creator must have authority in home team context

---

## 9.2 Get game summary
### `GET /api/v1/games/{gameId}`
Returns:
- game metadata
- teams
- status
- scoreboard summary
- current visibility state

---

## 9.3 Assign game editor
### `POST /api/v1/games/{gameId}/editors`
Assign scorekeeper/reviewer/editor.

### `GET /api/v1/games/{gameId}/editors`
List current editors.

---

## 9.4 Load game setup
### `GET /api/v1/games/{gameId}/setup`
Returns:
- game rosters
- lineup
- defensive alignment
- ruleset
- state summary

---

## 9.5 Populate game roster
### `POST /api/v1/games/{gameId}/roster/load-from-team`
Load roster from team memberships.

### `PATCH /api/v1/games/{gameId}/roster/{entryId}`
Update roster eligibility.

---

## 9.6 Lineup
### `GET /api/v1/games/{gameId}/lineup?team_id=...`
### `PUT /api/v1/games/{gameId}/lineup?team_id=...`
Set/replace lineup.

**Body**
- ordered player entries
- lineup mode fields if needed

---

## 9.7 Defensive alignment
### `GET /api/v1/games/{gameId}/defense?team_id=...`
### `PUT /api/v1/games/{gameId}/defense?team_id=...`
Set defensive alignment.

---

## 9.8 Game state
### `GET /api/v1/games/{gameId}/state`
Returns current:
- inning
- half
- outs
- score
- bases
- batter
- pitcher
- lineup pointers

---

# 10. Event scoring APIs

This is the operational heart of the Statbook.

## 10.1 Append event
### `POST /api/v1/games/{gameId}/events`
Append a scored event.

**Body**
- event_family
- event_type
- event_subtype optional
- payload
- current client state metadata optional

**Examples**
- single
- strikeout
- walk
- stolen_base
- substitution
- position_change
- pitching_change
- inning_transition

**Server behavior**
- validate permission
- validate state consistency
- append event
- update projections/snapshots
- generate review items if needed

---

## 10.2 List events
### `GET /api/v1/games/{gameId}/events`
Returns ordered event stream.

Query params:
- since_sequence
- inning
- include_voided

---

## 10.3 Edit/correct event
### `POST /api/v1/games/{gameId}/events/{eventId}/correct`
Apply correction workflow.

**Body**
- correction_type
- replacement payload or edit instructions
- notes

**Behavior**
- preserve audit
- mark prior event superseded or voided
- rebuild downstream projections

---

## 10.4 Undo last event
### `POST /api/v1/games/{gameId}/events/undo`
Undo the latest valid event if allowed by workflow.

---

## 10.5 Specialized convenience endpoints (optional)
If desired for simpler client implementation:

### `POST /api/v1/games/{gameId}/substitutions`
### `POST /api/v1/games/{gameId}/position-changes`
### `POST /api/v1/games/{gameId}/pitching-changes`

Internally these still create events.

---

# 11. Review / finalization APIs

## 11.1 Review items
### `GET /api/v1/games/{gameId}/review-items`
List integrity/review issues.

### `PATCH /api/v1/games/{gameId}/review-items/{reviewItemId}`
Resolve review item.

---

## 11.2 Move to review
### `POST /api/v1/games/{gameId}/review`
Transition game toward review state.

---

## 11.3 Finalize game
### `POST /api/v1/games/{gameId}/finalize`
Finalize official game record.

**Behavior**
- verify authority
- verify review gates
- set game final
- promote official stats/aggregates
- trigger milestone/record reevaluation

---

## 11.4 Suspend/resume game
### `POST /api/v1/games/{gameId}/suspend`
### `POST /api/v1/games/{gameId}/resume`

---

# 12. Scorebook / reconstruction APIs

## 12.1 Box score
### `GET /api/v1/games/{gameId}/boxscore`
Returns:
- team totals
- player batting
- player pitching
- player fielding
- line score

---

## 12.2 Play-by-play
### `GET /api/v1/games/{gameId}/play-by-play`
Returns reconstructed play sequence.

---

## 12.3 Scorebook view
### `GET /api/v1/games/{gameId}/scorebook`
Returns data necessary to render traditional scorebook-style output.

---

## 12.4 Replay/reconstruction data
### `GET /api/v1/games/{gameId}/replay`
Optional early structured replay payload built from snapshots + events.

---

# 13. Stats APIs

## 13.1 Player stats
### `GET /api/v1/stats/players/{playerId}`
Query params:
- sport_id
- season
- team_id
- scope (`game`, `season`, `career`)

---

## 13.2 Team stats
### `GET /api/v1/stats/teams/{teamId}`
Query params:
- season
- scope (`game`, `season`, `history`)

---

## 13.3 Career aggregate
### `GET /api/v1/stats/players/{playerId}/career`
Can overlap with career endpoint if you want simpler API organization.

---

## 13.4 Season aggregate
### `GET /api/v1/stats/players/{playerId}/seasons/{seasonLabel}`
### `GET /api/v1/stats/teams/{teamId}/seasons/{seasonLabel}`

---

# 14. History APIs

## 14.1 Player history
### `GET /api/v1/history/players/{playerId}`
Returns:
- teams
- seasons
- aggregates
- milestones
- achievements
- recent timeline

---

## 14.2 Team history
### `GET /api/v1/history/teams/{teamId}`
Returns:
- roster history
- former players
- seasons
- records
- team milestones

---

## 14.3 Timeline
### `GET /api/v1/history/players/{playerId}/timeline`
### `GET /api/v1/history/teams/{teamId}/timeline`

---

# 15. Historical import APIs

## 15.1 Create import
### `POST /api/v1/imports`
Create historical import record and upload file metadata.

**Body**
- import_type
- context_type
- context_id
- source_label
- fidelity_level

---

## 15.2 Upload file / attach data
### `POST /api/v1/imports/{importId}/file`
Upload CSV/XLSX/etc.

---

## 15.3 Preview import mapping
### `GET /api/v1/imports/{importId}/preview`
Returns parsed sample and mapping suggestions.

---

## 15.4 Confirm import mapping
### `POST /api/v1/imports/{importId}/confirm`
Confirm mapping and execute import.

---

## 15.5 Import audit
### `GET /api/v1/imports/{importId}`
Fetch import details, source, status, audit metadata.

### `GET /api/v1/imports/{importId}/audit`
Fetch import audit trail.

---

## 15.6 List imports for context
### `GET /api/v1/imports?context_type=team&context_id=...`

---

# 16. Recognition APIs

## 16.1 Milestones
### `GET /api/v1/recognition/players/{playerId}/milestones`
### `GET /api/v1/recognition/teams/{teamId}/milestones`

---

## 16.2 Records
### `GET /api/v1/recognition/players/{playerId}/records`
### `GET /api/v1/recognition/teams/{teamId}/records`

---

## 16.3 Achievements
### `GET /api/v1/recognition/players/{playerId}/achievements`
### `GET /api/v1/recognition/teams/{teamId}/achievements`

---

## 16.4 Streaks
### `GET /api/v1/recognition/players/{playerId}/streaks`
### `GET /api/v1/recognition/teams/{teamId}/streaks`

---

# 17. Card and sharing APIs

## 17.1 Player card
### `GET /api/v1/cards/players/{playerId}`
Returns card data subject to visibility rules.

---

## 17.2 Milestone card
### `GET /api/v1/cards/milestones/{milestoneId}`

---

## 17.3 Game card
### `GET /api/v1/cards/games/{gameId}`
Optional MVP edge feature for shareable game summary.

---

## 17.4 Create share link
### `POST /api/v1/sharing/links`
Create controlled share link for object.

**Body**
- object_type
- object_id
- allow_authenticated_only
- expires_at optional

---

## 17.5 Revoke share link
### `DELETE /api/v1/sharing/links/{shareLinkId}`

---

## 17.6 Resolve share link
### `GET /api/v1/sharing/links/{token}`
Return underlying object if permitted.

**Notes**
- must not create indexable public discovery
- token lookup route should be guarded appropriately

---

# 18. Visibility / permission management APIs

## 18.1 Object visibility
### `GET /api/v1/visibility/{objectType}/{objectId}`
Get visibility rule.

### `PATCH /api/v1/visibility/{objectType}/{objectId}`
Update visibility.

**Permission**
- only authorized controller of object
- minor rules can constrain options

---

# 19. XP / progression APIs

Only if minimal MVP plumbing is included.

## 19.1 Current progression
### `GET /api/v1/progression/me`
Returns:
- total_xp
- level
- cosmetic unlocks

## 19.2 XP ledger
### `GET /api/v1/progression/me/ledger`
Returns usage-based XP events.

**Rule**
- imported historical stats should not appear here as XP sources

---

# 20. Admin / support APIs

These are very useful even in MVP if access is tightly restricted.

## 20.1 Audit logs
### `GET /api/v1/admin/audit`
Filter by:
- actor
- object_type
- object_id
- date range

---

## 20.2 Game correction audit
### `GET /api/v1/admin/games/{gameId}/corrections`

---

## 20.3 Duplicate identity review (later)
### `POST /api/v1/admin/players/{playerId}/merge`
Likely later, but good to anticipate.

---

# 21. Permission matrix guidance by endpoint class

## 21.1 Open/authenticated
- register/login
- fetch current profile
- list sports

## 21.2 Self-scoped
- update own profile
- view own progression
- request claim of own player identity

## 21.3 Guardian-scoped
- view/manage child-linked visibility where permitted
- upload child historical data where allowed

## 21.4 Team-context scoped
- create roster
- assign team roles
- start game
- manage lineup
- score game
- upload team historical stats

## 21.5 Official authority scoped
- finalize game
- correct finalized game
- manage ownership-sensitive actions

## 21.6 Restricted / admin
- audit tools
- sensitive dispute operations
- identity merges

---

# 22. Endpoint priorities for actual build order

Given your desired MVP, I would implement endpoints in this order.

## Wave A — Identity + team setup
- auth register/login/me
- profile get/update
- create/list/get teams
- team roles assign/list
- team roster add/list/update
- player create/get/claim
- guardian relationship create/list

## Wave B — Game setup and scoring
- create game
- get game/setup/state
- load roster
- set lineup
- set defense
- append event
- list events
- review items
- finalize game

## Wave C — Derived outputs
- box score
- play-by-play
- scorebook
- player/team stats
- player career
- team history

## Wave D — Imports + recognition
- create/upload/confirm imports
- milestones
- records
- achievements
- timeline

## Wave E — Cards + sharing
- player card
- milestone card
- share link create/revoke/resolve
- visibility management

---

# 23. Recommended response shapes

## 23.1 Standard API envelope
Keep it simple and consistent, e.g.:

```json
{
  "data": { ... },
  "meta": { ... },
  "error": null
}
```

For errors:

```json
{
  "data": null,
  "meta": {},
  "error": {
    "code": "FORBIDDEN",
    "message": "You do not have permission to finalize this game."
  }
}
```

---

# 24. API behavior rules that should be explicit

## 24.1 Finalized games
Endpoints that modify gameplay truth must reject ordinary edits after finalization unless using correction workflow.

## 24.2 Minors
Visibility endpoints and share endpoints must check minor rules every time, not just at creation.

## 24.3 Imported stats
Import endpoints must require:
- source label
- fidelity level
- uploader attribution

## 24.4 XP
Progression endpoints must exclude imported stat-driven unlocks.

## 24.5 Sharing
Shared object access must respect:
- link status
- expiration
- authenticated-only setting
- object visibility policy

---

# 25. Suggested API non-goals for MVP

Do not build yet:
- DM/chat APIs
- friend request APIs
- generic social feed APIs
- league-wide advanced analytics APIs
- marketplace APIs
- training integration APIs beyond identity compatibility

---

# 26. Recommended next practical move

You asked for:
> **3 > 1 > 2**

We have now completed:
1. **Phased Codex implementation backlog**
2. **Concrete database schema draft**
3. **API / endpoints plan**

So the best next step is no longer another architecture artifact.

