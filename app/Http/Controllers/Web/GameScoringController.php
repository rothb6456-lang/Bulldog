<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameStateSnapshot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameScoringController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the interactive, live play-by-play scoring visual workspace.
     */
    public function show(Request $request, Game $game): View
    {
        $this->authorize('score', $game);

        $game->load([
            'sport',
            'ruleset',
            'homeTeam.memberships.playerIdentity',
            'awayTeam.memberships.playerIdentity',
            'gameRosterEntries.playerIdentity',
            'lineupEntries.playerIdentity',
        ]);

        $currentState = GameStateSnapshot::where('game_id', $game->id)
            ->orderBy('sequence_number', 'desc')
            ->first();

        if (! $currentState) {
            $currentState = GameStateSnapshot::create([
                'game_id' => $game->id,
                'sequence_number' => 0,
                'inning_number' => 1,
                'half_inning' => 'top',
                'outs' => 0,
                'balls' => 0,
                'strikes' => 0,
                'score_home' => 0,
                'score_away' => 0,
                'base_state' => '000',
            ]);
        }

        $boxScore = \App\Models\GamePlayerStat::where('game_id', $game->id)->with('playerIdentity', 'team')->get()->groupBy('player_identity_id');
        $events = \App\Models\GameEvent::where('game_id', $game->id)->where('is_voided', false)->orderByDesc('sequence_number')->limit(30)->get();
        return view('games.score', compact('game', 'currentState', 'boxScore', 'events'));
    }

    public function store(\App\Http\Requests\Scoring\StoreScoringEventRequest $request, Game $game, \App\Actions\Scoring\RecordScoringEventAction $action)
    {
        $action->execute($game->id, $request->validated(), $request->user()->id);
        return redirect()->route('games.score', $game);
    }
}
