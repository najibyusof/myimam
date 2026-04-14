<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuthResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Login user and return authentication token
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->aktif) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive. Please contact an administrator.'],
            ]);
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json(new AuthResource((object)[
            'user' => $user,
            'token' => $token,
            'expires_in' => now()->addDay(),
        ]));
    }

    /**
     * Refresh authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json(new AuthResource((object)[
            'user' => $user,
            'token' => $token,
            'expires_in' => now()->addDay(),
        ]));
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // TODO: Implement password reset logic
        // For now, return success message
        return response()->json([
            'message' => 'If an account exists with this email, a password reset link has been sent.',
        ]);
    }
}
