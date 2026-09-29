<?php

namespace App\Actions\Training;

use App\Models\{User, PlayerIdentity};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResolveTrainingIdentity
{
    public function execute(User $user): PlayerIdentity
    {
        return DB::transaction(function () use ($user) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            return PlayerIdentity::firstOrCreate(['user_id' => $user->id], [
                'player_code' => 'PLY-'.Str::upper(Str::random(10)), 'claim_status' => 'claimed',
                'display_name' => $user->name, 'created_by_user_id' => $user->id,
            ]);
        });
    }
}
