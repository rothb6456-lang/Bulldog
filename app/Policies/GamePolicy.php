<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GamePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Game $game): bool
    {
        return $user->can('viewGameData', $game->homeTeam)
            || $user->can('viewGameData', $game->awayTeam)
            || $this->score($user, $game);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Game $game): bool
    {
        return $this->score($user, $game)
            || $user->can('manageRoster', $game->homeTeam)
            || $user->can('manageRoster', $game->awayTeam);
    }

    public function delete(User $user, Game $game): bool
    {
        return $this->update($user, $game);
    }

    /**
     * Determine whether the user may score or manage this game's live state.
     * Scorekeepers are included because they are explicitly authorized at team level.
     */
    public function score(User $user, Game $game): bool
    {
        $ownershipTeamId = $game->ownership_team_id ?? $game->home_team_id;

        return DB::table('team_role_assignments')
            ->where('team_id', $ownershipTeamId)
            ->where('user_id', $user->id)
            ->whereIn('role_type', ['team_admin', 'coach', 'scorekeeper'])
            ->exists();
    }
}
