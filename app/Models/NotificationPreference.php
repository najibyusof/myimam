<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'id_user',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'telegram_notifications',
        'telegram_chat_id',
        'fcm_token',
        'notification_types',
    ];

    protected function casts(): array
    {
        return [
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'telegram_notifications' => 'boolean',
            'notification_types' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function isChannelEnabled(string $channel): bool
    {
        return match ($channel) {
            'email' => $this->email_notifications,
            'sms' => $this->sms_notifications,
            'push' => $this->push_notifications && !is_null($this->fcm_token),
            'telegram' => $this->telegram_notifications && !is_null($this->telegram_chat_id),
            default => false,
        };
    }

    public function isNotificationTypeEnabled(string $type): bool
    {
        if (empty($this->notification_types)) {
            return true;
        }

        return in_array($type, $this->notification_types);
    }
}
