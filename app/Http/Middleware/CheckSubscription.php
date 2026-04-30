<?php

namespace App\Http\Middleware;

use App\Models\Masjid;
use App\Models\Subscription;
use App\Tenant\TenantContext;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the resolved tenant has a valid, non-expired subscription.
 *
 * Behavior:
 *   - SuperAdmin bypass → always passes through.
 *   - subscription_status = 'active' AND expiry in future → passes.
 *   - Subscription expired but within grace window → passes with a flash warning.
 *   - Subscription expired beyond grace window → redirects to subscription-expired page.
 *   - subscription_status = 'none' / no subscription record → redirects to expired page
 *     (treat no subscription as expired so they must contact SuperAdmin).
 *
 * Must run AFTER CheckTenantActive.
 * Must NOT apply to: subscription.expired, tenant.suspended, tenant.pending, logout.
 */
class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        // SuperAdmin always bypasses.
        if (TenantContext::isBypassed() || ! TenantContext::isResolved()) {
            return $next($request);
        }

        $masjidId = TenantContext::get();

        /** @var Masjid|null $masjid */
        $masjid = $request->attributes->get('current_masjid')
            ?? Cache::remember(
                "tenant_status:{$masjidId}",
                60,
                fn () => Masjid::withoutGlobalScopes()->select(['id', 'status', 'subscription_status', 'subscription_expiry'])
                    ->find($masjidId)
            );

        if ($masjid === null) {
            return $next($request);
        }

        $subscriptionStatus = $masjid->subscription_status;
        $expiry = $masjid->subscription_expiry
            ? Carbon::parse($masjid->subscription_expiry)
            : null;

        if ($subscriptionStatus === 'active' && ($expiry === null || $expiry->isFuture())) {
            return $next($request);
        }

        $latestBillingSubscription = Subscription::query()
            ->where('tenant_id', $masjidId)
            ->latest('id')
            ->first();

        if (
            $latestBillingSubscription
            && $latestBillingSubscription->status === 'active'
            && $latestBillingSubscription->end_date
            && Carbon::parse($latestBillingSubscription->end_date)->isFuture()
        ) {
            return $next($request);
        }

        // Backward compatibility for legacy tenant_subscriptions grace period.
        $graceDays = Cache::remember(
            "tenant_grace:{$masjidId}",
            300,
            fn () => (int) (\App\Models\TenantSubscription::withoutGlobalScopes()
                ->where('masjid_id', $masjidId)
                ->orderByDesc('end_date')
                ->value('grace_days') ?? 0)
        );

        if ($expiry && $graceDays > 0) {
            $graceEnd = $expiry->copy()->addDays($graceDays);
            if (Carbon::now()->lessThanOrEqualTo($graceEnd)) {
                // Within grace period — allow with a warning flash.
                session()->flash(
                    'subscription_warning',
                    'Your subscription has expired. You are in a grace period until '
                        . $graceEnd->format('d M Y') . '.'
                );
                return $next($request);
            }
        }

        return redirect()->route('subscription.index')
            ->with('payment_status', 'pending')
            ->with('payment_message', 'Langganan anda tiada/expired. Sila langgan pelan untuk teruskan penggunaan sistem.');
    }
}
