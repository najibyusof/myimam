<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\LogAktivitiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly LogAktivitiService $log) {}
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['signature_image'], $validated['remove_signature_image']);

        $request->user()->fill($validated);

        if ($request->boolean('remove_signature_image') && $request->user()->signature_path) {
            Storage::disk('public')->delete($request->user()->signature_path);
            $request->user()->signature_path = null;
            $this->log->record(LogAktivitiService::JENIS_DELETE, 'Profil', 'Tandatangan Dipadam', [
                'rujukan_id' => $request->user()->id,
                'butiran'    => 'Tandatangan digital dipadam dari profil pengguna.',
            ], $request);
        }

        if ($request->hasFile('signature_image')) {
            if ($request->user()->signature_path) {
                Storage::disk('public')->delete($request->user()->signature_path);
            }
            $request->user()->signature_path = $request->file('signature_image')->store('signature-images', 'public');
            $this->log->record(LogAktivitiService::JENIS_UPDATE, 'Profil', 'Tandatangan Dimuat Naik', [
                'rujukan_id' => $request->user()->id,
                'butiran'    => 'Tandatangan digital dimuat naik ke profil pengguna.',
            ], $request);
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
