<?php

namespace App\Actions\Games;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateGameAction
{
    /**
     * Execute the transaction-safe game scheduling workflow.
     */
    public function execute(array $data, string $creatorUserId): Game
    {
        return DB::transaction(function () use ($data, $creatorUserId) {
            // Generate unique Game Code
            do {
                $gameCode = 'GM-' . Str::upper(Str::random(8));
            } while (Game::where('game_code', $gameCode)->exists());

            // 1. Create the Game
            $game = Game::create([
                'game_code' => $gameCode,
                'sport_id' => Team::find($data['home_team_id'])->sport_id, // Inherit sport from home team
                'home_team_id' => $data['home_team_id'],
                'away_team_id' => $data['away_team_id'],
                'ruleset_id' => $data['ruleset_id'],
                'scheduled_at' => $data['scheduled_at'],
                'location' => $data['location'],
                'status' => 'draft', // Standard initial state
                'ownership_team_id' => $data['home_team_id'], // Home team owns by default
                'created_by_user_id' => $creatorUserId,
            ]);

            // 2. Pre-populate game roster tables from active team memberships
            $this->loadTeamRosterIntoGame($game, $game->home_team_id);
            $this->loadTeamRosterIntoGame($game, $game->away_team_id);

            return $game;
        });
    }

    /**
     * Helper to load current team players as eligible game roster slots.
     */
    protected function loadTeamRosterIntoGame(Game $game, string $teamId): void
    {
        $team = Team::find($teamId);
        if (!$team) {
            return;
        }

        $activePlayerMemberships = $team->memberships()
            ->where('membership_type', 'player')
            ->where('status', 'active')
            ->get();

        foreach ($activePlayerMemberships as $membership) {
            $game->gameRosterEntries()->create([
                'team_id' => $teamId,
                'player_identity_id' => $membership->player_identity_id,
                'team_membership_id' => $membership->id,
                'roster_status' => 'active',
                'eligible_to_play' => true, // Default to true
            ]);
        }
    }
}