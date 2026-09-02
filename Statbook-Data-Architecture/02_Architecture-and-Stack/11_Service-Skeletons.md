
# Controller / Service Skeletons for the first endpoints

I’ll keep these framework-neutral so Codex can adapt them.

We should implement these first:

1. `POST /auth/register`
2. `POST /auth/login`
3. `GET /auth/me`
4. `POST /players`
5. `POST /players/{id}/claim`
6. `POST /teams`
7. `POST /teams/{id}/roles`
8. `POST /teams/{id}/roster`

---

# 1. Register

## Controller
```ts
async function registerController(req, res) {
  const input = validateRegisterInput(req.body);

  const result = await authService.registerUser({
    email: input.email,
    password: input.password,
    displayName: input.displayName,
    dateOfBirth: input.dateOfBirth,
  });

  return res.status(201).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class AuthService {
  async registerUser(input) {
    const existing = await userRepository.findByEmail(input.email);
    if (existing) {
      throw new ConflictError('Email already in use');
    }

    const userId = generateUuid();
    const bulldogUserCode = await codeService.generateBulldogUserCode();
    const passwordHash = await passwordService.hash(input.password);
    const isMinor = ageService.computeIsMinor(input.dateOfBirth);

    await userRepository.create({
      id: userId,
      bulldogUserCode,
      email: input.email,
      passwordHash,
      dateOfBirth: input.dateOfBirth,
      isMinor,
      accountStatus: 'active',
    });

    await userProfileRepository.create({
      userId,
      displayName: input.displayName,
      defaultVisibilityLevel: 'private',
    });

    await auditLogService.record({
      actorUserId: userId,
      actionType: 'user.registered',
      objectType: 'user',
      objectId: userId,
      metadata: {},
    });

    const session = await sessionService.createForUser(userId);

    return {
      user: await userRepository.findById(userId),
      profile: await userProfileRepository.findByUserId(userId),
      session,
    };
  }
}
```

---

# 2. Login

## Controller
```ts
async function loginController(req, res) {
  const input = validateLoginInput(req.body);

  const result = await authService.loginUser({
    email: input.email,
    password: input.password,
  });

  return res.status(200).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class AuthService {
  async loginUser(input) {
    const user = await userRepository.findByEmail(input.email);
    if (!user || !user.passwordHash) {
      throw new UnauthorizedError('Invalid credentials');
    }

    const ok = await passwordService.verify(input.password, user.passwordHash);
    if (!ok) {
      throw new UnauthorizedError('Invalid credentials');
    }

    if (user.accountStatus !== 'active') {
      throw new ForbiddenError('Account is not active');
    }

    const session = await sessionService.createForUser(user.id);

    return {
      user,
      session,
    };
  }
}
```

---

# 3. Me

## Controller
```ts
async function meController(req, res) {
  const currentUserId = requireAuth(req);

  const result = await userService.getCurrentUserSummary(currentUserId);

  return res.status(200).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class UserService {
  async getCurrentUserSummary(userId) {
    const user = await userRepository.findById(userId);
    const profile = await userProfileRepository.findByUserId(userId);
    const playerIdentity = await playerIdentityRepository.findByUserId(userId);
    const coachIdentity = await coachIdentityRepository.findByUserId(userId);
    const teamRoles = await teamRoleAssignmentRepository.listByUserId(userId);

    return {
      user,
      profile,
      identities: {
        player: playerIdentity || null,
        coach: coachIdentity || null,
      },
      teamRoles,
    };
  }
}
```

---

# 4. Create Player Identity

## Controller
```ts
async function createPlayerController(req, res) {
  const currentUserId = requireAuth(req);
  const input = validateCreatePlayerInput(req.body);

  const result = await playerIdentityService.createPlayerIdentity({
    actorUserId: currentUserId,
    userId: input.userId ?? null,
    birthYear: input.birthYear ?? null,
    primarySportId: input.primarySportId ?? null,
    context: input.context ?? 'self',
  });

  return res.status(201).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class PlayerIdentityService {
  async createPlayerIdentity(input) {
    if (input.context === 'self' && input.userId && input.userId !== input.actorUserId) {
      throw new ForbiddenError('Cannot create self player identity for another user');
    }

    if (!input.userId) {
      // unclaimed okay, usually coach/team context
      // later this should validate team context more explicitly
    }

    const playerId = generateUuid();
    const playerCode = await codeService.generatePlayerCode();

    await playerIdentityRepository.create({
      id: playerId,
      playerCode,
      userId: input.userId,
      createdByUserId: input.actorUserId,
      birthYear: input.birthYear,
      primarySportId: input.primarySportId,
      claimStatus: input.userId ? 'claimed' : 'unclaimed',
      identityStatus: 'active',
    });

    await auditLogService.record({
      actorUserId: input.actorUserId,
      actionType: 'player_identity.created',
      objectType: 'player_identity',
      objectId: playerId,
      metadata: {
        context: input.context,
      },
    });

    return await playerIdentityRepository.findById(playerId);
  }
}
```

