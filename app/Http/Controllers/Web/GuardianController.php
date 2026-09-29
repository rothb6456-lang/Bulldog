<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{GuardianRelationship, Team};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardianController extends Controller
{
    public function index(Request $request)
    {
        // Do not disclose child details until both acceptance and confirmation.
        $relationships = GuardianRelationship::where('invited_email', $request->user()->email)->with('team')->get();
        return view('ecosystem.guardians', compact('relationships'));
    }

    public function invite(Request $request, Team $team)
    {
        $this->authorize('assignRoles', $team);
        $data = $request->validate(['player_identity_id' => 'required|uuid', 'email' => 'required|email|max:255']);
        $membership = $team->memberships()->where('player_identity_id', $data['player_identity_id'])->where('status', 'active')->firstOrFail();
        $player = $membership->playerIdentity;
        abort_unless($player && ! $player->user_id && (! $player->birth_year || $player->birth_year >= now()->year - 18), 422, 'Select a private youth identity.');
        $email = strtolower($data['email']);
        abort_if($email === $request->user()->email, 422, 'A different adult must accept the invitation.');
        $relationship = GuardianRelationship::firstOrNew(['player_identity_id' => $player->id, 'invited_email' => $email]);
        abort_if($relationship->exists && ($relationship->verification_status === 'verified' || ($relationship->verification_status === 'accepted' && $relationship->expires_at->isFuture())), 422, 'This relationship is already awaiting confirmation or verified.');
        $relationship->fill(['team_id' => $team->id, 'invited_by_user_id' => $request->user()->id, 'verification_status' => 'invited', 'guardian_user_id' => null, 'accepted_at' => null, 'confirmed_at' => null, 'confirmed_by_user_id' => null, 'expires_at' => now()->addDays(7)])->save();
        return back()->with('success', 'Invitation ready. Ask the guardian to register with that email, verify it, then open Guardian invitations. No child information is sent by email.');
    }

    public function accept(Request $request, GuardianRelationship $relationship)
    {
        DB::transaction(function () use ($request, $relationship) {
            $relation = GuardianRelationship::lockForUpdate()->findOrFail($relationship->id);
            abort_unless($relation->invited_email === $request->user()->email && ! $request->user()->is_minor, 403);
            abort_unless($relation->verification_status === 'invited' && $relation->expires_at->isFuture(), 422, 'Invitation expired or already accepted.');
            $relation->update(['guardian_user_id' => $request->user()->id, 'verification_status' => 'accepted', 'accepted_at' => now()]);
        });
        return back()->with('success', 'Accepted. A team administrator must confirm before player access is granted.');
    }

    public function confirm(Request $request, GuardianRelationship $relationship)
    {
        $this->authorize('assignRoles', $relationship->team);
        abort_if($relationship->guardian_user_id === $request->user()->id, 403);
        DB::transaction(function () use ($relationship, $request) {
            $relation = GuardianRelationship::lockForUpdate()->findOrFail($relationship->id);
            abort_unless($relation->verification_status === 'accepted' && $relation->expires_at->isFuture(), 422);
            $relation->update(['verification_status' => 'verified', 'confirmed_by_user_id' => $request->user()->id, 'confirmed_at' => now()]);
        });
        return back()->with('success', 'Guardian relationship confirmed.');
    }

    public function revoke(Request $request, GuardianRelationship $relationship)
    {
        $this->authorize('assignRoles', $relationship->team);
        $relationship->update(['verification_status' => 'revoked']);
        return back()->with('success', 'Guardian access revoked. The audit record is retained.');
    }
}
