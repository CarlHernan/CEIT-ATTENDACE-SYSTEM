<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::with('role', 'societies')->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! in_array($user->role?->slug, ['officer', 'lsg_officer'], true)) {
            return response()->json([
                'message' => 'Unauthorized role.',
            ], 403);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'flutterflow', [
            'events:view',
            'attendance:record',
        ])->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->slug,
                    'is_society_officer' => (bool) $user->is_society_officer,
                    'is_lsg_officer' => (bool) $user->is_lsg_officer,
                    'societies' => $user->societies->map(fn ($soc) => [
                        'id' => $soc->id,
                        'name' => $soc->name,
                        'slug' => $soc->slug,
                        'abbreviation' => $soc->abbreviation,
                        'position' => $soc->pivot->position ?? null,
                    ]),
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'data' => ['message' => 'Logged out'],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('role', 'societies');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->slug,
                'is_society_officer' => (bool) $user->is_society_officer,
                'is_lsg_officer' => (bool) $user->is_lsg_officer,
                'societies' => $user->societies->map(fn ($soc) => [
                    'id' => $soc->id,
                    'name' => $soc->name,
                    'slug' => $soc->slug,
                    'abbreviation' => $soc->abbreviation,
                    'position' => $soc->pivot->position ?? null,
                ]),
            ],
        ]);
    }
}