---

# 5. Claim Player Identity

## Controller
```ts
async function claimPlayerController(req, res) {
  const currentUserId = requireAuth(req);
  const playerId = req.params.playerId;

  const result = await playerIdentityService.claimPlayerIdentity({
    actorUserId: currentUserId,
    playerIdentityId: playerId,
  });

  return res.status(200).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class PlayerIdentityService {
  async claimPlayerIdentity(input) {
    const player = await playerIdentityRepository.findById(input.playerIdentityId);
    if (!player) {
      throw new NotFoundError('Player identity not found');
    }

    if (player.userId && player.userId !== input.actorUserId) {
      throw new ConflictError('Player identity already claimed');
    }

    if (player.userId === input.actorUserId) {
      return player;
    }

    await playerIdentityRepository.update(player.id, {
      userId: input.actorUserId,
      claimStatus: 'claimed',
    });

    await auditLogService.record({
      actorUserId: input.actorUserId,
      actionType: 'player_identity.claimed',
      objectType: 'player_identity',
      objectId: player.id,
      metadata: {},
    });

    return await playerIdentityRepository.findById(player.id);
  }
}
```

---

# 6. Create Team

## Controller
```ts
async function createTeamController(req, res) {
  const currentUserId = requireAuth(req);
  const input = validateCreateTeamInput(req.body);

  const result = await teamService.createTeam({
    actorUserId: currentUserId,
    sportId: input.sportId,
    organizationId: input.organizationId ?? null,
    name: input.name,
    nickname: input.nickname ?? null,
    seasonLabel: input.seasonLabel ?? null,
    ageGroup: input.ageGroup ?? null,
    city: input.city ?? null,
    stateRegion: input.stateRegion ?? null,
  });

  return res.status(201).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class TeamService {
  async createTeam(input) {
    const teamId = generateUuid();
    const teamCode = await codeService.generateTeamCode();

    await teamRepository.create({
      id: teamId,
      teamCode,
      sportId: input.sportId,
      organizationId: input.organizationId,
      name: input.name,
      nickname: input.nickname,
      seasonLabel: input.seasonLabel,
      ageGroup: input.ageGroup,
      city: input.city,
      stateRegion: input.stateRegion,
      createdByUserId: input.actorUserId,
      visibilityLevel: 'team',
      status: 'active',
    });

    await teamRoleAssignmentRepository.create({
      id: generateUuid(),
      teamId,
      userId: input.actorUserId,
      roleType: 'team_admin',
      grantedByUserId: input.actorUserId,
      status: 'active',
    });

    await auditLogService.record({
      actorUserId: input.actorUserId,
      actionType: 'team.created',
      objectType: 'team',
      objectId: teamId,
      metadata: {},
    });

    return await teamRepository.findById(teamId);
  }
}
```

---

# 7. Assign Team Role

## Controller
```ts
async function assignTeamRoleController(req, res) {
  const currentUserId = requireAuth(req);
  const teamId = req.params.teamId;
  const input = validateAssignTeamRoleInput(req.body);

  const result = await teamRoleService.assignRole({
    actorUserId: currentUserId,
    teamId,
    targetUserId: input.userId,
    roleType: input.roleType,
  });

  return res.status(201).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class TeamRoleService {
  async assignRole(input) {
    const canAssign = await permissionPolicyService.canAssignTeamRole(
      input.actorUserId,
      input.teamId
    );

    if (!canAssign) {
      throw new ForbiddenError('You do not have permission to assign team roles');
    }

    const existing = await teamRoleAssignmentRepository.findByTeamUserRole(
      input.teamId,
      input.targetUserId,
      input.roleType
    );

    if (existing) {
      throw new ConflictError('Role already assigned');
    }

    const assignmentId = generateUuid();

    await teamRoleAssignmentRepository.create({
      id: assignmentId,
      teamId: input.teamId,
      userId: input.targetUserId,
      roleType: input.roleType,
      grantedByUserId: input.actorUserId,
      status: 'active',
    });

    await auditLogService.record({
      actorUserId: input.actorUserId,
      actionType: 'team_role.assigned',
      objectType: 'team_role_assignment',
      objectId: assignmentId,
      metadata: {
        teamId: input.teamId,
        targetUserId: input.targetUserId,
        roleType: input.roleType,
      },
    });

    return await teamRoleAssignmentRepository.findById(assignmentId);
  }
}
```

---

# 8. Add Roster Player

This endpoint supports:
- add existing player
- create new unclaimed player inline

