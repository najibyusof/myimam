<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuthResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Register new user
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'peranan' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'peranan' => $validated['peranan'] ?? 'User',
            'aktif' => true,
        ]);

        // Assign default role
        $user->assignRole('user');

        // Create authentication token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json(new AuthResource((object)[
            'user' => $user,
            'token' => $token,
            'expires_in' => now()->addDay(),
        ]), 201);
    }
}
