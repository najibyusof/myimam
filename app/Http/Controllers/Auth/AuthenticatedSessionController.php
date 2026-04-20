<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\LoginDemoAccountService;
use App\Services\CmsPageBuilderService;
use App\Services\CmsRenderer;
use App\Models\Masjid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(
        Request $request,
        CmsPageBuilderService $builderService,
        CmsRenderer $renderer,
        LoginDemoAccountService $demoAccountService
    ): View {
        $masjid = $request->attributes->get('current_masjid');
        $selectedRole = $request->filled('role') ? (string) $request->query('role') : null;
        $demoData = $demoAccountService->forLoginPage($masjid, $selectedRole);
        $builderPage = $builderService->getRenderablePage('login', $masjid?->id);

        if ($builderPage) {
            $renderedHtml = $renderer->render($builderPage->content_json, [
                'tenantMasjid' => $masjid,
                'demoAccounts' => $demoData['accounts'],
                'activeDemoRole' => $demoData['active_role'],
                'activeDemoAccount' => $demoData['active_account'],
                'demoCopyPayload' => $demoData['copy_payload'],
            ]);

            return view('cms.page', [
                'pageTitle' => $builderPage->title,
                'seoTitle' => $builderPage->seo_title ?: $builderPage->title,
                'seoDescription' => $builderPage->seo_meta_description,
                'renderedHtml' => $renderedHtml,
                'tenantMasjid' => $masjid,
                'tenantSource' => $request->attributes->get('current_masjid_source'),
                'bodyClass' => 'bg-slate-100 text-slate-900 antialiased',
            ]);
        }

        return view('auth.login', [
            'demoAccounts' => $demoData['accounts'],
            'activeDemoRole' => $demoData['active_role'],
            'activeDemoAccount' => $demoData['active_account'],
            'demoCopyPayload' => $demoData['copy_payload'],
            'showDemoAccounts' => true,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if ($user && $user->peranan !== 'superadmin' && $user->id_masjid) {
            $masjidStatus = Masjid::query()
                ->whereKey($user->id_masjid)
                ->value('status');

            if ($masjidStatus === 'suspended') {
                Auth::logout();

                throw ValidationException::withMessages([
                    'email' => 'Tenant suspended. Please contact administrator.',
                ]);
            }
        }

        if ($user && method_exists($user, 'hasTwoFactorEnabled') && $user->hasTwoFactorEnabled()) {
            Auth::logout();

            $request->session()->put('auth.2fa.user_id', $user->id);
            $request->session()->put('auth.2fa.remember', $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('2fa.challenge');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
