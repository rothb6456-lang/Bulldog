<?php

namespace App\Actions\Roster;

use App\Models\PlayerIdentity;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Services\Codes\CodeGenerator;
use Illuminate\Support\Facades\DB;

class AddRosterPlayerAction
{
    protected CodeGenerator $codeGenerator;

    public function __construct(CodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Add a player to a team roster.
     * Supports linking an existing PlayerIdentity or creating an unclaimed one inline.
     */
    public function execute(string $teamId, array $data, string $creatorUserId): TeamMembership
    {
        return DB::transaction(function () use ($teamId, $data, $creatorUserId) {
            $playerIdentityId = $data['player_identity_id'] ?? null;

            // Pathway B: If no existing player is selected, create a new unclaimed PlayerIdentity
            if (!$playerIdentityId) {
                $playerCode = $this->codeGenerator->generatePlayerCode();

                $player = PlayerIdentity::create([
                    'player_code' => $playerCode,
                    'display_name' => $data['display_name'],
                    'birth_year' => $data['birth_year'] ?? null,
                    'claim_status' => 'unclaimed',
                    'created_by_user_id' => $creatorUserId,
                ]);

                $playerIdentityId = $player->id;
            }

            // Create the roster team membership
            return TeamMembership::create([
                'team_id' => $teamId,
                'player_identity_id' => $playerIdentityId,
                'membership_type' => 'player',
                'jersey_number' => $data['jersey_number'] ?? null,
                'positions_json' => $data['positions'] ?? null, // Cast automatically array -> JSON
                'status' => 'active',
            ]);
        });
    }
}