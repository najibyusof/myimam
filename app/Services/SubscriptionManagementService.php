<?php

namespace App\Services;

use App\Models\Masjid;
use App\Models\Plan;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionManagementService
{
    public function createPlan(array $data): SubscriptionPlan
    {
        $plan = SubscriptionPlan::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'duration_months' => $data['duration_months'],
            'features' => $data['features'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->syncToNewPlanCatalog($plan);

        return $plan;
    }

    public function updatePlan(SubscriptionPlan $plan, array $data): SubscriptionPlan
    {
        $originalName = (string) $plan->name;

        $plan->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'duration_months' => $data['duration_months'],
            'features' => $data['features'] ?? null,
            'is_active' => $data['is_active'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $plan = $plan->refresh();
        $this->syncToNewPlanCatalog($plan, $originalName);

        return $plan;
    }

    private function syncToNewPlanCatalog(SubscriptionPlan $legacyPlan, ?string $originalName = null): void
    {
        $targetName = (string) $legacyPlan->name;

        $newPlan = Plan::query()
            ->when($originalName, fn ($query) => $query->where('name', $originalName))
            ->orWhere('name', $targetName)
            ->first();

        $payload = [
            'name' => $targetName,
            'price' => $legacyPlan->price,
            'duration_days' => max(1, ((int) $legacyPlan->duration_months) * 30),
            'features' => $legacyPlan->features,
        ];

        if ($newPlan) {
            $newPlan->update($payload);
            return;
        }

        Plan::query()->create($payload);
    }

    public function assignTenantSubscription(Masjid $masjid, array $data, User $actor): TenantSubscription
    {
        return DB::transaction(function () use ($masjid, $data, $actor) {
            $plan = SubscriptionPlan::query()->findOrFail((int) $data['plan_id']);

            $startDate = Carbon::parse($data['start_date'])->startOfDay();
            $endDate = !empty($data['end_date'])
                ? Carbon::parse($data['end_date'])->endOfDay()
                : $startDate->copy()->addMonths((int) $plan->duration_months)->subDay()->endOfDay();

            // Close any existing active or grace subscription for this tenant.
            TenantSubscription::query()
                ->where('masjid_id', $masjid->id)
                ->whereIn('status', ['active', 'grace'])
                ->update(['status' => 'cancelled']);

            $subscription = TenantSubscription::create([
                'masjid_id' => $masjid->id,
                'plan_id' => $plan->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'status' => $data['status'],
                'grace_days' => (int) ($data['grace_days'] ?? 7),
                'amount_paid' => $data['amount_paid'] ?? 0,
                'payment_reference' => $data['payment_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            $masjid->update([
                'subscription_status' => $subscription->status,
                'subscription_expiry' => $subscription->end_date,
            ]);

            return $subscription->refresh();
        });
    }

    public function syncSubscriptionStatuses(): array
    {
        $today = Carbon::today()->toDateString();

        $expiredCount = TenantSubscription::query()
            ->where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->update(['status' => 'expired']);

        $masjids = Masjid::query()->select(['id'])->get();
        $syncedMasjids = 0;

        foreach ($masjids as $masjid) {
            $latest = TenantSubscription::query()
                ->where('masjid_id', $masjid->id)
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'grace' THEN 1 WHEN status = 'expired' THEN 2 ELSE 3 END")
                ->orderByDesc('end_date')
                ->first();

            $masjid->update([
                'subscription_status' => $latest?->status ?? 'none',
                'subscription_expiry' => $latest?->end_date,
            ]);

            $syncedMasjids++;
        }

        return [
            'expired_subscriptions' => $expiredCount,
            'synced_masjids' => $syncedMasjids,
        ];
    }
}
