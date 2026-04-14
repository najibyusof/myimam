<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotifications
{
    public function appNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function unreadAppNotifications(): MorphMany
    {
        return $this->appNotifications()->whereNull('read_at');
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class, 'id_user');
    }

    public function getOrCreateNotificationPreference(): NotificationPreference
    {
        return $this->notificationPreference()->firstOrCreate([
            'id_user' => $this->id,
        ], [
            'email_notifications' => true,
            'push_notifications' => true,
        ]);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->unreadAppNotifications()->count();
    }

    public function markAllNotificationsAsRead(): void
    {
        $this->unreadAppNotifications()->update(['read_at' => now()]);
    }
}
