<?php

namespace App\Http\Middleware;

use App\Models\Masjid;
use App\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the resolved tenant (Masjid) is in an active state.
 *
 * Checks:
 *   - If TenantContext is bypassed (SuperAdmin), passes through immediately.
 *   - If no tenant is resolved, blocks access — user has no assigned masjid.
 *   - If the masjid status is 'suspended', redirects to the tenant-suspended page.
 *   - If the masjid status is 'pending', redirects to the tenant-pending page.
 *
 * Must run AFTER ResolveTenant.
 * Must NOT apply to routes: tenant.suspended, tenant.pending, logout.
 */
class CheckTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // SuperAdmin bypasses all tenant checks.
        if (TenantContext::isBypassed()) {
            return $next($request);
        }

        // No tenant resolved — user account has no masjid assigned.
        if (! TenantContext::isResolved()) {
            abort(403, 'Your account is not assigned to any mosque. Please contact the administrator.');
        }

        $masjidId = TenantContext::get();

        // Cache the masjid status for 60 seconds to avoid a DB hit on every request.
        $masjid = Cache::remember(
            "tenant_status:{$masjidId}",
            60,
            fn () => Masjid::withoutGlobalScopes()->select(['id', 'nama', 'status', 'subscription_status', 'subscription_expiry'])
                ->find($masjidId)
        );

        if ($masjid === null) {
            abort(404, 'Mosque tenant record not found.');
        }

        if ($masjid->status === 'suspended') {
            return redirect()->route('tenant.suspended')
                ->with('masjid_name', $masjid->nama ?? '');
        }

        if ($masjid->status === 'pending') {
            return redirect()->route('tenant.pending');
        }

        // Store masjid on request for downstream middleware/controllers.
        $request->attributes->set('current_masjid', $masjid);

        return $next($request);
    }
}
