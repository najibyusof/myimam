# Notification System - Implementation Flow & Architecture

## System Flow Diagram

```
USER/EVENT TRIGGER
        │
        ▼
┌──────────────────────────────┐
│  Create Notification Object   │
│  (FinanceNotification, etc)   │
└──────────────────┬────────────┘
                   │
                   ▼
        ┌─────────────────────┐
        │  NotificationService│
        │    .send(user,      │
        │     notification)   │
        └────────┬────────────┘
                 │
                 ▼
      ┌──────────────────────┐
      │ NotificationDispatcher
      │ - Select channels    │
      │ - Check preferences  │
      └────────┬─────────────┘
               │
        ┌──────┴───────┬──────────┬──────────┐
        │              │          │          │
        ▼              ▼          ▼          ▼
    ┌────────┐   ┌────────┐  ┌──────────┐ ┌────────┐
    │Database│   │ Email  │  │Telegram  │ │  FCM   │
    │Channel │   │Channel │  │Channel   │ │Channel │
    └──┬─────┘   └───┬────┘  └────┬─────┘ └───┬────┘
       │             │            │            │
       │ toDatabase()│ toMail()    │ toTelegram()
       │             │ toTelegram()│ toFCM()
       │             │ toFCM()     │
       │             │            │
       ▼             ▼            ▼            ▼
    [DB]        [Queue]    [Telegram API]  [FCM API]
   ✓ Instant    ✓ Async    ✓ Near-instant ✓ Near-instant
   ✓ Audit      ✓ Retry    ✓ HTML format  ✓ Data payload


       └──────────────────┬──────────────────┘
                          │
                          ▼
            ┌─────────────────────────┐
            │ NotificationLog Entry   │
            │ (channel, status, etc)  │
            └─────────────────────────┘


              PREFERENCES CHECK
    ┌──────────────────────────────┐
    │ NotificationPreference       │
    │ .isChannelEnabled($channel): │
    │   - email: true/false        │
    │   - push: token exists?      │
    │   - telegram: chat_id set?   │
    │   - sms: true/false          │
    └──────────────────────────────┘
```

---

## Detailed Component Interaction

### 1. Notification Service Entry Point

```
NotificationService::send()
│
├─ Accept: notifiable (User|[]Users)
│          notification (BaseNotification)
│          channels override (optional)
│
└─ Delegate to NotificationDispatcher
```

### 2. Dispatcher Logic

```
NotificationDispatcher::dispatch()
│
├─ Get channels from notification.getChannels()
│
├─ Retrieve user preferences
│
├─ Filter channels by preferences
│  │
│  ├─ Check email_notifications boolean
│  ├─ Check push_notifications + fcm_token
│  ├─ Check telegram_notifications + chat_id
│  └─ Check sms_notifications boolean
│
├─ For each enabled channel:
│  │
│  ├─ Create NotificationLog entry (pending)
│  │
│  ├─ Call channel.send(notifiable, notification)
│  │  │
│  │  ├─ Call notification.toChannel()
│  │  ├─ Format for that channel
│  │  └─ Send/Queue to destination
│  │
│  └─ Update NotificationLog (sent/failed/retry)
│
└─ Return aggregate success boolean
```

### 3. Channel Execution

**Database Channel**

```
toDatabase() → Gets data array
    ↓
Create Notification model
    ↓
Stored in `notifications` table
    ↓
Immediately available in app
```

**Email Channel**

```
toMail() → Gets Mailable object
    ↓
Mail::queue($mailable)
    ↓
Queued in job queue
    ↓
Processed by queue worker
    ↓
Sent via configured mail service
```

**Telegram Channel**

```
toTelegram() → Gets HTML string
    ↓
Check telegram_chat_id exists
    ↓
HTTP POST to Telegram API
    ↓
Message delivered to user's chat
    ↓
User sees in Telegram app
```

**FCM Channel**

