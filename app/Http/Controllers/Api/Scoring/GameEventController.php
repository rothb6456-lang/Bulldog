<?php

namespace App\Http\Controllers\Api\Scoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GameEventController extends Controller
{
    public function store(\App\Http\Requests\Scoring\StoreScoringEventRequest $request, \App\Models\Game $game, \App\Actions\Scoring\RecordScoringEventAction $action)
    {
        $event = $action->execute($game->id, $request->validated(), $request->user()->id);
        return response()->json(['event' => $event->load('eventPlayers')], 201);
    }

    public function index(Request $request, \App\Models\Game $game)
    {
        $this->authorize('view', $game);
        return response()->json(['events' => \App\Models\GameEvent::where('game_id', $game->id)->where('is_voided', false)->orderBy('sequence_number')->get()]);
    }
}
