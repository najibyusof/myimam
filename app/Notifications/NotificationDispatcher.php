<?php

namespace App\Notifications;

use App\Models\NotificationLog;
use App\Notifications\Channels\DatabaseChannel;
use App\Notifications\Channels\EmailChannel;
use App\Notifications\Channels\FCMChannel;
use App\Notifications\Channels\TelegramChannel;
use Throwable;

class NotificationDispatcher
{
    protected array $channels = [
        'database' => DatabaseChannel::class,
        'email' => EmailChannel::class,
        'telegram' => TelegramChannel::class,
        'fcm' => FCMChannel::class,
    ];

    /**
     * Dispatch notification to specified channels
     */
    public function dispatch(object $notifiable, BaseNotification $notification, ?array $channelsOverride = null): bool
    {
        $channels = $channelsOverride ?? $notification->getChannels();
        $success = false;

        // Check user preferences
        if (method_exists($notifiable, 'getOrCreateNotificationPreference')) {
            $preference = $notifiable->getOrCreateNotificationPreference();

            $channels = array_filter($channels, function ($channel) use ($preference) {
                return $preference->isChannelEnabled($channel);
            });
        }

        foreach ($channels as $channelName) {
            try {
                $channelClass = $this->channels[$channelName] ?? null;

                if (!$channelClass) {
                    continue;
                }

                // Create notification log entry
                $log = NotificationLog::create([
                    'channel' => $channelName,
                    'notifiable_type' => $notifiable::class,
                    'notifiable_id' => $notifiable->id,
                    'subject' => $notification->getSubject(),
                    'message' => $notification->getMessage(),
                    'status' => 'pending',
                ]);

                // Send through channel
                $channel = new $channelClass();
                $channel->setLog($log);

                if ($channel->send($notifiable, $notification)) {
                    $success = true;
                }
            } catch (Throwable $e) {
                \Log::error("Notification dispatch failed for channel {$channelName}", [
                    'error' => $e->getMessage(),
                    'notifiable' => $notifiable::class . ':' . $notifiable->id,
                ]);
            }
        }

        return $success;
    }

    /**
     * Get available channels
     */
    public function getAvailableChannels(): array
    {
        return array_keys($this->channels);
    }
}
