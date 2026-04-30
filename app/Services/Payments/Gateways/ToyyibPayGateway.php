<?php

namespace App\Services\Payments\Gateways;

use App\Models\Payment;
use App\Services\Payments\GatewayInterface;
use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ToyyibPayGateway implements GatewayInterface
{
    public function createBill(Payment $payment, array $meta = []): array
    {
        $config = config('services.payment.gateways.toyyibpay');
        $callbackToken = (string) ($config['callback_token'] ?? '');
        $verifySsl = (bool) ($config['verify_ssl'] ?? true);

        $callbackParams = [
            'gateway' => 'toyyibpay',
            'payment_id' => $payment->id,
        ];

        if ($callbackToken !== '') {
            $callbackParams['token'] = $callbackToken;
        }

        $callbackUrl = $this->appendQueryParams(
            $meta['callback_url'] ?? route('payment.callback'),
            $callbackParams
        );

        $email = trim((string) ($meta['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($meta['phone'] ?? '')) ?? '';
        $hasCompletePayorInfo = $email !== '' && $phone !== '';
        $payorInfoFlag = $hasCompletePayorInfo ? 1 : 0;
        $billName = $this->sanitizeText((string) ($meta['description'] ?? ('Subscription Payment ' . $payment->id)), 30);
        $billDescription = $this->sanitizeText((string) ($meta['description'] ?? ('Subscription Payment ' . $payment->id)), 100);

        try {
            $client = Http::asForm();

            if (!$verifySsl) {
                $client = $client->withoutVerifying();
            }

            $payload = [
                'userSecretKey' => $config['secret_key'],
                'categoryCode' => $config['category_code'],
                'billName' => $billName !== '' ? $billName : ('Subscription_' . $payment->id),
                'billDescription' => $billDescription !== '' ? $billDescription : ('Subscription_' . $payment->id),
                'billPriceSetting' => 1,
                'billPayorInfo' => $payorInfoFlag,
                'billAmount' => (int) round(((float) $payment->amount) * 100),
                'billReturnUrl' => $meta['redirect_url'] ?? route('subscription.status', $payment),
                'billCallbackUrl' => $callbackUrl,
                'billExternalReferenceNo' => (string) $payment->id,
                'billTo' => $meta['name'] ?? 'Masjid Tenant',
            ];

            if ($hasCompletePayorInfo) {
                $payload['billEmail'] = $email;
                $payload['billPhone'] = $phone;
            }

            $response = $client->post(rtrim($config['base_url'], '/') . '/index.php/api/createBill', $payload);
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
        $first = is_array($json) && isset($json[0]) ? $json[0] : [];
        $billCode = $first['BillCode'] ?? $first['billCode'] ?? null;

        if (!$response->successful() && empty($json)) {
            $json = [
                'status' => 'error',
                'http_status' => $response->status(),
                'body' => $response->body(),
            ];
        }

        return [
            'ok' => $response->successful() && !empty($billCode),
            'reference_id' => $billCode,
            'payment_url' => $billCode ? rtrim($config['base_url'], '/') . '/' . $billCode : null,
            'raw' => $json,
        ];
    }

    public function parseCallback(array $payload): array
    {
        $statusId = (string) (
            Arr::get($payload, 'status_id')
            ?? Arr::get($payload, 'status')
            ?? Arr::get($payload, 'billpaymentStatus')
            ?? ''
        );

        $mappedStatus = match ($statusId) {
            '1' => 'paid',
            '2' => 'pending',
            '3' => 'failed',
            default => 'failed',
        };

        return [
            'reference_id' => Arr::get($payload, 'billcode')
                ?: Arr::get($payload, 'billCode')
                ?: Arr::get($payload, 'bill_code'),
            'status' => $mappedStatus,
        ];
    }

    public function verifyCallback(array $payload): bool
    {
        $hasPaymentReference = !empty(Arr::get($payload, 'billcode'))
            || !empty(Arr::get($payload, 'billCode'))
            || !empty(Arr::get($payload, 'payment_id'));

        if (!$hasPaymentReference) {
            return false;
        }

        // Official ToyyibPay callback hash verification.
        $secretKey = (string) config('services.payment.gateways.toyyibpay.secret_key', '');
        $status = (string) (Arr::get($payload, 'status') ?? Arr::get($payload, 'status_id') ?? '');
        $orderId = (string) (Arr::get($payload, 'order_id') ?? '');
        $refNo = (string) (Arr::get($payload, 'refno') ?? '');
        $receivedHash = (string) (Arr::get($payload, 'hash') ?? '');

        if ($secretKey !== '' && $status !== '' && $orderId !== '' && $refNo !== '' && $receivedHash !== '') {
            $expectedHash = md5($secretKey . $status . $orderId . $refNo . 'ok');

            return hash_equals(strtolower($expectedHash), strtolower($receivedHash));
        }

        $expectedToken = (string) config('services.payment.gateways.toyyibpay.callback_token', '');

        if ($expectedToken === '') {
            return false;
        }

        $providedToken = (string) Arr::get($payload, 'token', '');

        return $providedToken !== '' && hash_equals($expectedToken, $providedToken);
    }

    private function sanitizeText(string $value, int $maxLength): string
    {
        $value = preg_replace('/[^A-Za-z0-9_ ]/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        if ($value === '') {
            return '';
        }

        return mb_substr($value, 0, $maxLength);
    }

    private function appendQueryParams(string $url, array $params): string
    {
        $parts = parse_url($url);
        $query = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query = array_merge($query, $params);

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $host . $port . $path . '?' . http_build_query($query) . $fragment;
    }
}
