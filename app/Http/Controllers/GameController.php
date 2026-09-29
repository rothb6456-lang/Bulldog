<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function create(Request $request)
    {
        $managedTeams = \App\Models\Team::where('status', 'active')->whereHas('roleAssignments', fn ($q) => $q->where('user_id', $request->user()->id)->whereIn('role_type', ['coach', 'team_admin']))->get();
        $allTeams = \App\Models\Team::where('status', 'active')->with('sport')->get(['id', 'name', 'sport_id']);
        $rulesets = \App\Models\Ruleset::all();
        return view('games.create', ['myTeams' => $managedTeams->load('sport'), 'opponents' => $allTeams, 'rulesets' => $rulesets]);
    }

    public function store(\App\Http\Requests\Games\StoreGameRequest $request, \App\Actions\Games\CreateGameAction $action)
    {
        $data = $request->validated();
        $home = \App\Models\Team::findOrFail($data['home_team_id']);
        $away = \App\Models\Team::findOrFail($data['away_team_id']);
        abort_unless($home->status === 'active' && $away->status === 'active' && $home->sport_id === $away->sport_id && \App\Models\Ruleset::whereKey($data['ruleset_id'])->where('sport_id', $home->sport_id)->exists(), 422, 'Choose teams and rules for the same sport.');
        return redirect()->route('games.show', $action->execute($data, $request->user()->id));
    }

    public function show(\App\Models\Game $game)
    {
        $this->authorize('view', $game);
        $game->load('sport', 'ruleset', 'homeTeam', 'awayTeam', 'gameRosterEntries.playerIdentity', 'lineupEntries.playerIdentity', 'defensiveAssignments');
        $gameRosterEntries = $game->gameRosterEntries;
        $lineupEntries = $game->lineupEntries;
        $benchPlayers = $gameRosterEntries->whereNotIn('player_identity_id', $lineupEntries->pluck('player_identity_id'));
        $defensiveAssignments = $game->defensiveAssignments->pluck('player_identity_id', 'position_code');
        return view('games.show', compact('game', 'gameRosterEntries', 'lineupEntries', 'benchPlayers', 'defensiveAssignments'));
    }

    public function demo(Request $request, \App\Actions\Games\CreatePracticeGameAction $action)
    {
        $data = $request->validate(['sport_id' => 'required|uuid|exists:sports,id']);
        return redirect()->route('games.score', $action->execute($request->user(), \App\Models\Sport::findOrFail($data['sport_id'])));
    }

    public function finalize(Request $request, \App\Models\Game $game)
    {
        $this->authorize('score', $game);
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $game) {
            $locked = \App\Models\Game::lockForUpdate()->findOrFail($game->id);
            if ($locked->status !== 'finalized') {
                $locked->update(['status' => 'finalized', 'finalized_at' => now(), 'finalized_by_user_id' => $request->user()->id]);
            }
        });
        return redirect()->route('games.score', $game)->with('success', $game->is_demo ? 'Practice complete. Your real stats and XP are unchanged.' : 'Game finalized. Player history updated.');
    }

    public function start(\App\Models\Game $game)
    {
        $this->authorize('score', $game);
        abort_if($game->status === 'finalized', 422);
        $game->update(['status' => 'in_progress']);
        return redirect()->route('games.score', $game);
    }
}
