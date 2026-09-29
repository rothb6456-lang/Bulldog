<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Game, LineupEntry, TeamMembership};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameSetupController extends Controller
{
    private function editable(Game $game): void
    {
        $this->authorize('score', $game);
        abort_unless($game->status === 'draft', 422, 'Lineup setup is locked after the game starts.');
    }

    public function add(Request $request, Game $game)
    {
        $this->editable($game);
        $data = $request->validate(['player_identity_id' => 'required|uuid']);
        DB::transaction(function () use ($game, $data) {
            Game::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $entry = $game->rosterEntries()->where('player_identity_id', $data['player_identity_id'])->where('eligible_to_play', true)->firstOrFail();
            if (! $game->lineupEntries()->where('player_identity_id', $entry->player_identity_id)->exists()) {
                $game->lineupEntries()->create(['team_id' => $entry->team_id, 'player_identity_id' => $entry->player_identity_id, 'batting_order_slot' => 1 + (int) $game->lineupEntries()->where('team_id', $entry->team_id)->max('batting_order_slot'), 'lineup_status' => 'starter']);
            }
        });
        return back()->with('success', 'Player added to the lineup.');
    }

    public function lineup(Request $request, Game $game)
    {
        $this->editable($game);
        $data = $request->validate(['slots' => 'required|array', 'slots.*.order' => 'required|integer|min:1|max:25', 'slots.*.lineup_status' => 'required|in:starter,substitute,removed']);
        DB::transaction(function () use ($game, $data) {
            Game::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $entries = $game->lineupEntries()->get();
            abort_unless(count($data['slots']) === $entries->count(), 422, 'Reload the complete lineup.');
            $used = [];
            foreach ($entries as $entry) {
                $slot = $data['slots'][$entry->id] ?? null;
                abort_unless($slot, 422);
                $key = $entry->team_id.':'.$slot['order'];
                abort_if(isset($used[$key]), 422, 'Each batting slot must be unique within a team.');
                $used[$key] = true;
            }
            // Temporary offset permits swapping occupied unique batting slots.
            foreach ($entries as $entry) { $entry->update(['batting_order_slot' => $entry->batting_order_slot + 100]); }
            foreach ($entries as $entry) { $entry->update(['batting_order_slot' => $data['slots'][$entry->id]['order'], 'lineup_status' => $data['slots'][$entry->id]['lineup_status']]); }
        });
        return back()->with('success', 'Lineup saved.');
    }

    public function defense(Request $request, Game $game)
    {
        $this->editable($game);
        $data = $request->validate(['defense' => 'required|array', 'defense.*' => 'nullable|uuid|distinct']);
        DB::transaction(function () use ($game, $data) {
            Game::whereKey($game->id)->lockForUpdate()->firstOrFail();
            foreach ($data['defense'] as $position => $id) {
                abort_unless(in_array($position, ['P','C','1B','2B','3B','SS','LF','CF','RF','DP_FLEX']), 422);
                if (! $id) { $game->defensiveAssignments()->where('team_id', $game->home_team_id)->where('position_code', $position)->delete(); continue; }
                $player = $game->lineupEntries()->where('team_id', $game->home_team_id)->where('player_identity_id', $id)->firstOrFail();
                $game->defensiveAssignments()->updateOrCreate(['team_id' => $game->home_team_id, 'position_code' => $position], ['player_identity_id' => $player->player_identity_id]);
            }
        });
        return back()->with('success', 'Home team defense saved.');
    }

    public function roster(Request $request, Game $game)
    {
        $this->editable($game);
        $data = $request->validate(['eligible_players' => 'sometimes|array', 'eligible_players.*' => 'uuid', 'jersey' => 'sometimes|array', 'jersey.*' => 'nullable|string|max:10']);
        DB::transaction(function () use ($game, $data) {
            $entries = $game->rosterEntries()->get();
            abort_if(array_diff($data['eligible_players'] ?? [], $entries->pluck('player_identity_id')->all()), 422);
            foreach ($entries as $entry) {
                $entry->update(['eligible_to_play' => in_array($entry->player_identity_id, $data['eligible_players'] ?? [])]);
                if ($entry->team_membership_id && request()->user()->can('manageRoster', $entry->team)) {
                    TeamMembership::whereKey($entry->team_membership_id)->update(['jersey_number' => $data['jersey'][$entry->player_identity_id] ?? null]);
                }
            }
        });
        return back()->with('success', 'Game roster updated.');
    }

    public function reloadRoster(Game $game)
    {
        $this->editable($game);
        foreach (TeamMembership::whereIn('team_id', [$game->home_team_id, $game->away_team_id])->where('status', 'active')->where('membership_type', 'player')->get() as $member) {
            $game->rosterEntries()->firstOrCreate(['player_identity_id' => $member->player_identity_id], ['team_id' => $member->team_id, 'team_membership_id' => $member->id, 'eligible_to_play' => true, 'roster_status' => 'active']);
        }
        return back()->with('success', 'New team players loaded into the game.');
    }
}
