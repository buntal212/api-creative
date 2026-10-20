<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Username atau password tidak valid.',
            ], 422);
        }

        $user->tokens()->where('name', 'creative-admin-panel')->delete();

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $user->createToken('creative-admin-panel')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user->only(['id', 'name', 'username', 'email']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
