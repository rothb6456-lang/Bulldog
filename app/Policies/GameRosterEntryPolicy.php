<?php

namespace App\Policies;

use App\Models\GameRosterEntry;
use App\Models\User;

class GameRosterEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GameRosterEntry $entry): bool
    {
        return $user->can('viewGameData', $entry->team)
            || $user->can('viewGameData', $entry->game->homeTeam)
            || $user->can('viewGameData', $entry->game->awayTeam);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, GameRosterEntry $entry): bool
    {
        return $user->can('manageRoster', $entry->team)
            || $user->can('scoreGame', $entry->team);
    }

    public function delete(User $user, GameRosterEntry $entry): bool
    {
        return $user->can('manageRoster', $entry->team)
            || $user->can('scoreGame', $entry->team);
    }
}
