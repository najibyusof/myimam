<?php

namespace App\Notifications\Channels;

use Throwable;

class DatabaseChannel extends Channel
{
    public function send(object $notifiable, object $notification): bool
    {
        try {
            if (!method_exists($notification, 'toDatabase')) {
                return false;
            }

            $data = $notification->toDatabase($notifiable);

            $notifiable->appNotifications()->create([
                'type' => $notification::class,
                'data' => $data,
            ]);

            $this->logSuccess();

            return true;
        } catch (Throwable $e) {
            $this->logFailure($e->getMessage());

            return false;
        }
    }
}
