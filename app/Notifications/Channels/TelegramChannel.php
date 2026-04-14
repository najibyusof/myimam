<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Throwable;

class TelegramChannel extends Channel
{
    public function send(object $notifiable, object $notification): bool
    {
        try {
            if (!method_exists($notification, 'toTelegram')) {
                return false;
            }

            $preference = $notifiable->getOrCreateNotificationPreference();

            if (!$preference->isChannelEnabled('telegram')) {
                return false;
            }

            $message = $notification->toTelegram($notifiable);
            $chatId = $preference->telegram_chat_id;
            $botToken = config('services.telegram.bot_token');

            if (!$chatId || !$botToken) {
                throw new \Exception('Telegram chat ID or bot token not configured');
            }

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->failed()) {
                throw new \Exception('Telegram API error: ' . $response->body());
            }

            $this->logSuccess();

            return true;
        } catch (Throwable $e) {
            $this->logFailure($e->getMessage());

            return false;
        }
    }
}
