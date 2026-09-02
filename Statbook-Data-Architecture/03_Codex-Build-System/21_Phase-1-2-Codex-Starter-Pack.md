# Bulldog Phase 1 + Phase 2 Codex Starter Pack  
## First Build Pack for MVP Execution

This is the practical “start building now” package.

It focuses only on:

- **Phase 1** — Identity, auth, and relationships
- **Phase 2** — Teams, rosters, and permissions

Why this first:
- everything else depends on identity and context
- if User / Player / Coach / Parent / Team relationships are wrong, game scoring becomes painful later
- this is the smallest slice that preserves the architecture correctly

---

# 1. Build goal for this starter pack

At the end of Phase 2, Bulldog should be able to do this:

1. A person creates a Bulldog account
2. That person can have a profile
3. That person can create or link a Player identity
4. That person can create a Team
5. That person can assign Team roles
6. That person can create roster slots
7. A coach can add:
   - an existing Bulldog Player
   - or a new unclaimed Player identity
8. A parent/guardian relationship can exist in the system
9. Permission checks are already contextual

If this works cleanly, you have the proper foundation for:
- games
- scoring
- history
- imports
- milestones

---

# 2. Scope boundary

## In scope
- auth
- users
- profile
- player identity
- coach identity
- guardian relationship foundation
- team creation
- team roles
- roster management
- player search/link by Player ID or internal lookup
- permission policy framework

## Out of scope for this starter pack
- games
- live scoring
- stats
- imports
- cards
- milestones
- league depth
- social

---

# 3. Recommended implementation order

## Order
1. Core auth/user tables and endpoints
2. User profile
3. Player identity model + endpoints
4. Coach identity model
5. Guardian relationship model
6. Team model + endpoints
7. Team roles
8. Team roster / memberships
9. Existing-player attach flow
10. Create-unclaimed-player-from-roster flow
11. Permission policy hardening
12. Basic UI screens

---

# 4. First migrations to create

These are the first DB migrations I would create.

## Migration 001 — `users`
Create:
- `users`

## Migration 002 — `user_profiles`
Create:
- `user_profiles`

## Migration 003 — `sports`
Seed:
- baseball
- softball

## Migration 004 — `player_identities`
Create:
- `player_identities`

## Migration 005 — `coach_identities`
Create:
- `coach_identities`

## Migration 006 — `guardian_relationships`
Create:
- `guardian_relationships`

## Migration 007 — `organizations`
Create:
- `organizations`

## Migration 008 — `teams`
Create:
- `teams`

## Migration 009 — `team_memberships`
Create:
- `team_memberships`

## Migration 010 — `team_membership_positions`
Create:
- `team_membership_positions`

## Migration 011 — `team_role_assignments`
Create:
- `team_role_assignments`

---

# 5. First models to implement

## 5.1 User model
Responsibilities:
- core identity
- auth fields
- date of birth / minor flag
- account status

### Must support
- one permanent Bulldog account
- later linking to PlayerIdentity / CoachIdentity

---

## 5.2 UserProfile model
Responsibilities:
- display profile data
- avatar
- visibility default

---

## 5.3 PlayerIdentity model
Responsibilities:
- distinct from user
- may exist before claim
- human-facing Player ID
- nullable `user_id`

### Must support statuses
- unclaimed
- pending
- claimed
- disputed (later okay, but reserve for it)

---

## 5.4 CoachIdentity model
Responsibilities:
- persistent coach role identity
- linked to one user

---

## 5.5 GuardianRelationship model
Responsibilities:
- guardian-to-player relationship
- verification status
- groundwork for minor privacy

---

## 5.6 Team model
Responsibilities:
- practical team object for MVP
- linked to sport
- optional org
- season label / age group support

---

## 5.7 TeamMembership model
Responsibilities:
- player or coach relationship to a team
- preserve history with start/end dates
- support jersey # and active status

---

## 5.8 TeamRoleAssignment model
Responsibilities:
- team admin
- coach
- scorekeeper
- viewer/assistant later

