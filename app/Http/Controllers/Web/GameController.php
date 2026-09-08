<?php

namespace App\Http\Controllers;

use App\Actions\Games\CreateGameAction;
use App\Http\Requests\Games\StoreGameRequest;
use App\Models\Game;
use App\Models\Ruleset;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected CreateGameAction $createGameAction;

    public function __construct(CreateGameAction $createGameAction)
    {
        $this->createGameAction = $createGameAction;
    }

    /**
     * Show the Game Creation scheduler page.
     */
    public function create(Request $request)
    {
        // Fetch all teams where the user has scheduling rights (Admin or Coach)
        $managedTeamIds = $request->user()->id
            ? DB::table('team_role_assignments')
            ->where('user_id', $request->user()->id)
            ->whereIn('role_type', ['team_admin', 'coach'])
            ->pluck('team_id')
            : collect();

        $managedTeams = Team::whereIn('id', $managedTeamIds)->with('sport')->get();

        // Fetch possible opponents grouped by sport for front-end real-time filtering
        $allTeams = Team::with('sport')->get();

        // Fetch seeded rulesets
        $rulesets = Ruleset::all();

        return view('games.create', compact('managedTeams', 'allTeams', 'rulesets'));
    }

    /**
     * Store scheduled game and redirect to workspace.
     */
    public function store(StoreGameRequest $request)
    {
        $game = $this->createGameAction->execute(
            $request->validated(),
            $request->user()->id
        );

        return redirect()->route('games.show', $game->id)
            ->with('success', 'Game scheduled successfully. Your roster has been prepared.');
    }

    /**
     * Show the Pre-Game interactive workspace.
     */
    public function show(Game $game)
    {
        // Eager load nested associations to avoid N+1 query bottlenecks on the lineup diamond
        $game->load([
            'sport',
            'ruleset',
            'homeTeam.memberships.playerIdentity',
            'awayTeam.memberships.playerIdentity',
            'gameRosterEntries.playerIdentity',
            'lineupEntries.playerIdentity',
            'defensiveAssignments.playerIdentity'
        ]);

        return view('games.show', compact('game'));
    }
}
