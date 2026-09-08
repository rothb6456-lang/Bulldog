<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function isTeamAdmin(User $user, Team $team): bool
    {
        return $team->roleAssignments()
            ->where('user_id', $user->id)
            ->where('role_type', 'team_admin')
            ->exists();
    }

    public function isCoach(User $user, Team $team): bool
    {
        return $team->roleAssignments()
            ->where('user_id', $user->id)
            ->where('role_type', 'coach')
            ->exists();
    }

    public function isScorekeeper(User $user, Team $team): bool
    {
        return $team->roleAssignments()
            ->where('user_id', $user->id)
            ->where('role_type', 'scorekeeper')
            ->exists();
    }

    public function isViewer(User $user, Team $team): bool
    {
        return $team->roleAssignments()
            ->where('user_id', $user->id)
            ->where('role_type', 'viewer')
            ->exists();
    }

    public function isRosteredPlayer(User $user, Team $team): bool
    {
        $playerIdentity = $user->playerIdentity;

        if (! $playerIdentity) {
            return false;
        }

        return $team->memberships()
            ->where('player_identity_id', $playerIdentity->id)
            ->where('status', 'active')
            ->exists();
    }

    public function isVerifiedGuardian(User $user, Team $team): bool
    {
        $verifiedChildIds = $user->guardianRelationships()
            ->where('verification_status', 'verified')
            ->pluck('player_identity_id');

        if ($verifiedChildIds->isEmpty()) {
            return false;
        }

        return $team->memberships()
            ->whereIn('player_identity_id', $verifiedChildIds)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Determine whether the user can view the team.
     * Context-aware: Admins, Coaches, Scorekeepers, Viewers, Roster Players, or Verified Guardians.
     */
    public function view(User $user, Team $team): bool
    {
        return $this->isTeamAdmin($user, $team)
            || $this->isCoach($user, $team)
            || $this->isScorekeeper($user, $team)
            || $this->isViewer($user, $team)
            || $this->isRosteredPlayer($user, $team)
            || $this->isVerifiedGuardian($user, $team);
    }

    /**
     * Determine whether the user can update the team metadata.
     * Only Team Admins can update team settings.
     */
    public function update(User $user, Team $team): bool
    {
        return $this->isTeamAdmin($user, $team);
    }

    /**
     * Determine whether the user can assign/remove team roles.
     * Only Team Admins can manage team roles.
     */
    public function assignRoles(User $user, Team $team): bool
    {
        return $this->isTeamAdmin($user, $team);
    }

    /**
     * Determine whether the user can manage the roster.
     * Team Admins and Coaches can manage the roster.
     */
    public function manageRoster(User $user, Team $team): bool
    {
        return $this->isTeamAdmin($user, $team)
            || $this->isCoach($user, $team);
    }

    /**
     * Determine whether the user can manage scorekeeping or live game admin tasks.
     * Team Admins, Coaches, and Scorekeepers can manage game scoring.
     */
    public function scoreGame(User $user, Team $team): bool
    {
        return $this->isTeamAdmin($user, $team)
            || $this->isCoach($user, $team)
            || $this->isScorekeeper($user, $team);
    }

    /**
     * Determine whether the user can view game data read-only.
     * Team Admins, Coaches, Scorekeepers, Viewers, rostered players, and verified guardians can view.
     */
    public function viewGameData(User $user, Team $team): bool
    {
        return $this->view($user, $team);
    }
}
