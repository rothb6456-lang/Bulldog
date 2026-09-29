<?php

namespace App\Policies;

use App\Models\PlayerIdentity;
use App\Models\User;

class PlayerIdentityPolicy
{
    public function view(User $user, PlayerIdentity $player): bool
    {
        return $player->user_id === $user->id
            || $player->created_by_user_id === $user->id
            || $user->guardianRelationships()->where('player_identity_id', $player->id)->where('verification_status', 'verified')->exists()
            || $player->memberships()->where('status', 'active')->whereHas('team.roleAssignments', fn ($q) =>
                $q->where('user_id', $user->id)->whereIn('role_type', ['team_admin', 'coach'])
            )->exists();
    }

    public function viewTraining(User $user, PlayerIdentity $player): bool
    {
        return $player->user_id === $user->id
            || $user->guardianRelationships()->where('player_identity_id', $player->id)->where('verification_status', 'verified')->exists();
    }

    public function share(User $user, PlayerIdentity $player): bool
    {
        return $player->user_id === $user->id && ! $user->is_minor;
    }
}
