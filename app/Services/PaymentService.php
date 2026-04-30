<?php

namespace App\Services;

use App\Models\Masjid;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payments\PaymentGatewayManager;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager,
        private InvoiceService $invoiceService
    )
    {
    }

    public function createPayment($tenant, $plan, ?string $gateway = null, array $options = []): array
    {
        /** @var Masjid $tenant */
        /** @var Plan $plan */

        return DB::transaction(function () use ($tenant, $plan, $gateway, $options) {
            Subscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'is_trial' => false,
                'auto_renew' => (bool) ($options['auto_renew'] ?? true),
                'renewal_of_id' => $options['renewal_of_id'] ?? null,
                'start_date' => null,
                'end_date' => null,
            ]);

            $gatewayName = strtolower((string) ($gateway ?: config('services.payment.default_gateway', 'billplz')));

            $payment = Payment::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'status' => 'pending',
                'gateway' => $gatewayName,
                'reference_id' => null,
                'payload' => null,
            ]);

            $gateway = $this->gatewayManager->driver($gatewayName);
            $result = $gateway->createBill($payment, [
                'name' => $tenant->nama,
                'email' => $options['payer_email'] ?? ('tenant' . $tenant->id . '@myimam.local'),
                'phone' => $options['payer_phone'] ?? null,
                'description' => "Langganan {$plan->name}",
                'callback_url' => route('payment.callback'),
                'redirect_url' => route('subscription.status', $payment),
            ]);

            $payment->update([
                'reference_id' => $result['reference_id'] ?? null,
                'payload' => $result['raw'] ?? null,
            ]);

            if (!($result['ok'] ?? false) || empty($result['payment_url'])) {
                $gatewayError = $this->extractGatewayErrorMessage($result['raw'] ?? null);
                $payment->update(['status' => 'failed']);
                $subscription->update(['status' => 'expired']);

                return [
                    'success' => false,
                    'payment' => $payment->fresh(),
                    'subscription' => $subscription->fresh(),
                    'payment_url' => null,
                    'message' => $gatewayError ?: 'Gateway failed to generate payment url.',
                ];
            }

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'subscription' => $subscription->fresh(),
                'payment_url' => $result['payment_url'],
                'message' => null,
            ];
        });
    }

    public function startTrial(Masjid $tenant, Plan $plan, int $trialDays = 7): array
    {
        $hasAnySubscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->exists();

        if ($hasAnySubscription) {
            return [
                'success' => false,
                'message' => 'Percubaan hanya tersedia untuk tenant baru.',
            ];
        }

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($trialDays);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'is_trial' => true,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'trial_ends_at' => $endDate,
            'auto_renew' => false,
        ]);

        $tenant->update([
            'subscription_status' => 'active',
            'subscription_expiry' => $endDate,
        ]);

        return [
            'success' => true,
            'subscription' => $subscription,
            'message' => null,
        ];
    }

    public function handleCallback($data): array
    {
        $payload = (array) $data;

        $gatewayName = $this->detectGateway($payload);

        $gateway = $this->gatewayManager->driver($gatewayName);

        if (!$gateway->verifyCallback($payload)) {
            return ['success' => false, 'message' => 'Invalid callback signature.'];
        }

        $parsed = $gateway->parseCallback($payload);
        $referenceId = $parsed['reference_id'] ?? null;
        $status = $parsed['status'] ?? 'failed';

        if (!$referenceId) {
            return ['success' => false, 'message' => 'Reference id missing from callback payload.'];
        }

        /** @var Payment|null $payment */
        $payment = null;
        $paymentId = Arr::get($payload, 'payment_id');

        if ($paymentId) {
            $payment = Payment::query()->find((int) $paymentId);
        }

        if (!$payment && $referenceId) {
            $payment = Payment::query()
                ->where('gateway', $gatewayName)
                ->where('reference_id', $referenceId)
                ->latest('id')
                ->first();
        }

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found.'];
        }

        return DB::transaction(function () use ($payment, $status, $payload, $referenceId) {
            $normalizedStatus = in_array($status, ['paid', 'pending', 'failed'], true)
                ? $status
                : 'failed';

            $payment->update([
                'status' => $normalizedStatus,
                'reference_id' => $referenceId ?: $payment->reference_id,
                'payload' => $payload,
            ]);

            /** @var Subscription $subscription */
            $subscription = $payment->subscription()->with('plan', 'tenant')->firstOrFail();

            if ($payment->status === 'paid') {
                $startDate = Carbon::now();
                $endDate = Carbon::now()->addDays((int) $subscription->plan->duration_days);

                Subscription::query()
                    ->where('tenant_id', $subscription->tenant_id)
                    ->where('status', 'active')
                    ->where('id', '!=', $subscription->id)
                    ->update(['status' => 'expired']);

                $subscription->update([
                    'status' => 'active',
                    'is_trial' => false,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'trial_ends_at' => null,
                ]);

                $subscription->tenant()->update([
                    'subscription_status' => 'active',
                    'subscription_expiry' => $endDate,
                ]);

                $this->invoiceService->generateForPayment($payment);
            } elseif ($payment->status === 'failed') {
                $subscription->update(['status' => 'expired']);
            }

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'subscription' => $subscription->fresh(),
                'message' => null,
            ];
        });
    }

    private function detectGateway(array $payload): string
    {
        if (
            Arr::has($payload, 'billcode')
            || Arr::has($payload, 'billCode')
            || Arr::has($payload, 'status_id')
        ) {
            return 'toyyibpay';
        }

        if (
            Arr::has($payload, 'billplz.id')
            || Arr::has($payload, 'id')
            || Arr::has($payload, 'x_signature')
        ) {
            return 'billplz';
        }

        return strtolower((string) Arr::get(
            $payload,
            'gateway',
            config('services.payment.default_gateway', 'billplz')
        ));
    }

    private function extractGatewayErrorMessage(mixed $raw): ?string
    {
        if (!is_array($raw)) {
            return null;
        }

        $message = (string) (
            $raw['error']
            ?? $raw['msg']
            ?? $raw['message']
            ?? $raw[0]['msg']
            ?? $raw[0]['message']
            ?? ''
        );

        if ($message === '') {
            return null;
        }

        if (str_contains(strtolower($message), 'ssl certificate')) {
            return 'Payment gateway SSL validation failed on this server. For local environment, set TOYYIBPAY_VERIFY_SSL=false in .env and run php artisan config:clear.';
        }

        return 'Payment gateway error: ' . $message;
    }
}
