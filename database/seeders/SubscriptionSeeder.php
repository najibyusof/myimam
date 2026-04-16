<?php

namespace Database\Seeders;

use App\Models\Masjid;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $superAdmin = User::query()->where('peranan', 'superadmin')->orderBy('id')->first();

        $basicPlan = SubscriptionPlan::query()->updateOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Pelan Asas',
                'price' => 99.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'max_users' => 12,
                    'advanced_reports' => false,
                    'api_access' => false,
                    'cms_customization' => true,
                ],
                'is_active' => true,
                'sort_order' => 10,
            ]
        );

        $premiumPlan = SubscriptionPlan::query()->updateOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Pelan Premium',
                'price' => 249.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'max_users' => 40,
                    'advanced_reports' => true,
                    'api_access' => true,
                    'cms_customization' => true,
                ],
                'is_active' => true,
                'sort_order' => 20,
            ]
        );

        $blueprints = [
            [
                'code' => 'alfalah',
                'plan' => $basicPlan,
                'start_date' => $today->copy()->subMonths(2),
                'end_date' => $today->copy()->addMonths(1),
                'status' => 'active',
                'amount_paid' => 99.00,
                'grace_days' => 7,
            ],
            [
                'code' => 'arrahman',
                'plan' => $premiumPlan,
                'start_date' => $today->copy()->subMonth(),
                'end_date' => $today->copy()->addMonths(2),
                'status' => 'active',
                'amount_paid' => 249.00,
                'grace_days' => 10,
            ],
            [
                'code' => 'annur',
                'plan' => $basicPlan,
                'start_date' => $today->copy()->subMonths(3),
                'end_date' => $today->copy()->subDays(15),
                'status' => 'expired',
                'amount_paid' => 99.00,
                'grace_days' => 7,
            ],
        ];

        foreach ($blueprints as $item) {
            $masjid = Masjid::query()->where('code', $item['code'])->first();
            if (! $masjid) {
                continue;
            }

            TenantSubscription::query()->updateOrCreate(
                [
                    'masjid_id' => $masjid->id,
                    'plan_id' => $item['plan']->id,
                    'start_date' => $item['start_date']->toDateString(),
                ],
                [
                    'end_date' => $item['end_date']->toDateString(),
                    'status' => $item['status'],
                    'grace_days' => $item['grace_days'],
                    'amount_paid' => $item['amount_paid'],
                    'payment_reference' => strtoupper(sprintf(
                        'SUB-%s-%s',
                        $masjid->code,
                        $item['start_date']->format('Ym')
                    )),
                    'notes' => 'Data langganan contoh untuk ujian sewaan masjid.',
                    'created_by' => $superAdmin?->id,
                ]
            );

            $masjid->update([
                'subscription_status' => $item['status'] === 'active' ? 'active' : 'expired',
                'subscription_expiry' => $item['end_date']->copy()->endOfDay(),
                'status' => 'active',
                'created_by' => $masjid->created_by ?? $superAdmin?->id,
            ]);
        }
    }
}
