<?php

namespace App\Http\Middleware;

use App\Models\Masjid;
use App\Tenant\PublicTenantResolver;
use App\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active tenant for the request and stores it in TenantContext.
 *
 * Resolution order:
 *   1. Authenticated non-superadmin user → use the user's assigned masjid.
 *   2. Public resolver → query (?masjid=), subdomain, then session.
 *   3. Authenticated superadmin → bypass model scoping, but still expose any
 *      resolved tenant in request/app container for previewing tenant content.
 */
class ResolveTenant
{
    public function __construct(private PublicTenantResolver $publicTenantResolver)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        TenantContext::flush();

        $user = $request->user();
        $resolvedMasjid = null;
        $source = null;

        if ($user && $user->peranan !== 'superadmin' && $user->id_masjid) {
            $resolvedMasjid = Masjid::query()->find((int) $user->id_masjid);
            $source = 'user';
        } else {
            $resolved = $this->publicTenantResolver->resolveWithSource($request);
            $resolvedMasjid = $resolved['masjid'];
            $source = $resolved['source'];
        }

        if ($user && $user->peranan === 'superadmin') {
            TenantContext::bypass();
        } elseif ($resolvedMasjid) {
            TenantContext::set((int) $resolvedMasjid->id);
        }

        if ($resolvedMasjid) {
            $request->attributes->set('current_masjid', $resolvedMasjid);
            $request->attributes->set('current_masjid_source', $source);

            app()->instance('currentMasjid', $resolvedMasjid);
            app()->instance('currentMasjidSource', $source);

            $request->session()->put('tenant.masjid_id', $resolvedMasjid->id);
        } else {
            $request->attributes->remove('current_masjid');
            $request->attributes->remove('current_masjid_source');

            if (app()->bound('currentMasjid')) {
                app()->forgetInstance('currentMasjid');
            }

            if (app()->bound('currentMasjidSource')) {
                app()->forgetInstance('currentMasjidSource');
            }

            if ($request->filled('masjid')) {
                $request->session()->forget('tenant.masjid_id');
            }
        }

        return $next($request);
    }
}
