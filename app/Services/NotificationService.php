<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Notifications\BaseNotification;
use App\Notifications\NotificationDispatcher;

class NotificationService
{
    public function __construct(private NotificationDispatcher $dispatcher)
    {
    }

    /**
     * Send notification to a user or multiple users
     */
    public function send(object|array $notifiable, BaseNotification $notification, ?array $channels = null): bool
    {
        if (is_array($notifiable)) {
            $success = true;

            foreach ($notifiable as $recipient) {
                if (!$this->dispatcher->dispatch($recipient, $notification, $channels)) {
                    $success = false;
                }
            }

            return $success;
        }

        return $this->dispatcher->dispatch($notifiable, $notification, $channels);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(object $user, int $limit = 10)
    {
        return $user->unreadAppNotifications()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user
     */
    public function getNotifications(object $user, int $limit = 50)
    {
        return $user->appNotifications()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $notificationId): bool
    {
        $notification = \App\Models\Notification::find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(object $user): bool
    {
        $user->markAllNotificationsAsRead();

        return true;
    }

    /**
     * Get notification logs for analytics
     */
    public function getNotificationLogs(string $channel = null, string $status = null, int $limit = 100)
    {
        $query = NotificationLog::query();

        if ($channel) {
            $query->where('channel', $channel);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notification delivery statistics
     */
    public function getStatistics(?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $query = NotificationLog::query();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $total = $query->count();
        $sent = $query->clone()->where('status', 'sent')->count();
        $failed = $query->clone()->where('status', 'failed')->count();
        $pending = $query->clone()->where('status', 'pending')->count();

        $byChannel = $query->clone()->groupBy('channel')
            ->selectRaw('channel, count(*) as total, sum(case when status = "sent" then 1 else 0 end) as sent')
            ->get();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'delivery_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
            'by_channel' => $byChannel,
        ];
    }
}
