<?php

namespace App\Http\Controllers\Api\V1\Training;

use App\Actions\Training\ResolveTrainingIdentity;
use App\Http\Controllers\Controller;
use App\Models\{PlayerTrainingProfile, PlayerTrainingGoal};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingProfileController extends Controller
{
    public function store(Request $request, ResolveTrainingIdentity $resolve)
    {
        $data = $request->validate([
            'experience_level' => 'nullable|in:beginner,intermediate,advanced',
            'goals' => 'present|array|max:30', 'goals.*.title' => 'required|string|max:255',
        ]);
        $player = $resolve->execute($request->user());
        DB::transaction(function () use ($data, $player, $request) {
            \App\Models\PlayerIdentity::whereKey($player->id)->lockForUpdate()->firstOrFail();
            PlayerTrainingProfile::updateOrCreate(['player_identity_id' => $player->id], ['experience_level' => $data['experience_level'] ?? null]);
            $titles = array_unique(array_column($data['goals'], 'title'));
            PlayerTrainingGoal::where('player_identity_id', $player->id)->where('source', 'momentum')->whereNotIn('title', $titles)->update(['status' => 'abandoned']);
            foreach ($titles as $title) {
                PlayerTrainingGoal::updateOrCreate(['player_identity_id' => $player->id, 'source' => 'momentum', 'title' => $title], ['status' => 'active', 'created_by_user_id' => $request->user()->id]);
            }
        });
        return response()->json(['player_identity_id' => $player->id]);
    }
}
