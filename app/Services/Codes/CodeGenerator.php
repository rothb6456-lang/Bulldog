<?php

namespace App\Services\Codes;

use App\Models\PlayerIdentity;
use App\Models\Team;
use Illuminate\Support\Str;

class CodeGenerator
{
    /**
     * Generate a unique human-readable Player Code (e.g., PLY-A8B9C2).
     */
    public function generatePlayerCode(): string
    {
        do {
            $code = 'PLY-' . Str::upper(Str::random(6));
        } while (PlayerIdentity::where('player_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique human-readable Team Code (e.g., TM-WARRIORS).
     */
    public function generateTeamCode(string $teamName): string
    {
        $slug = Str::upper(Str::slug(substr($teamName, 0, 10)));
        
        do {
            $code = 'TM-' . $slug . '-' . Str::upper(Str::random(4));
        } while (Team::where('team_code', $code)->exists());

        return $code;
    }
}