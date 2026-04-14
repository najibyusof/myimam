<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Masjid;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return view('auth.two-factor', [
                'enabled' => true,
                'secret' => null,
                'otpAuthUrl' => null,
                'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
            ]);
        }

        $secret = $request->session()->get('2fa.setup_secret');

        if (!$secret) {
            $secret = (new Google2FA())->generateSecretKey();
            $request->session()->put('2fa.setup_secret', $secret);
        }

        $otpAuthUrl = (new Google2FA())->getQRCodeUrl(
            config('app.name', 'Laravel'),
            $user->email,
            $secret
        );

        return view('auth.two-factor', [
            'enabled' => false,
            'secret' => $secret,
            'otpAuthUrl' => $otpAuthUrl,
            'recoveryCodes' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $secret = $request->session()->get('2fa.setup_secret');
        if (!$secret) {
            throw ValidationException::withMessages([
                'code' => __('Two-factor setup session expired. Please try again.'),
            ]);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($secret, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => __('Invalid authentication code.'),
            ]);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('2fa.setup_secret');

        return redirect()->route('two-factor.edit')->with('status', 'Two-factor authentication enabled.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->forget('2fa.setup_secret');

        return redirect()->route('two-factor.edit')->with('status', 'Two-factor authentication disabled.');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        $pendingUserId = (int) $request->session()->get('auth.2fa.user_id');
        $user = User::query()->find($pendingUserId);
        if ($user && $user->peranan !== 'superadmin' && $user->id_masjid) {
            $masjidStatus = Masjid::query()->whereKey($user->id_masjid)->value('status');
            if ($masjidStatus === 'suspended') {
                $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);

                return redirect()->route('login')->withErrors([
                    'email' => 'Tenant suspended. Please contact administrator.',
                ]);
            }
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $pendingUserId = (int) $request->session()->get('auth.2fa.user_id');
        $remember = (bool) $request->session()->get('auth.2fa.remember', false);

        $user = User::query()->find($pendingUserId);
        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
            return redirect()->route('login');
        }

        if ($user->peranan !== 'superadmin' && $user->id_masjid) {
            $masjidStatus = Masjid::query()->whereKey($user->id_masjid)->value('status');
            if ($masjidStatus === 'suspended') {
                $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);

                throw ValidationException::withMessages([
                    'code' => 'Tenant suspended. Please contact administrator.',
                ]);
            }
        }

        $inputCode = trim($request->string('code')->toString());
        $verified = false;

        if (preg_match('/^\d{6}$/', $inputCode) === 1) {
            $verified = (new Google2FA())->verifyKey($user->two_factor_secret, $inputCode);
        }

        if (!$verified) {
            $recoveryCodes = collect($user->two_factor_recovery_codes ?? []);
            if ($recoveryCodes->contains($inputCode)) {
                $user->forceFill([
                    'two_factor_recovery_codes' => $recoveryCodes->reject(fn (string $code): bool => hash_equals($code, $inputCode))->values()->all(),
                ])->save();
                $verified = true;
            }
        }

        if (!$verified) {
            throw ValidationException::withMessages([
                'code' => __('Invalid authentication or recovery code.'),
            ]);
        }

        Auth::loginUsingId($user->id, $remember);

        $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
