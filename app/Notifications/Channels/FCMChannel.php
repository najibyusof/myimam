<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Throwable;

class FCMChannel extends Channel
{
    public function send(object $notifiable, object $notification): bool
    {
        try {
            if (!method_exists($notification, 'toFCM')) {
                return false;
            }

            $preference = $notifiable->getOrCreateNotificationPreference();

            if (!$preference->isChannelEnabled('push')) {
                return false;
            }

            $fcmToken = $preference->fcm_token;
            $serverKey = config('services.fcm.server_key');

            if (!$fcmToken || !$serverKey) {
                throw new \Exception('FCM token or server key not configured');
            }

            $payload = $notification->toFCM($notifiable);

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $payload['title'] ?? 'Notification',
                    'body' => $payload['body'] ?? '',
                    'icon' => $payload['icon'] ?? 'icon_0',
                ],
                'data' => $payload['data'] ?? [],
                'priority' => $payload['priority'] ?? 'high',
            ]);

            if ($response->failed()) {
                throw new \Exception('FCM API error: ' . $response->body());
            }

            $this->logSuccess();

            return true;
        } catch (Throwable $e) {
            $this->logFailure($e->getMessage());

            return false;
        }
    }
}
