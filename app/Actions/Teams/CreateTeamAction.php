<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Services\Codes\CodeGenerator;
use Illuminate\Support\Facades\DB;

class CreateTeamAction
{
    protected CodeGenerator $codeGenerator;

    public function __construct(CodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Execute the team creation workflow.
     */
    public function execute(array $data, string $creatorUserId): Team
    {
        return DB::transaction(function () use ($data, $creatorUserId) {
            // Generate a unique team code
            $teamCode = $this->codeGenerator->generateTeamCode($data['name']);

            // 1. Create the team
            $team = Team::create([
                'team_code' => $teamCode,
                'name' => $data['name'],
                'sport_id' => $data['sport_id'],
                'age_group' => $data['age_group'] ?? null,
                'season_label' => $data['season_label'] ?? null,
                'created_by_user_id' => $creatorUserId,
                'status' => 'active',
            ]);

            // 2. Automatically assign the creator as the Team Admin
            $team->roleAssignments()->create([
                'user_id' => $creatorUserId,
                'role_type' => 'team_admin',
                'granted_by_user_id' => $creatorUserId,
            ]);

            return $team;
        });
    }
}