<?php

namespace App\Console\Commands\Notifications;

use App\Models\NotificationLog;
use Illuminate\Console\Command;

class RetryFailedNotificationsCommand extends Command
{
    protected $signature = 'notifications:retry-failed {--channel= : Specific channel to retry} {--limit=10 : Number of failed notifications to retry}';

    protected $description = 'Retry sending failed notifications';

    public function handle(): int
    {
        $channel = $this->option('channel');
        $limit = (int) $this->option('limit');

        $query = NotificationLog::where('status', 'failed')
            ->where('retry_count', '<', config('notifications.max_retry_attempts', 3));

        if ($channel) {
            $query->where('channel', $channel);
        }

        $logs = $query->limit($limit)->get();

        if ($logs->isEmpty()) {
            $this->info('No failed notifications to retry.');

            return 0;
        }

        $this->withProgressBar($logs, function ($log) {
            try {
                $notifiable = $log->notifiable;

                if (!$notifiable) {
                    $log->markAsFailed('Notifiable not found');

                    return;
                }

                // Retry sending based on channel
                match ($log->channel) {
                    'email' => \Mail::to($notifiable->email)->send(new \App\Mail\RetryNotification($log)),
                    'telegram' => $this->retryTelegram($log, $notifiable),
                    'fcm' => $this->retryFCM($log, $notifiable),
                    default => null,
                };
            } catch (\Throwable $e) {
                $log->markAsFailed($e->getMessage());
            }
        });

        $this->newLine();
        $this->info('Notification retry completed.');

        return 0;
    }

    private function retryTelegram(NotificationLog $log, object $notifiable): void
    {
        $preference = $notifiable->getOrCreateNotificationPreference();

        if (!$preference->telegram_chat_id) {
            $log->markAsFailed('No Telegram chat ID');

            return;
        }

        // Implement retry logic
        $log->markAsSent();
    }

    private function retryFCM(NotificationLog $log, object $notifiable): void
    {
        $preference = $notifiable->getOrCreateNotificationPreference();

        if (!$preference->fcm_token) {
            $log->markAsFailed('No FCM token');

            return;
        }

        // Implement retry logic
        $log->markAsSent();
    }
}
