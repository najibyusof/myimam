# Notification System Architecture

## Overview

A unified, multi-channel notification system supporting:

- **Database Notifications** - In-app notifications
- **Email Notifications** - Queue-based email delivery
- **Telegram Notifications** - Real-time messaging via Telegram Bot API
- **Push Notifications** - Firebase Cloud Messaging (FCM)

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Notification Event                        │
└────────┬────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│              NotificationService (Dispatcher)               │
│  - Send to single/multiple users                           │
│  - Channel selection                                        │
│  - Preference checking                                      │
└────────┬────────────────────────────────────────────────────┘
         │
    ┌────┴────┬───────────┬──────────┐
    │          │           │          │
    ▼          ▼           ▼          ▼
┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐
│Database│ │ Email  │ │Telegram│ │  FCM   │
│Channel │ │Channel │ │Channel │ │Channel │
└────┬───┘ └───┬────┘ └───┬────┘ └───┬────┘
     │         │          │          │
     ▼         ▼          ▼          ▼
  [DB]     [Queue]   [Telegram]  [Firebase]
         │ API    │   API         API
         └─────────────────────────────────┘
```

## System Components

### 1. Models

#### Notification

Stores in-app notifications with read/unread status.

```php
- id: UUID
- type: string (notification class name)
- notifiable_type: string (morphs)
- notifiable_id: integer (morphs)
- data: JSON (notification content)
- read_at: timestamp
- created_at, updated_at
```

#### NotificationPreference

User preferences for notification channels and types.

```php
- id_user: FK
- email_notifications: boolean
- sms_notifications: boolean
- push_notifications: boolean
- telegram_notifications: boolean
- telegram_chat_id: string
- fcm_token: string
- notification_types: JSON (allowed types)
```

#### NotificationLog

Audit trail for all notification sends.

```php
- id: integer
- notification_id: UUID
- channel: string (email|telegram|fcm|database)
- notifiable_type, notifiable_id: morphs
- subject, message: text
- status: string (pending|sent|failed)
- error_message: nullable string
- retry_count: integer
- sent_at: timestamp
```

### 2. Service Layer

#### NotificationService

Main service for sending and managing notifications.

```php
send(notifiable, notification, channels)
  - Send to single user/multiple users
  - Option to override channels
  - Returns success boolean

getUnreadNotifications(user, limit)
  - Fetch unread in-app notifications

getNotifications(user, limit)
  - Fetch all notifications

markAsRead(notificationId)
  - Mark single notification as read

markAllAsRead(user)
  - Mark all user notifications as read

getNotificationLogs(channel, status, limit)
  - Retrieve audit logs

getStatistics(from, to)
  - Get delivery stats
  - By channel breakdown
  - Success/failure rates
```

### 3. Notification Channels

#### DatabaseChannel

Stores notification in database.

```php
Methods:
- send(notifiable, notification): bool
- Calls notification->toDatabase()
- Creates Notification model record
```

#### EmailChannel

Sends email via queue.

```php
Methods:
- send(notifiable, notification): bool
- Calls notification->toMail()
- Queues email via Mail::queue()
- Supports custom Mail objects
```

#### TelegramChannel

Sends message via Telegram Bot API.

```php
Methods:
- send(notifiable, notification): bool
- Checks user preference for enabled + chat_id
- Calls notification->toTelegram()
- Uses HTTP client to call Telegram API
- Supports HTML formatting
```

#### FCMChannel

Sends push notification via Firebase.

```php
Methods:
- send(notifiable, notification): bool
- Checks user preference for enabled + token
- Calls notification->toFCM()
- Uses HTTP client to call FCM API
- Supports data payload + priority
```

### 4. Base Notification Class

```php
BaseNotification
├── getChannels(): array
│   - Which channels to dispatch through
│   - Default: ['database', 'email', 'telegram', 'fcm']
│
├── toDatabase(notifiable): array
│   - In-app notification content
│
├── toMail(notifiable): Mailable
│   - Email message object
│
├── toTelegram(notifiable): string
│   - Telegram message (supports HTML)
│
└── toFCM(notifiable): array
    - Push notification data
    - title, body, icon, data, priority
```

### 5. Trait: HasNotifications

Add to any model requiring notifications.

```php
trait HasNotifications
├── notifications(): HasMany
├── unreadNotifications(): HasMany
├── notificationPreference(): HasOne
├── getOrCreateNotificationPreference(): NotificationPreference
├── unreadNotificationsCount(): int
└── markAllNotificationsAsRead(): void
```

## Configuration Files

### config/notifications.php

```php
- enabled_channels: array
- retry_failed_notifications: bool
- max_retry_attempts: int
- email.queue, email.retry_after
- telegram.rate_limit
- fcm.priority, fcm.time_to_live
- database.retention_days
```

### config/services.php (additions)

```php
'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
],

'fcm' => [
    'server_key' => env('FCM_SERVER_KEY'),
    'sender_id' => env('FCM_SENDER_ID'),
],
```

### .env

```
TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/webhook/telegram
FCM_SERVER_KEY=your_fcm_server_key_here
FCM_SENDER_ID=your_fcm_sender_id_here
```

## Database Migrations

All migrations are versioned with timestamps:

- `2026_04_14_120000_create_notifications_table.php`
- `2026_04_14_120010_create_notification_preferences_table.php`
- `2026_04_14_120020_create_notification_logs_table.php`

Run migrations:

```bash
php artisan migrate
```

## API Endpoints

All endpoints require authentication.

### GET /api/notifications

Get user's notifications

Query parameters:

- `unread=true|false` - Filter unread only
- `limit=50` - Pagination limit

Response:

```json
{
    "data": [...notifications],
    "unread_count": 5,
    "total_count": 50
}
```

### POST /api/notifications/{id}/read

Mark specific notification as read

### POST /api/notifications/read-all

Mark all notifications as read

### GET /api/notifications/preferences

Get user's notification preferences

### POST /api/notifications/preferences

Update notification preferences

Request body:

```json
{
    "email_notifications": true,
    "push_notifications": true,
    "telegram_notifications": false,
    "fcm_token": "device_token_here",
    "telegram_chat_id": "123456789",
    "notification_types": ["finance", "approval"]
}
```

### GET /api/notifications/statistics (admin)

Get system-wide notification statistics

## Console Commands

### Retry Failed Notifications

```bash
php artisan notifications:retry-failed
php artisan notifications:retry-failed --channel=email
php artisan notifications:retry-failed --limit=10
```

### Cleanup Old Notifications

```bash
php artisan notifications:cleanup
php artisan notifications:cleanup --days=30
```

## Usage Examples

See separate USAGE.md file for detailed examples of:

- Creating custom notifications
- Sending notifications
- Handling preferences
- Error handling
- Testing
