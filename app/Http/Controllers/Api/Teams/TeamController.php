<?php

namespace App\Http\Controllers\Api\Teams;

use App\Actions\Teams\CreateTeamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use AuthorizesRequests;
    protected CreateTeamAction $createTeamAction;

    public function __construct(CreateTeamAction $createTeamAction)
    {
        $this->createTeamAction = $createTeamAction;
    }

    /**
     * Handle team creation.
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->createTeamAction->execute(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Team created successfully.',
            'team' => [
                'id' => $team->id,
                'team_code' => $team->team_code,
                'name' => $team->name,
                'age_group' => $team->age_group,
                'season_label' => $team->season_label,
            ]
        ], 201);
    }

    /**
     * Fetch a specific team's details.
     */
    public function show(Request $request, Team $team): JsonResponse
    {
        // Enforce team-context permission check
        $this->authorize('view', $team);

        return response()->json([
            'team' => $team->load(['sport', 'memberships.playerIdentity'])
        ]);
    }
}