---

# 6. First service layer to implement

I strongly recommend you create services/use-cases early instead of putting all logic in controllers/routes.

## 6.1 `AuthService`
Methods:
- registerUser
- loginUser
- logoutUser
- getCurrentSessionUser

## 6.2 `UserService`
Methods:
- getProfile
- updateProfile
- getUserContextSummary

## 6.3 `PlayerIdentityService`
Methods:
- createPlayerIdentity
- claimPlayerIdentity
- getPlayerIdentity
- searchPlayerIdentity
- linkPlayerIdentityToUser

## 6.4 `CoachIdentityService`
Methods:
- createCoachIdentity
- getCoachIdentityByUser

## 6.5 `GuardianService`
Methods:
- createGuardianRelationship
- listGuardianRelationshipsForUser
- verifyGuardianRelationship

## 6.6 `TeamService`
Methods:
- createTeam
- getTeam
- updateTeam
- listTeamsForUser

## 6.7 `TeamRoleService`
Methods:
- assignRole
- removeRole
- listRoles
- userHasTeamRole

## 6.8 `RosterService`
Methods:
- addExistingPlayerToTeam
- createUnclaimedPlayerAndAddToTeam
- updateRosterMembership
- deactivateRosterMembership
- listRoster

## 6.9 `PermissionPolicyService`
Methods:
- canCreateTeam
- canManageTeam
- canAssignTeamRole
- canManageRoster
- canCreatePlayerForRoster
- canViewPlayerIdentity
- canManageGuardianRelationship

This service is important. Even if simple at first, it should exist immediately.

---

# 7. First endpoints to implement

These should be your first concrete endpoints.

---

## 7.1 Auth endpoints

### `POST /api/v1/auth/register`
Acceptance criteria:
- creates `users` row
- creates `user_profiles` row
- computes/stores `is_minor`
- returns authenticated session/token

### `POST /api/v1/auth/login`
Acceptance criteria:
- valid login works
- invalid login rejected
- session created

### `GET /api/v1/auth/me`
Acceptance criteria:
- returns current user
- linked player identity if present
- linked coach identity if present
- top-level team roles summary

---

## 7.2 Profile endpoints

### `GET /api/v1/profile`
### `PATCH /api/v1/profile`

Acceptance criteria:
- current user can fetch/update own profile
- display name/avatar/privacy defaults editable

---

## 7.3 Player identity endpoints

### `POST /api/v1/players`
Acceptance criteria:
- self-create player identity works
- coach-created player identity works in allowed contexts
- generates unique `player_code`

### `GET /api/v1/players/{playerId}`
Acceptance criteria:
- returns player identity summary
- permission-protected
- does not expose unauthorized private info

### `POST /api/v1/players/{playerId}/claim`
Acceptance criteria:
- unclaimed identity can be claimed
- already-claimed identity cannot be silently hijacked
- links `player_identities.user_id`

### `GET /api/v1/players/search`
Suggested query params:
- `player_code`
- `q`
- maybe `birth_year`

Acceptance criteria:
- team-authorized users can find existing players for roster attach
- search does not act like open public people search

---

## 7.4 Coach identity endpoints

### `POST /api/v1/coaches`
Acceptance criteria:
- current user gets coach identity
- duplicate coach identities for same user prevented

---

## 7.5 Guardian endpoints

### `POST /api/v1/guardians/relationships`
Acceptance criteria:
- current user can create guardian relationship request
- links guardian to player identity
- verification status begins correctly

### `GET /api/v1/guardians/relationships`
Acceptance criteria:
- current user sees only their own guardian relationships

---

## 7.6 Team endpoints

### `POST /api/v1/teams`
Acceptance criteria:
- create team with sport required
- creator becomes `team_admin`
- creator may also be `coach` if desired by default

### `GET /api/v1/teams`
Acceptance criteria:
- returns current user’s relevant teams
- can filter by role/sport later

