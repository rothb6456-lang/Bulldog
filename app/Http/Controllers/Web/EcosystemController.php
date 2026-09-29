<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{PlayerIdentity, CareerAggregate, TrainingSession};
use Illuminate\Http\Request;

class EcosystemController extends Controller
{
    public function hub(Request $request)
    {
        $player = $request->user()->playerIdentity;
        $sessionCount = $player ? TrainingSession::where('player_identity_id', $player->id)->count() : 0;
        return view('ecosystem.hub', compact('player', 'sessionCount'));
    }

    public function player(Request $request, PlayerIdentity $player)
    {
        $this->authorize('view', $player);
        $player->load('memberships.team');
        $stats = CareerAggregate::where('subject_type', 'player')->where('subject_id', $player->id)->get();
        if ($request->user()->can('viewTraining', $player)) {
            $player->load('trainingProfile', 'trainingGoals');
        }
        return response()->view('ecosystem.player', compact('player', 'stats'))->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store');
    }
}
