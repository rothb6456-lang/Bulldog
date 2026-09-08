<?php

namespace App\Policies;

use App\Models\DefensiveAssignment;
use App\Models\User;

class DefensiveAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DefensiveAssignment $assignment): bool
    {
        return $user->can('viewGameData', $assignment->team)
            || $user->can('viewGameData', $assignment->game->homeTeam)
            || $user->can('viewGameData', $assignment->game->awayTeam);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DefensiveAssignment $assignment): bool
    {
        return $user->can('manageRoster', $assignment->team)
            || $user->can('scoreGame', $assignment->team);
    }

    public function delete(User $user, DefensiveAssignment $assignment): bool
    {
        return $user->can('manageRoster', $assignment->team)
            || $user->can('scoreGame', $assignment->team);
    }
}
