<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PlayerIdentity;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShareLinkController extends Controller
{
    /**
     * Create high-entropy token to build secure, non-indexed link.
     */
    public function store(Request $request, PlayerIdentity $player)
    {
        // Require Coach, Admin, or Guardian relationship to initiate sharing [81, 104]
        $this->authorize('share', $player);

        $share = ShareLink::create([
            'object_type' => 'player_card',
            'object_id' => $player->id,
            'token' => Str::random(40), // Collision-safe, unguessable string
            'created_by_user_id' => Auth::id(),
            'expires_at' => now()->addDays(30), // Configurable expiration
            'is_active' => true,
        ]);

        return response()->json([
            'share_url' => route('share.resolve', ['token' => $share->token]),
        ], 201);
    }

    /**
     * Resolve the token to render the visual card without letting bots index it.
     */
    public function show(string $token)
    {
        $share = ShareLink::where('token', $token)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        if ($share->object_type === 'player_card') {
            $player = PlayerIdentity::findOrFail($share->object_id);

            // Load player profile metadata and stats
            $player->load(['memberships.team', 'profile']);

            // Extract stats
            $careerStats = DB::table('career_aggregates')
                ->where('scope_type', 'player')
                ->where('scope_id', $player->id)
                ->pluck('stat_value', 'stat_key')
                ->toArray();

            // Check if ANY statistic in their history came from imported offline spreadsheet sources [75, 148]
            $hasImportedStats = DB::table('imported_stat_lines')
                ->where('subject_type', 'player')
                ->where('subject_id', $player->id)
                ->exists();

            return view('sharing.share-card', compact('player', 'careerStats', 'hasImportedStats'));
        }

        abort(404);
    }
}
