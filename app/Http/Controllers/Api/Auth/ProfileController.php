<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Services\LogAktivitiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function __construct(private readonly LogAktivitiService $log) {}
    /**
     * Get current authenticated user profile
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $request->user()->id],
            'peranan' => ['nullable', 'string'],
            'signature_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=50,max_width=2000,max_height=600'],
            'remove_signature_image' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();

        if ($request->boolean('remove_signature_image') && $user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
            $user->signature_path = null;
            $this->log->record(LogAktivitiService::JENIS_DELETE, 'Profil', 'Tandatangan Dipadam', [
                'rujukan_id' => $user->id,
                'butiran'    => 'Tandatangan digital dipadam dari profil pengguna melalui API.',
            ], $request);
        }

        if ($request->hasFile('signature_image')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $user->signature_path = $request->file('signature_image')->store('signature-images', 'public');
            $this->log->record(LogAktivitiService::JENIS_UPDATE, 'Profil', 'Tandatangan Dimuat Naik', [
                'rujukan_id' => $user->id,
                'butiran'    => 'Tandatangan digital dimuat naik ke profil pengguna melalui API.',
            ], $request);
        }

        $user->fill(array_filter($validated, fn($value, $key) => !in_array($key, ['signature_image', 'remove_signature_image'], true), ARRAY_FILTER_USE_BOTH));
        $user->save();

        return response()->json(new UserResource($user));
    }

    public function uploadSignature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'signature_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=50,max_width=2000,max_height=600'],
        ]);

        $user = $request->user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $user->signature_path = $request->file('signature_image')->store('signature-images', 'public');
        $user->save();

        $this->log->record(LogAktivitiService::JENIS_UPDATE, 'Profil', 'Tandatangan Dimuat Naik', [
            'rujukan_id' => $user->id,
            'butiran'    => 'Tandatangan digital dimuat naik ke profil pengguna melalui API.',
        ], $request);

        return response()->json([
            'message' => 'Signature uploaded successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function removeSignature(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
            $user->signature_path = null;
            $user->save();
            $this->log->record(LogAktivitiService::JENIS_DELETE, 'Profil', 'Tandatangan Dipadam', [
                'rujukan_id' => $user->id,
                'butiran'    => 'Tandatangan digital dipadam dari profil pengguna melalui API.',
            ], $request);
        }

        return response()->json([
            'message' => 'Signature removed successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        // Revoke all tokens except current one
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}
