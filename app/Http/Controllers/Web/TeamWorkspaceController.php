<?php

namespace App\Http\Controllers\Web;

use App\Actions\Teams\CreateTeamAction;
use App\Actions\Roster\AddRosterPlayerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Requests\Teams\StoreRosterEntryRequest;
use App\Models\{Team, Sport, PlayerIdentity, GuardianRelationship};
use Illuminate\Http\Request;

class TeamWorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::whereHas('roleAssignments', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('sport')->withCount('memberships')->get();
        return view('ecosystem.teams', compact('teams'));
    }

    public function create()
    {
        return view('ecosystem.create-team', ['sports' => Sport::whereIn('code', ['baseball', 'softball'])->get()]);
    }

    public function store(StoreTeamRequest $request, CreateTeamAction $action)
    {
        return redirect()->route('teams.show', $action->execute($request->validated(), $request->user()->id));
    }

    public function show(Request $request, Team $team)
    {
        $this->authorize('view', $team);
        $team->load('sport', 'memberships.playerIdentity');
        $players = collect();
        if ($request->filled('search') && $request->user()->can('manageRoster', $team)) {
            // Search only identities this account already has authority to view.
            $players = PlayerIdentity::where(function ($q) use ($request) {
                $q->where('created_by_user_id', $request->user()->id)->orWhere('user_id', $request->user()->id)
                    ->orWhereHas('memberships.team.roleAssignments', fn ($q) => $q->where('user_id', $request->user()->id)->whereIn('role_type', ['coach', 'team_admin']));
            })->where('display_name', 'like', '%'.$request->string('search').'%')->limit(20)->get();
        }
        $relationships = $request->user()->can('assignRoles', $team)
            ? GuardianRelationship::where('team_id', $team->id)->with('playerIdentity')->get() : collect();
        return view('ecosystem.team', compact('team', 'players', 'relationships'));
    }

    public function roster(StoreRosterEntryRequest $request, Team $team, AddRosterPlayerAction $action)
    {
        $this->authorize('manageRoster', $team);
        $action->execute($team->id, $request->validated(), $request->user()->id);
        return back()->with('success', 'Player added to the roster.');
    }
}
