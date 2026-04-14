# Unified Notification System - Implementation Summary

## System Overview

A production-ready, multi-channel notification system deployed through a unified dispatcher with support for:

1. **Database Notifications** - In-app notification center
2. **Email Notifications** - Queue-based with retry logic
3. **Telegram Notifications** - Telegram Bot API integration with HTML formatting
4. **Push Notifications** - Firebase Cloud Messaging (FCM) for mobile/web

---

## Architecture Components

### Database Schema

**3 Core Tables:**

1. **notifications** (UUID primary key)
    - Stores in-app notifications with read/unread status
    - Stores full notification data as JSON
    - Indexed by notifiable model + creation date

2. **notification_preferences**
    - Per-user channel preferences
    - Stores: email, sms, push, telegram toggles
    - Stores Telegram chat_id, FCM token
    - Stores allowed notification types

3. **notification_logs**
    - Audit trail for every send attempt
    - Tracks: channel, status (pending/sent/failed), retry count
    - Enables monitoring and retry logic

### Service Architecture

```
NotificationService (main interface)
    ↓
NotificationDispatcher (channel selector)
    ↓
4 Channels (parallel execution):
├── DatabaseChannel     → App\Models\Notification
├── EmailChannel        → Mail Queue
├── TelegramChannel     → Telegram Bot API
└── FCMChannel          → Firebase API
    ↓
NotificationLog (create entry & track)
```

### Key Classes

**BaseNotification** (abstract)

- Define channels: `getChannels()`
- Transform for each channel:
    - `toDatabase()` → Array
    - `toMail()` → Mailable object
    - `toTelegram()` → HTML string
    - `toFCM()` → Array with title/body/data

**NotificationDispatcher**

- Route to enabled channels
- Check user preferences
- Create audit logs
- Handle failures gracefully

**NotificationService**

- Main API for sending
- Get/mark notifications
- Statistics & analytics
- Batch operations

---

## Implementation Files

### Models (3 files)

- `app/Models/Notification.php` - In-app notifications
- `app/Models/NotificationPreference.php` - User preferences
- `app/Models/NotificationLog.php` - Audit trail

### Services (2 files)

- `app/Services/NotificationService.php` - Primary interface
- `app/Notifications/NotificationDispatcher.php` - Channel router

### Notification Channels (5 files)

- `app/Notifications/Channels/Channel.php` - Base class
- `app/Notifications/Channels/DatabaseChannel.php`
- `app/Notifications/Channels/EmailChannel.php`
- `app/Notifications/Channels/TelegramChannel.php`
- `app/Notifications/Channels/FCMChannel.php`

### Base Notification (1 file)

- `app/Notifications/BaseNotification.php` - Abstract base

### Sample Notifications (3 files)

- `app/Notifications/FinanceNotification.php` - Income/expense/transfer
- `app/Notifications/ApprovalNotification.php` - Workflow approvals
- `app/Notifications/Examples/AccountNotification.php` - Account events

### Trait (1 file)

- `app/Traits/HasNotifications.php` - Add to models (already added to User)

### Provider (1 file)

- `app/Providers/NotificationServiceProvider.php` - DI registration

### Controllers (1 file)

- `app/Http/Controllers/Api/NotificationController.php` - API endpoints

### Console Commands (2 files)

- `app/Console/Commands/Notifications/RetryFailedNotificationsCommand.php`
- `app/Console/Commands/Notifications/CleanupOldNotificationsCommand.php`

### Migrations (3 files)

- `database/migrations/*_create_notifications_table.php`
- `database/migrations/*_create_notification_preferences_table.php`
- `database/migrations/*_create_notification_logs_table.php`

### Configuration (2 files updated)

- `config/notifications.php` - Feature toggles & settings
- `config/services.php` - Telegram & FCM credentials

### Documentation (2 files)

- `docs/NOTIFICATION_ARCHITECTURE.md` - Technical design
- `docs/NOTIFICATION_USAGE.md` - Implementation guide

---

## Setup Instructions

### 1. Environment Configuration

Add to `.env`:

```
TELEGRAM_BOT_TOKEN=your_token_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/webhook/telegram
FCM_SERVER_KEY=your_server_key_here
FCM_SENDER_ID=your_sender_id_here
```

### 2. Run Migrations

```bash
php artisan migrate
```

Adds 3 tables with proper indexes and foreign keys.

### 3. Service Provider Registration

Already added to `bootstrap/providers.php`:

```php
App\Providers\NotificationServiceProvider::class,
```

### 4. Update User Model

Already updated to include:

```php
use App\Traits\HasNotifications;
```

---

## Usage Examples

### Send Single Notification

```php
use App\Notifications\FinanceNotification;
use App\Services\NotificationService;

$service = app(NotificationService::class);

$user = User::find(1);

$notification = new FinanceNotification(
    type: 'expense',
    amount: 'RM 500.00',
    description: 'Pembelian dapur',
    details: ['category' => 'Supplies']
);

$service->send($user, $notification);
```

### Send to Multiple Users

```php
$managers = User::role('Manager')->get();

$service->send($managers, $notification);
```

### Send Specific Channels

```php
$service->send($user, $notification, ['database', 'email']);
```

### Get User Notifications

```php
$unread = $service->getUnreadNotifications($user);
$all = $service->getNotifications($user, limit: 50);
```

### Manage Preferences