```
toFCM() → Gets payload array
    ↓
Check fcm_token exists
    ↓
HTTP POST to Firebase API
    ↓
Push notification queued
    ↓
Delivered to mobile/web device
```

---

## Data Models Relationship

```
User (1)
  │
  ├─── notifications (N) ◄──── Notification (morph)
  │    │                           │
  │    └─ unread: read_at = null   ├─ data (JSON)
  │    └─ read: read_at set      └─ type (class name)
  │
  ├─── notificationPreference (1) ◄──── NotificationPreference
  │    │                                     │
  │    ├─ email_notifications (bool)         ├─ telegram_chat_id
  │    ├─ push_notifications (bool)          ├─ fcm_token
  │    ├─ telegram_notifications (bool)      └─ notification_types (JSON)
  │    └─ enabled_channels()
  │
  └─── [Related to] ◄──── NotificationLog
       │
       ├─ channel (email|telegram|fcm|database)
       ├─ status (pending|sent|failed)
       ├─ retry_count
       └─ error_message (if failed)
```

---

## Configuration Hierarchy

```
config/notifications.php (feature flags)
        │
        ├─ enabled_channels
        ├─ retry_failed_notifications
        ├─ max_retry_attempts
        │
        ├─ email.*
        │   ├─ queue
        │   ├─ queue_name
        │   └─ retry_after
        │
        ├─ telegram.*
        │   ├─ enabled
        │   ├─ rate_limit
        │   └─ rate_limit_per_minute
        │
        ├─ fcm.*
        │   ├─ enabled
        │   ├─ priority
        │   └─ time_to_live
        │
        └─ database.*
            ├─ retention_days
            ├─ auto_cleanup
            └─ cleanup_schedule


config/services.php (credentials)
        │
        ├─ telegram
        │   ├─ bot_token (env: TELEGRAM_BOT_TOKEN)
        │   └─ webhook_url (env: TELEGRAM_WEBHOOK_URL)
        │
        └─ fcm
            ├─ server_key (env: FCM_SERVER_KEY)
            └─ sender_id (env: FCM_SENDER_ID)
```

---

## Notification Lifecycle

### Create Phase

```
1. Instantiate notification class
   new FinanceNotification(type, amount, description)

2. Define channels
   getChannels() → ['database', 'email', 'telegram', 'fcm']

3. Define transformations
   toDatabase() → []
   toMail() → Mailable
   toTelegram() → string
   toFCM() → []
```

### Send Phase

```
1. Call service
   $service->send($user, $notification)

2. Dispatcher receives request
   - Check user preferences
   - Filter to enabled channels
   - Create log entries

3. Each channel processes
   - Get user preference
   - Format data
   - Send/queue
   - Update log (success/failed)

4. Return result
   - true: at least one channel succeeded
   - false: all channels failed
```

### Persistence Phase

```
1. Notifications table (all types)
   - One per sent notification
   - JSON data storage
   - Read/unread tracking

2. Notification logs table (every attempt)
   - One per channel per notification
   - Status tracking (pending/sent/failed)
   - Retry count for failed sends
   - Error messages for debugging

3. User preferences table (per user)
   - Channel toggles
   - Device tokens
   - Notification type filters
```

### Retry Phase

```
1. Failed notification identified
   where status = 'failed' AND retry_count < max

2. Via console command
   php artisan notifications:retry-failed

3. Dispatcher attempts again
   - Check preferences (may have changed)
   - Attempt send again
   - Update log entry

4. Max attempts reached
   - No more retries
   - Manual intervention required
```

### Cleanup Phase

```
1. Old notifications maintenance
   where created_at < now()->subDays(30)

2. Via console command
   php artisan notifications:cleanup

3. Archive or delete
   - Configurable retention days
   - Frees database space
   - Audit trail remains in logs
```

---

## Error Handling Strategy

