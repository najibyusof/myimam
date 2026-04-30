<?php

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionLifecycleService
{
    public function __construct(
        private PaymentService $paymentService,
        private WhatsAppService $whatsAppService
    ) {
    }

    public function processAutoRenewals(): array
    {
        $renewed = 0;
        $failed = 0;

        $subscriptions = Subscription::query()
            ->with(['tenant', 'plan', 'payments'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<=', Carbon::now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $existingPending = Subscription::query()
                ->where('tenant_id', $subscription->tenant_id)
                ->where('plan_id', $subscription->plan_id)
                ->where('status', 'pending')
                ->exists();

            if ($existingPending) {
                continue;
            }

            $gateway = $subscription->payments()->latest('id')->value('gateway');

            $result = $this->paymentService->createPayment(
                $subscription->tenant,
                $subscription->plan,
                $gateway,
                [
                    'auto_renew' => true,
                    'renewal_of_id' => $subscription->id,
                ]
            );

            if ($result['success'] ?? false) {
                $renewed++;
            } else {
                $failed++;
            }
        }

        return [
            'renewed' => $renewed,
            'failed' => $failed,
        ];
    }

    public function sendExpiryReminders(int $daysBefore = 3): array
    {
        $sent = 0;
        $skipped = 0;
        $from = Carbon::now();
        $to = Carbon::now()->addDays($daysBefore);

        $subscriptions = Subscription::query()
            ->with(['tenant', 'plan'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$from, $to])
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($subscriptions as $subscription) {
            $recipient = $subscription->tenant?->whatsapp_no
                ?: config('services.whatsapp.fallback_to');

            if (!$recipient) {
                $skipped++;
                continue;
            }

            $message = sprintf(
                "Peringatan langganan: %s akan tamat pada %s. Sila renew di %s",
                $subscription->tenant?->nama ?? 'Tenant',
                $subscription->end_date?->format('d M Y H:i') ?? '-',
                rtrim((string) config('app.url'), '/') . '/subscription'
            );

            if ($this->whatsAppService->send($recipient, $message)) {
                $subscription->update(['reminder_sent_at' => Carbon::now()]);
                $sent++;
            } else {
                $skipped++;
            }
        }

        return [
            'sent' => $sent,
            'skipped' => $skipped,
        ];
    }
}