## Controller
```ts
async function addRosterPlayerController(req, res) {
  const currentUserId = requireAuth(req);
  const teamId = req.params.teamId;
  const input = validateAddRosterPlayerInput(req.body);

  const result = await rosterService.addPlayerToRoster({
    actorUserId: currentUserId,
    teamId,
    existingPlayerId: input.playerId ?? null,
    createPlayer: input.createPlayer ?? null,
    jerseyNumber: input.jerseyNumber ?? null,
    positions: input.positions ?? [],
  });

  return res.status(201).json({
    data: result,
    meta: {},
    error: null,
  });
}
```

## Service
```ts
class RosterService {
  async addPlayerToRoster(input) {
    const canManage = await permissionPolicyService.canManageRoster(
      input.actorUserId,
      input.teamId
    );

    if (!canManage) {
      throw new ForbiddenError('You do not have permission to manage this roster');
    }

    let playerIdentityId = input.existingPlayerId;

    if (!playerIdentityId && input.createPlayer) {
      const newPlayer = await playerIdentityService.createPlayerIdentity({
        actorUserId: input.actorUserId,
        userId: null,
        birthYear: input.createPlayer.birthYear ?? null,
        primarySportId: input.createPlayer.primarySportId ?? null,
        context: 'team_roster',
      });

      playerIdentityId = newPlayer.id;
    }

    if (!playerIdentityId) {
      throw new ValidationError('Either playerId or createPlayer is required');
    }

    const existingMembership =
      await teamMembershipRepository.findActivePlayerMembership(
        input.teamId,
        playerIdentityId
      );

    if (existingMembership) {
      throw new ConflictError('Player is already on this roster');
    }

    const membershipId = generateUuid();

    await teamMembershipRepository.create({
      id: membershipId,
      teamId: input.teamId,
      membershipType: 'player',
      playerIdentityId,
      jerseyNumber: input.jerseyNumber,
      status: 'active',
      createdByUserId: input.actorUserId,
    });

    for (const positionCode of input.positions) {
      await teamMembershipPositionRepository.create({
        id: generateUuid(),
        teamMembershipId: membershipId,
        positionCode,
      });
    }

    await auditLogService.record({
      actorUserId: input.actorUserId,
      actionType: 'team_roster.player_added',
      objectType: 'team_membership',
      objectId: membershipId,
      metadata: {
        teamId: input.teamId,
        playerIdentityId,
      },
    });

    return await teamMembershipRepository.getRosterEntryDetail(membershipId);
  }
}
```

---

# 9. Permission Policy Skeleton

```ts
class PermissionPolicyService {
  async canAssignTeamRole(actorUserId: string, teamId: string): Promise<boolean> {
    const assignment = await teamRoleAssignmentRepository.findActiveRole(
      teamId,
      actorUserId,
      'team_admin'
    );

    return !!assignment;
  }

  async canManageRoster(actorUserId: string, teamId: string): Promise<boolean> {
    const roles = await teamRoleAssignmentRepository.listActiveRolesForUserOnTeam(
      actorUserId,
      teamId
    );

    return roles.some(r => ['team_admin', 'coach'].includes(r.roleType));
  }

  async canViewPlayerIdentity(actorUserId: string, playerIdentityId: string): Promise<boolean> {
    const player = await playerIdentityRepository.findById(playerIdentityId);
    if (!player) return false;

    if (player.userId === actorUserId) return true;

    const guardian = await guardianRelationshipRepository.findByGuardianAndPlayer(
      actorUserId,
      playerIdentityId
    );
    if (guardian) return true;

    // later: team-context checks
    return false;
  }
}
```

---

# 10. Validation Input Shapes

## Register
```ts
type RegisterInput = {
  email: string;
  password: string;
  displayName: string;
  dateOfBirth?: string;
};
```

## Create Player
```ts
type CreatePlayerInput = {
  userId?: string | null;
  birthYear?: number | null;
  primarySportId?: string | null;
  context?: 'self' | 'team_roster';
};
```

## Create Team
```ts
type CreateTeamInput = {
  sportId: string;
  organizationId?: string | null;
  name: string;
  nickname?: string | null;
  seasonLabel?: string | null;
  ageGroup?: string | null;
  city?: string | null;
  stateRegion?: string | null;
};
```

## Assign Team Role
```ts
type AssignTeamRoleInput = {
  userId: string;
  roleType: 'team_admin' | 'coach' | 'scorekeeper' | 'viewer';
};
```

## Add Roster Player
```ts
type AddRosterPlayerInput = {
  playerId?: string | null;
  createPlayer?: {
    birthYear?: number | null;
    primarySportId?: string | null;
  } | null;
  jerseyNumber?: string | null;
  positions?: string[];
};
```

---