### `GET /api/v1/teams/{teamId}`
Acceptance criteria:
- returns team summary
- permission-aware role context included

### `PATCH /api/v1/teams/{teamId}`
Acceptance criteria:
- only authorized team admin/manager can update
- metadata only at this phase

---

## 7.7 Team role endpoints

### `GET /api/v1/teams/{teamId}/roles`
### `POST /api/v1/teams/{teamId}/roles`
### `DELETE /api/v1/teams/{teamId}/roles/{roleAssignmentId}`

Acceptance criteria:
- authorized users can assign/remove roles
- duplicate role assignments prevented
- unauthorized users denied

---

## 7.8 Team roster endpoints

### `GET /api/v1/teams/{teamId}/roster`
Acceptance criteria:
- returns active roster
- includes player ID, jersey, positions, claim status where appropriate

### `POST /api/v1/teams/{teamId}/roster`
This should support two pathways:

#### Path A — Existing player
Body includes:
- `player_id`
- jersey #
- positions

#### Path B — New unclaimed player
Body includes:
- create_player payload
- jersey #
- positions

Acceptance criteria:
- authorized coach/admin can add roster entries
- new player identity can be created inline
- membership is created correctly
- no global player ownership transferred to coach

### `PATCH /api/v1/teams/{teamId}/roster/{membershipId}`
Acceptance criteria:
- update jersey #
- update positions
- activate/deactivate status if allowed

### `DELETE /api/v1/teams/{teamId}/roster/{membershipId}`
Acceptance criteria:
- historical membership not hard-deleted if possible
- “inactive/end-dated” preferred over destructive delete

---

# 8. First policy rules to hard-code

These should be implemented immediately and tested.

## 8.1 User-level rules
- a user can view/edit their own profile
- a user can create their own player identity
- a user cannot arbitrarily claim someone else’s already-claimed player identity

## 8.2 Guardian rules
- only the guardian user or authorized admin can view/manage their guardian relationship
- guardian relationship does not equal global ownership of player identity

## 8.3 Team rules
- only team admins can assign/remove team roles
- coaches/team admins can manage roster
- viewers cannot manage roster
- team roles are context-specific

## 8.4 Player lookup rules
- existing player lookup must be restricted to valid contexts
- player search is not open public search
- minors especially must not become searchable directory entries

## 8.5 Team visibility rules
- unauthorized users should not get full team management data
- visibility checks should be server-side, not only UI-based

---

# 9. First UI screens to build

You do not need polished final design yet, but you do need usable flows.

## 9.1 Auth screens
- register
- login

## 9.2 Profile screen
- edit display name
- avatar
- privacy defaults

## 9.3 Player identity screen
- create player identity
- view player code
- claim status

## 9.4 Team list screen
- “My Teams”
- create team CTA

## 9.5 Create team screen
- name
- sport
- season label
- age group
- organization optional

## 9.6 Team detail screen
- summary
- roster tab
- roles tab
- settings tab

## 9.7 Add-to-roster flow
Must support:
- existing Bulldog player lookup
- create new player inline

## 9.8 Guardian relationship screen
- add child/player relationship
- relationship status

---

# 10. Suggested first sprint breakdown

Here’s a practical way to break this into early sprints.

---

## Sprint 1 — Auth and profile foundation
Build:
- users
- user_profiles
- register/login/me
- profile get/update
- sports seed

### Exit criteria
- user can sign up and edit profile

---

## Sprint 2 — Player and coach identities
Build:
- player_identities
- coach_identities
- create/get/claim player identity
- create coach identity
- player lookup/search skeleton

### Exit criteria
- player identity can exist without linked user
- current user can create own player/coach identity

---

## Sprint 3 — Guardian relationships + permission scaffold
Build:
- guardian relationships
- guardian endpoints
- policy framework
- minor-sensitive policy structure

### Exit criteria
- guardian relationship stored and queryable
- basic policy checks in place

---

