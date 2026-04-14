<?php

namespace App\Notifications;

abstract class BaseNotification
{
    /**
     * Notification channels to dispatch to
     * Override in subclass to customize
     */
    public function getChannels(): array
    {
        return ['database', 'email', 'telegram', 'fcm'];
    }

    /**
     * Transform notification for database storage
     */
    public function toDatabase(object $notifiable): array
    {
        return [];
    }

    /**
     * Transform notification for email
     */
    public function toMail(object $notifiable): ?object
    {
        return null;
    }

    /**
     * Transform notification for Telegram
     */
    public function toTelegram(object $notifiable): ?string
    {
        return null;
    }

    /**
     * Transform notification for Firebase Cloud Messaging
     */
    public function toFCM(object $notifiable): ?array
    {
        return null;
    }
}
