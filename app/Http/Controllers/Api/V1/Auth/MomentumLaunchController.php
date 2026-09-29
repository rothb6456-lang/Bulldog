<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MomentumLaunchController extends Controller
{
    public function launch(Request $request)
    {
        abort_unless($request->user()->status === 'active' && ! $request->user()->is_minor, 403);
        $code = Str::random(64);
        DB::table('momentum_launch_codes')->where('expires_at', '<', now())->delete();
        DB::table('momentum_launch_codes')->insert(['code_hash' => hash('sha256', $code), 'user_id' => $request->user()->id, 'expires_at' => now()->addSeconds(90)]);
        return redirect()->away(rtrim(config('ecosystem.momentum_url'), '/').'/#bulldog_launch='.$code)
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function exchange(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|size:64']);
        return DB::transaction(function () use ($data) {
            $hash = hash('sha256', $data['code']);
            $code = DB::table('momentum_launch_codes')->where('code_hash', $hash)->lockForUpdate()->first();
            abort_unless($code && now()->lt($code->expires_at), 422, 'Launch expired. Open Momentum again from your Bulldog hub.');
            DB::table('momentum_launch_codes')->where('code_hash', $hash)->delete();
            $user = User::findOrFail($code->user_id);
            abort_unless($user->status === 'active' && ! $user->is_minor && $user->hasVerifiedEmail(), 403);
            $token = $user->createToken('Momentum launch', ['*'], now()->addDays(30))->plainTextToken;
            return response()->json(['token' => $token, 'user' => ['id' => $user->id, 'name' => $user->name]])->header('Cache-Control', 'no-store');
        });
    }
}
