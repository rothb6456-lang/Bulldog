<?php

namespace App\Policies;

use App\Models\TeamMembership;
use App\Models\User;

class TeamMembershipPolicy
{
    /**
     * Determine whether the user can view the specific roster membership details.
     */
    public function view(User $user, TeamMembership $membership): bool
    {
        // Delegate view check back to the team
        return $user->can('view', $membership->team);
    }

    /**
     * Determine whether the user can update a roster membership (e.g., jersey number, field positions).
     */
    public function update(User $user, TeamMembership $membership): bool
    {
        // Delegate roster write checks back to the team
        return $user->can('manageRoster', $membership->team);
    }

    /**
     * Determine whether the user can delete/deactivate a roster membership.
     */
    public function delete(User $user, TeamMembership $membership): bool
    {
        // Delegate roster write checks back to the team
        return $user->can('manageRoster', $membership->team);
    }
}