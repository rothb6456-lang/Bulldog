<?php

namespace App\Http\Controllers\Api\Scoring;

use App\Actions\Scoring\RecordScoringEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Scoring\StoreScoringEventRequest;
use App\Models\Game;
use App\Models\GameEvent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameEventController extends Controller
{
    use AuthorizesRequests;

    protected RecordScoringEventAction $recordScoringEvent;

    public function __construct(RecordScoringEventAction $recordScoringEvent)
    {
        $this->recordScoringEvent = $recordScoringEvent;
    }

    /**
     * Record a new game play event.
     */
    public function store(StoreScoringEventRequest $request, Game $game): JsonResponse
    {
        $event = $this->recordScoringEvent->execute(
            $game->id,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Event logged successfully.',
            'event' => $event->load('eventPlayers'),
        ], 210);
    }

    /**
     * Retrieve chronological history of game events.
     */
    public function index(Request $request, Game $game): JsonResponse
    {
        $this->authorize('view', $game);

        $events = GameEvent::where('game_id', $game->id)
            ->where('is_voided', false)
            ->with('eventPlayers.playerIdentity')
            ->orderBy('sequence_number', 'asc')
            ->get();

        return response()->json([
            'events' => $events
        ]);
    }
}
