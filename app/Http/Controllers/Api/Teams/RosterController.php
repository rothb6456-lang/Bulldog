<?php

namespace App\Http\Controllers\Api\Teams;

use App\Actions\Roster\AddRosterPlayerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreRosterEntryRequest;
use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class RosterController extends Controller
{
    use AuthorizesRequests;

    protected AddRosterPlayerAction $addRosterPlayerAction;

    public function __construct(AddRosterPlayerAction $addRosterPlayerAction)
    {
        $this->addRosterPlayerAction = $addRosterPlayerAction;
    }

    /**
     * Add a player to a team's roster.
     */
    public function store(StoreRosterEntryRequest $request, Team $team): JsonResponse
    {
        // Enforce roster editing permissions
        $this->authorize('manageRoster', $team);

        $membership = $this->addRosterPlayerAction->execute(
            $team->id,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Player rostered successfully.',
            'membership' => $membership->load('playerIdentity')
        ], 201);
    }
}