```
Try to send notification
        │
        ├─ Success ✓
        │   └─ Update log: sent
        │
        └─ Exception caught
            │
            ├─ Log error message
            │
            ├─ Increment retry_count
            │
            ├─ Update log: failed + error message
            │
            ├─ If retry_count < max_retries
            │   └─ Mark for retry
            │
            └─ If retry_count >= max_retries
                └─ Mark as final failure
                    (manual review required)
```

---

## API Request/Response Flow

### Get Notifications

```
GET /api/notifications?unread=true&limit=50

Response:
{
    "data": [
        {
            "id": "uuid",
            "type": "App\\Notifications\\FinanceNotification",
            "data": { "amount": "500", "type": "expense" },
            "read_at": null,
            "created_at": "2026-04-14T10:00:00Z"
        }
    ],
    "unread_count": 5,
    "total_count": 50
}
```

### Update Preferences

```
POST /api/notifications/preferences

Request:
{
    "email_notifications": true,
    "push_notifications": true,
    "telegram_notifications": true,
    "telegram_chat_id": "123456789",
    "fcm_token": "device_token_xyz",
    "notification_types": ["finance", "approval"]
}

Response:
{
    "message": "Preferences updated",
    "data": {
        "id": 1,
        "email_notifications": true,
        ...
    }
}
```

---

## Integration Points

### Event Listeners

```
Event: ExpenseCreated
    ↓
Listener: SendFinanceNotification
    ↓
Calls: $service->send($manager, FinanceNotification)
```

### Controller Actions

```
BelanjaSimpanController::store()
    ↓
Create Belanja record
    ↓
Call: $this->notificationService->send($managers, $notification)
    ↓
Return response
```

### Queue Jobs

```
SendNotificationJob::handle()
    ↓
Deserialize notification
    ↓
Call: $service->send($user, $notification)
    ↓
Update queue status
```

---

## Performance Considerations

| Operation        | Speed     | Impact             |
| ---------------- | --------- | ------------------ |
| Database send    | Instant   | None (synchronous) |
| Email queue      | Instant   | None (queued)      |
| Telegram API     | 100-500ms | Blocking           |
| FCM API          | 100-500ms | Blocking           |
| Preference check | <1ms      | Minimal            |
| Log creation     | <5ms      | Minimal            |

**Recommendation**: Run Telegram/FCM in queue to avoid blocking.

---

## Monitoring Points

```
Incoming Event Request
        │
        ├─ Check: Dispatcher created
        ├─ Check: Log entries created
        ├─ Check: Channel selection logic
        │
        ├─ Send Attempt 1
        │   └─ Check: Success/failure logged
        │
        ├─ Send Attempt 2 (if fail)
        │   └─ Check: Retry count incremented
        │
        └─ Final State
            └─ Check: Delivery rate = sent/total
```

---

## Deployment Checklist

- [ ] `.env` configured with credentials
- [ ] Migrations run: `php artisan migrate`
- [ ] Service provider registered
- [ ] User model has HasNotifications trait
- [ ] Queue driver configured
- [ ] Telegram bot token valid
- [ ] FCM credentials valid
- [ ] API routes registered (if needed)
- [ ] Test notification sends successfully
- [ ] Monitor logs for errors
- [ ] Schedule cleanup command in scheduler
- [ ] Monitor failed notifications regularly

---

## Scaling Considerations

1. **High Volume Sending**
    - Use batch operations
    - Queue all external sends
    - Monitor queue performance

2. **Preference Caching**
    - Cache user preferences for repeated sends
    - Invalidate on preference update

3. **Log Retention**
    - Archive old logs periodically
    - Configure retention_days appropriately
    - Run cleanup command on schedule

4. **Rate Limiting**
    - Telegram has rate limits
    - Implement exponential backoff
    - Queue retries appropriately

5. **Database Performance**
    - Index on: (notifiable_type, notifiable_id)
    - Index on: (channel, status, created_at)
    - Partition logs by date if very large
