<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function send(string $to, string $message): bool
    {
        $config = config('services.whatsapp');

        if (empty($config['access_token']) || empty($config['phone_number_id'])) {
            Log::warning('WhatsApp credentials not configured. Reminder skipped.', ['to' => $to]);
            return false;
        }

        $endpoint = rtrim((string) ($config['base_url'] ?? 'https://graph.facebook.com'), '/')
            . '/v18.0/' . $config['phone_number_id'] . '/messages';

        $response = Http::withToken($config['access_token'])
            ->acceptJson()
            ->post($endpoint, [
                'messaging_product' => 'whatsapp',
                'to' => $this->normalizePhone($to),
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message,
                ],
            ]);

        if (!$response->successful()) {
            Log::error('WhatsApp send failed', [
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        }

        return $response->successful();
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', trim($phone)) ?? '';

        if (str_starts_with($phone, '+')) {
            return ltrim($phone, '+');
        }

        if (str_starts_with($phone, '0')) {
            return '6' . $phone;
        }

        return $phone;
    }
}
