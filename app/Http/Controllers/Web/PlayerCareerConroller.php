<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PlayerIdentity;
use App\Models\SeasonAggregate;
use App\Models\CareerAggregate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PlayerCareerController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request, PlayerIdentity $player)
    {
        // Enforce contextual view policies [80, 271]
        $this->authorize('view', $player);

        // 1. Fetch overall career summaries
        $careerStats = CareerAggregate::where('subject_type', 'player')
            ->where('subject_id', $player->id)
            ->get()
            ->keyBy('stat_key');

        // 2. Fetch season-by-season aggregates
        $seasonStats = SeasonAggregate::where('subject_type', 'player')
            ->where('subject_id', $player->id)
            ->get()
            ->groupBy('season_label');

        // 3. Render the visual workspace
        return view('profile.career', compact('player', 'careerStats', 'seasonStats'));
    }
}
