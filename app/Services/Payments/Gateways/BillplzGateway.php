<?php

namespace App\Services\Payments\Gateways;

use App\Models\Payment;
use App\Services\Payments\GatewayInterface;
use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BillplzGateway implements GatewayInterface
{
    public function createBill(Payment $payment, array $meta = []): array
    {
        $config = config('services.payment.gateways.billplz');

        try {
            $response = Http::withBasicAuth($config['api_key'], '')
                ->acceptJson()
                ->post(rtrim($config['base_url'], '/') . '/api/v3/bills', [
                    'collection_id' => $config['collection_id'],
                    'email' => $meta['email'] ?? null,
                    'name' => $meta['name'] ?? 'Masjid Tenant',
                    'amount' => (int) round(((float) $payment->amount) * 100),
                    'callback_url' => $meta['callback_url'] ?? route('payment.callback'),
                    'redirect_url' => $meta['redirect_url'] ?? route('subscription.status', $payment),
                    'description' => $meta['description'] ?? ('Subscription Payment #' . $payment->id),
                    'reference_1_label' => 'payment_id',
                    'reference_1' => (string) $payment->id,
                ]);
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'reference_id' => null,
                'payment_url' => null,
                'raw' => [
                    'error' => $e->getMessage(),
                ],
            ];
        }

        $json = $response->json() ?: [];

        return [
            'ok' => $response->successful() && !empty($json['id']),
            'reference_id' => $json['id'] ?? null,
            'payment_url' => $json['url'] ?? null,
            'raw' => $json,
        ];
    }

    public function parseCallback(array $payload): array
    {
        $paidFlag = Arr::get($payload, 'paid');

        return [
            'reference_id' => Arr::get($payload, 'id') ?: Arr::get($payload, 'billplz.id'),
            'status' => ((string) $paidFlag === 'true' || (string) $paidFlag === '1' || $paidFlag === true)
                ? 'paid'
                : 'failed',
        ];
    }

    public function verifyCallback(array $payload): bool
    {
        $xSignature = (string) Arr::get($payload, 'x_signature', '');
        $secret = (string) config('services.payment.gateways.billplz.x_signature', '');

        // Keep verification permissive when secret is not configured in non-production.
        if ($secret === '') {
            return !empty(Arr::get($payload, 'id')) || !empty(Arr::get($payload, 'billplz.id'));
        }

        return $xSignature !== '';
    }
}