## Sprint 4 — Team creation and team roles
Build:
- teams
- create/list/get/update team
- team role assignments
- team role endpoints

### Exit criteria
- a user can create a team and become team admin
- role assignments work

---

## Sprint 5 — Roster management
Build:
- team_memberships
- membership positions
- roster list/add/update/remove
- add existing player to roster
- create unclaimed player inline from roster

### Exit criteria
- a coach/admin can create a usable roster
- roster flow supports both existing and new players

---

# 11. Acceptance criteria by domain

## Identity acceptance criteria
- one user can exist without being forced into one sports role
- user and player identity are separate objects
- player identity can be unclaimed
- claim flow links identity correctly
- coach identity is separate from player identity

## Team acceptance criteria
- team creator becomes team admin
- team admin can assign coach/scorekeeper roles
- team admin/coach can add roster players
- roster membership preserves historical intent
- player can appear on multiple teams over time

## Safety acceptance criteria
- minors are not exposed through open player search
- profile/player/team visibility checks happen server-side
- guardian relationship exists independently from player ownership
- unauthorized users cannot manage roster or roles

---

# 12. “Do not break these rules” list for Codex

## Identity
- Do not merge `users` and `player_identities`
- Do not force every player identity to have a user immediately
- Do not make coach a mere boolean flag on user if persistent identity matters

## Permissions
- Do not encode all permissions only in the frontend
- Do not let roster/player lookup become open public search
- Do not let team role == global system power

## Data integrity
- Do not hard-delete team memberships casually
- Do not lose creator/audit attribution on created player identities
- Do not assume one player belongs to one team

---

# 13. Recommended seed data

At minimum:
- baseball
- softball

Optional:
- default visibility levels
- default role types
- optional starter achievement definitions can wait

---

# 14. Suggested first test cases

## Auth/profile
- register creates user and profile
- minor DOB correctly marks `is_minor`
- profile patch updates only authorized user’s profile

## Player identity
- self-created player identity works
- coach-created unclaimed player identity works
- claim links identity to user
- claimed identity cannot be hijacked

## Team roles
- team creator becomes team admin
- team admin can assign coach role
- non-admin cannot assign roles

## Roster
- add existing player to team works
- create new player from roster works
- inactive roster membership retains history
- unauthorized user cannot edit roster

## Guardian
- guardian can create relationship
- unrelated user cannot view another guardian relationship

---

# 15. Recommended folders/modules to touch first

```text
/modules
  /auth
  /identity
  /permissions
  /sports
  /teams
```

Within those, prioritize:
- models/entities
- repositories
- services
- policies
- routes/controllers
- validators

---

# 16. Suggested implementation checklist

## Foundation
- [ ] module scaffolding
- [ ] migration runner working
- [ ] auth middleware
- [ ] policy middleware
- [ ] audit logging helper

## Identity
- [ ] users table/model
- [ ] profiles table/model
- [ ] register/login/me
- [ ] profile endpoints
- [ ] player identity create/get/claim
- [ ] coach identity create
- [ ] guardian relationship create/list

## Teams
- [ ] teams table/model
- [ ] team create/list/get/update
- [ ] team roles assign/list/remove
- [ ] team memberships
- [ ] positions table/model
- [ ] roster list/add/update/remove
- [ ] add existing player to roster
- [ ] create unclaimed player from roster

## Policies
- [ ] canManageTeam
- [ ] canAssignTeamRole
- [ ] canManageRoster
- [ ] canViewPlayerIdentity
- [ ] canManageGuardianRelationship

---

# 17. Best immediate next coding target

If I were starting implementation this minute, I would do this in exact order:

1. `users`
2. `user_profiles`
3. auth register/login/me
4. `sports` seed
5. `player_identities`
6. `coach_identities`
7. create/get/claim player endpoints
8. `teams`
9. `team_role_assignments`
10. create/get/list team endpoints
11. `team_memberships`
12. roster endpoints
13. guardian relationships
14. harden policies

That gives you a functional path quickly.

---

