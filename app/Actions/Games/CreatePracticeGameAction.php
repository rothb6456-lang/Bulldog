<?php

namespace App\Actions\Games;

use App\Actions\Teams\CreateTeamAction;
use App\Actions\Roster\AddRosterPlayerAction;
use App\Models\{Game, Sport, Ruleset, Team, User};
use Illuminate\Support\Facades\DB;

class CreatePracticeGameAction
{
    public function execute(User $user, Sport $sport): Game
    {
        return DB::transaction(function () use ($user, $sport) {
            // Serialize repeated clicks; synthetic rosters never include real minors.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $teams = [];
            foreach (['Bulldogs', 'Cardinals'] as $side) {
                $team = Team::where('created_by_user_id', $user->id)->where('sport_id', $sport->id)->where('status', 'demo')->where('name', 'Practice '.$side)->first();
                if (! $team) {
                    $team = app(CreateTeamAction::class)->execute(['name' => 'Practice '.$side, 'sport_id' => $sport->id, 'season_label' => 'Practice'], $user->id);
                    $team->update(['status' => 'demo']);
                    foreach (['Alex', 'Jordan', 'Taylor', 'Casey', 'Morgan', 'Riley', 'Avery', 'Cameron', 'Jamie'] as $i => $name) {
                        app(AddRosterPlayerAction::class)->execute($team->id, ['display_name' => $name.' (demo)', 'jersey_number' => (string) ($i + 1)], $user->id);
                    }
                }
                $teams[] = $team;
            }
            $ruleset = Ruleset::where('sport_id', $sport->id)->firstOrFail();
            $game = app(CreateGameAction::class)->execute(['home_team_id' => $teams[0]->id, 'away_team_id' => $teams[1]->id, 'ruleset_id' => $ruleset->id, 'scheduled_at' => now(), 'location' => 'Guided practice'], $user->id);
            $game->update(['is_demo' => true, 'status' => 'in_progress']);
            return $game;
        });
    }
}
