<?php

namespace App\Policies;

use App\Models\LineupEntry;
use App\Models\User;

class LineupEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LineupEntry $entry): bool
    {
        return $user->can('viewGameData', $entry->team)
            || $user->can('viewGameData', $entry->game->homeTeam)
            || $user->can('viewGameData', $entry->game->awayTeam);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, LineupEntry $entry): bool
    {
        return $user->can('manageRoster', $entry->team)
            || $user->can('scoreGame', $entry->team);
    }

    public function delete(User $user, LineupEntry $entry): bool
    {
        return $user->can('manageRoster', $entry->team)
            || $user->can('scoreGame', $entry->team);
    }
}
