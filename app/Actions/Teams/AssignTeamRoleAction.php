<?php

namespace App\Actions\Teams;

use App\Models\TeamRoleAssignment;

class AssignTeamRoleAction
{
    /**
     * Assign a specific contextual role to a user on a team.
     */
    public function execute(string $teamId, string $userId, string $roleType, string $grantedByUserId): TeamRoleAssignment
    {
        return TeamRoleAssignment::updateOrCreate(
            [
                'team_id' => $teamId,
                'user_id' => $userId,
                'role_type' => $roleType,
            ],
            [
                'granted_by_user_id' => $grantedByUserId,
            ]
        );
    }
}