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

        return view('games.score', compact('game', 'currentState'));
    }
}
