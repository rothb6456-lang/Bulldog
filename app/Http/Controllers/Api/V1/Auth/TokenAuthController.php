<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TokenAuthController extends Controller
{
    /**
     * Issue a Sanctum personal access token for the same account used by web auth.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || $user->status !== 'active' || $user->is_minor || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 422);
        }

        $token = $user->createToken($validated['device_name'] ?? 'unknown-device')->plainTextToken;

        return response()->json(['token' => $token, 'user' => ['id' => $user->id, 'name' => $user->name]])->header('Cache-Control', 'no-store');
    }
}