```php
$preference = $user->getOrCreateNotificationPreference();

$preference->update([
    'email_notifications' => true,
    'telegram_notifications' => true,
    'telegram_chat_id' => '123456789',
    'fcm_token' => 'device_token_here',
]);
```

---

## API Endpoints

All endpoints require authentication (`/api/notifications`):

| Method | Endpoint                         | Purpose                                     |
| ------ | -------------------------------- | ------------------------------------------- |
| GET    | `/api/notifications`             | Get notifications (unread filter available) |
| POST   | `/api/notifications/{id}/read`   | Mark as read                                |
| POST   | `/api/notifications/read-all`    | Mark all as read                            |
| GET    | `/api/notifications/preferences` | Get preferences                             |
| POST   | `/api/notifications/preferences` | Update preferences                          |
| GET    | `/api/notifications/statistics`  | Stats (admin)                               |

---

## Console Commands

```bash
# Retry failed notifications (max 3 retry attempts)
php artisan notifications:retry-failed

# Retry specific channel
php artisan notifications:retry-failed --channel=email

# Retry limited number
php artisan notifications:retry-failed --limit=10

# Cleanup old notifications (30 days default)
php artisan notifications:cleanup

# Cleanup older than specific days
php artisan notifications:cleanup --days=7
```

---

## Channel Specifications

### 1. Database Channel

- **Speed**: Instant
- **Reliability**: 100% (on DB success)
- **Use Case**: All events
- **Transform Method**: `toDatabase()` returns array

### 2. Email Channel

- **Speed**: Queued (async)
- **Reliability**: High (with retry)
- **Use Case**: Important notifications
- **Transform Method**: `toMail()` returns Mailable
- **Configuration**: `config/mail.php`

### 3. Telegram Channel

- **Speed**: Near-instant (API call)
- **Reliability**: High (rate-limited)
- **Use Case**: Urgent alerts, approvals
- **Transform Method**: `toTelegram()` returns HTML string
- **Configuration**:
    - Telegram bot token
    - User chat_id (from webhook `/start` flow)

### 4. FCM Channel

- **Speed**: Near-instant (API call)
- **Reliability**: High
- **Use Case**: Mobile push notifications
- **Transform Method**: `toFCM()` returns array
- **Configuration**:
    - Server key
    - Device token (from client)

---

## Monitoring & Debugging

### View Notification Logs

```php
$logs = \App\Models\NotificationLog::where('status', 'failed')
    ->where('created_at', '>=', now()->subHours(24))
    ->get();
```

### Get Statistics

```php
$stats = app(\App\Services\NotificationService::class)->getStatistics(
    from: now()->subDays(7),
    to: now()
);

// Returns: total, sent, failed, pending, delivery_rate, by_channel
```

### Check User Notifications

```php
echo $user->unreadNotificationsCount();
$unread = $user->unreadNotifications;
```

---

## Security & Best Practices

✅ **Security Measures**

- Encrypted sensitive fields (FCM token, Telegram chat_id in model storage)
- User preference checks before each send
- Audit logging for compliance
- Rate limiting on Telegram
- User authorization on API endpoints

✅ **Best Practices**

- Database channel for all events (audit trail)
- Email for formal notifications (queued)
- Telegram for urgent alerts (team coordination)
- FCM for mobile app engagement
- Graceful degradation (one channel failure ≠ all fail)
- Max retry attempts = 3
- Configurable notification retention (default 30 days)

---

## Testing Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Send test notification to User ID 1
- [ ] Check `notifications` table has record
- [ ] Check `notification_logs` table has 4 entries (one per channel)
- [ ] Verify preference checks prevent sending if disabled
- [ ] Test Telegram with valid bot token and chat_id
- [ ] Test FCM with valid server key and device token
- [ ] Test email queue: `php artisan queue:listen`
- [ ] Test retry command: `php artisan notifications:retry-failed`
- [ ] Test cleanup command: `php artisan notifications:cleanup`
- [ ] Test API endpoints with API client
- [ ] Verify notification statistics generation

---

## Files Summary

**Total New Files: 24**

- Migrations: 3
- Models: 3
- Services: 2
- Channels: 5
- Notifications: 3
- Controllers: 1
- Commands: 2
- Traits: 1
- Providers: 1
- Config: 1 (services.php updated, 1 new)
- Documentation: 2
- This summary: 1

**Modified Files: 4**

- `app/Models/User.php` - Added HasNotifications trait
- `bootstrap/providers.php` - Added NotificationServiceProvider
- `config/services.php` - Added Telegram & FCM config
- `config/notifications.php` - New file

---

## Next Steps

1. **Configure External Services**
    - Create Telegram Bot via @BotFather
    - Setup Firebase project & get server key
    - Configure .env with credentials

2. **Implement Custom Notifications**
    - Create notification classes for each event type
    - Define channels for each type
    - Integrate into controllers/services

3. **Build Frontend**
    - Notification center UI
    - Preferences page
    - Real-time updates (WebSocket or polling)

4. **Monitoring**
    - Setup logs dashboard
    - Alert on high failure rates
    - Monitor queue health

5. **Documentation**
    - Add API documentation
    - Create deployment guide
    - Document Telegram webhook setup

---

## Support & Questions

For detailed implementation examples, see:

- `docs/NOTIFICATION_USAGE.md` - Step-by-step guides
- `docs/NOTIFICATION_ARCHITECTURE.md` - Technical deep-dive
- Sample notification classes for reference

Documentation was written with Indonesian locale in mind (Malay translations in examples).
