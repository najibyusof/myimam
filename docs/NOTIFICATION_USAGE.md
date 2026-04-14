# Notification System - Implementation Guide

## Quick Start

### 1. Setup Environment Variables

```bash
# .env
TELEGRAM_BOT_TOKEN=123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/webhook/telegram
FCM_SERVER_KEY=AAAAkXKg...
FCM_SENDER_ID=123456789
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Register Service Provider

Already registered in `bootstrap/providers.php`:

```php
App\Providers\NotificationServiceProvider::class,
```

## Implementation Examples

### Example 1: Finance Notification

Send when new expense is recorded:

```php
use App\Notifications\FinanceNotification;
use App\Models\User;

$user = User::find(1);

$notification = new FinanceNotification(
    type: 'expense',
    amount: 'RM 500.00',
    description: 'Pembelian dapur untuk acara Jumaah',
    details: [
        'category' => 'Perlengkapan Masjid',
        'date' => '2026-04-14',
        'reference_id' => 'EXP-001',
    ]
);

// Send via all enabled channels
app(\App\Services\NotificationService::class)->send($user, $notification);

// Or specify channels
app(\App\Services\NotificationService::class)->send(
    $user,
    $notification,
    ['database', 'email', 'telegram']
);
```

### Example 2: Approval Workflow Notification

Send when baucar needs approval:

```php
use App\Notifications\ApprovalNotification;

$approvers = User::where('peranan', 'finance_manager')->get();

$notification = new ApprovalNotification(
    approvalType: 'baucar',
    approvalId: 123,
    action: 'pending',
    reason: 'Sila semak dan luluskan baucar ini'
);

// Send to multiple users
app(\App\Services\NotificationService::class)->send($approvers, $notification);
```

### Example 3: Custom Notification

Create custom notification for your use case:

```php
// app/Notifications/BiayaTransferNotification.php

namespace App\Notifications;

class BiayaTransferNotification extends BaseNotification
{
    public function __construct(
        private string $fromAccount,
        private string $toAccount,
        private string $amount,
        private string $reference,
    ) {
    }

    public function getChannels(): array
    {
        // Only database and email for transfers
        return ['database', 'email'];
    }

    public function getSubject(): string
    {
        return "Pemindahan Akaun: {$this->amount}";
    }

    public function getMessage(): string
    {
        return "{$this->fromAccount} ke {$this->toAccount}";
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'from' => $this->fromAccount,
            'to' => $this->toAccount,
            'amount' => $this->amount,
            'reference' => $this->reference,
        ];
    }

    public function toMail(object $notifiable): ?object
    {
        return new \Illuminate\Mail\Message(function ($mail) use ($notifiable) {
            $mail->to($notifiable->email)
                ->subject($this->getSubject())
                ->view('emails.transfer_notification', [
                    'user' => $notifiable,
                    'from' => $this->fromAccount,
                    'to' => $this->toAccount,
                    'amount' => $this->amount,
                ]);
        });
    }

    public function toTelegram(object $notifiable): ?string
    {
        return "💰 <b>Pemindahan Akaun</b>\n" .
               "Dari: {$this->fromAccount}\n" .
               "Ke: {$this->toAccount}\n" .
               "Jumlah: <b>{$this->amount}</b>";
    }

    public function toFCM(object $notifiable): ?array
    {
        return [
            'title' => 'Pemindahan Akaun',
            'body' => "{$this->amount} dari {$this->fromAccount}",
            'icon' => 'transfer_icon',
            'data' => [
                'reference' => $this->reference,
                'amount' => $this->amount,
            ],
        ];
    }
}
```

### Example 4: Sending from Controller

```php
// app/Http/Controllers/Belanja/BelanjaSimpanController.php

namespace App\Http\Controllers\Belanja;

use App\Http\Controllers\Controller;
use App\Models\BaucarBayaran;
use App\Notifications\FinanceNotification;
use App\Services\NotificationService;

class BelanjaSimpanController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function store()
    {
        // ... validation and save ...

        $belanja = Belanja::create([...]);

        // Notify relevant users
        $managers = User::where('peranan', 'finance_manager')->get();

        $this->notificationService->send(
            $managers,
            new FinanceNotification(
                type: 'expense',
                amount: $belanja->jumlah,
                description: $belanja->keterangan,
                details: [
                    'category' => $belanja->kategoriBelanja->nama,
                    'baucar_id' => $belanja->baucar_id,
                ]
            )
        );

        return redirect()->back()->with('success', 'Belanja disimpan');
    }
}
```

### Example 5: Batch Notifications

```php
use App\Models\User;
use App\Notifications\AccountNotification;
use App\Services\NotificationService;

$service = app(NotificationService::class);

// Notify all active users in specific masjid
$users = User::where('id_masjid', 1)
    ->where('aktif', true)
    ->get();

$notification = new AccountNotification(
    subject: 'Laporan Kewangan Bulanan',
    message: 'Laporan kewangan untuk bulan April 2026 sudah siap',
    data: ['url' => route('reports.monthly')]
);

$service->send($users, $notification);
```

## User Preferences Management

### Setup User Preferences

```php
// In controller or seeder
$user = User::find(1);
$preference = $user->getOrCreateNotificationPreference();

$preference->update([
    'email_notifications' => true,
    'telegram_notifications' => true,
    'telegram_chat_id' => '123456789',
    'fcm_token' => 'device_token_here',
    'notification_types' => ['finance', 'approval', 'system'],
]);
```

### Check If Channel Enabled

```php
$preference = $user->getOrCreateNotificationPreference();

if ($preference->isChannelEnabled('email')) {
    // Send email
}

if ($preference->isNotificationTypeEnabled('finance')) {
    // This notification type is enabled
}
```

## Telegram Integration

### Get User Telegram Chat ID

1. User starts your bot: `/start`
2. Bot sends welcome message with link
3. User clicks link to authenticate
4. Save `chat_id` to preferences

```php
// Example webhook handler
Route::post('/webhook/telegram', function (Request $request) {
    $data = $request->all();

    if (isset($data['message']['text']) && $data['message']['text'] === '/start') {
        $chatId = $data['message']['chat']['id'];

        // Save to user
        $user = User::whereEmail($data['message']['from']['username'] . '@example.com')->first();
        if ($user) {
            $user->getOrCreateNotificationPreference()->update([
                'telegram_chat_id' => $chatId,
                'telegram_notifications' => true,
            ]);
        }
    }
});
```

## Firebase Cloud Messaging (FCM)

### Register Device Token

Client-side (JavaScript):

```javascript
// Get FCM token and send to server
messaging.getToken({ vapidKey: "your_public_key" }).then((token) => {
    fetch("/api/notifications/preferences", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization:
                "Bearer " + document.querySelector('[name="api_token"]').value,
        },
        body: JSON.stringify({
            fcm_token: token,
        }),
    });
});
```

### FCM Service Worker

```javascript
// public/firebase-messaging-sw.js
importScripts(
    "https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js",
);
importScripts(
    "https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js",
);

firebase.initializeApp({
    apiKey: "YOUR_API_KEY",
    projectId: "YOUR_PROJECT_ID",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon,
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
```

## Email Notifications

### Create Email Template

```blade
<!-- resources/views/emails/finance_notification.blade.php -->
<x-mail::message>
# {{ $subject }}

Penggunaan: {{ $user->name }}

**Jenis:** {{ ucfirst($type) }}
**Jumlah:** {{ $amount }}

{{ $description }}

<x-mail::button :url="route('dashboard')">
Lihat Dashboard
</x-mail::button>

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
```

## Monitoring & Analytics

### Check Notification Statistics

```php
use App\Services\NotificationService;

$service = app(NotificationService::class);

$stats = $service->getStatistics(
    from: now()->subDays(7),
    to: now()
);

// Returns:
// [
//     'total' => 250,
//     'sent' => 235,
//     'failed' => 10,
//     'pending' => 5,
//     'delivery_rate' => 94.0,
//     'by_channel' => [...]
// ]
```

### Retry Failed Notifications

```bash
# Retry all failed
php artisan notifications:retry-failed

# Retry failed emails only
php artisan notifications:retry-failed --channel=email

# Retry specific number
php artisan notifications:retry-failed --limit=5
```

### Cleanup Old Notifications

```bash
# Cleanup older than 30 days
php artisan notifications:cleanup --days=30

# Cleanup older than 7 days
php artisan notifications:cleanup --days=7
```

## Error Handling

### Graceful Failure

```php
try {
    $service->send($user, $notification);
} catch (\Exception $e) {
    \Log::error('Notification failed', [
        'error' => $e->getMessage(),
        'user_id' => $user->id,
    ]);

    // Application continues, notification logged for retry
}
```

### View Failed Notifications

```php
// Get failed notifications in last 7 days
$failed = \App\Models\NotificationLog::where('status', 'failed')
    ->where('created_at', '>=', now()->subDays(7))
    ->get();

foreach ($failed as $log) {
    echo "Channel: {$log->channel}";
    echo "Error: {$log->error_message}";
    echo "Retries: {$log->retry_count}";
}
```

## Best Practices

1. **User Preference Check** - Always respect user's channel preferences
2. **Graceful Degradation** - If one channel fails, others still attempt
3. **Queue Management** - Emails should always be queued, not sent synchronously
4. **Rate Limiting** - Implement rate limits to prevent notification spam
5. **Logging** - All sends logged for audit trail and debugging
6. **Template Testing** - Test email/telegram/FCM messages in different formats
7. **Timezone Awareness** - Store times in UTC, convert for display
8. **Error Recovery** - Automatic retry for failed sends with max attempt limit
9. **Data Privacy** - Don't store sensitive data in notification payloads
10. **Documentation** - Keep notification types and channels documented

## Troubleshooting

### Notifications Not Sending

1. Check user preference is enabled for channel
2. Verify Telegram chat_id is set
3. Check FCM token is valid
4. Review notification logs: `NotificationLog::where('status', 'failed')->get()`

### Telegram Not Working

1. Verify `TELEGRAM_BOT_TOKEN` in .env
2. Test token: `curl https://api.telegram.org/botTOKEN/getMe`
3. Ensure user has set `telegram_chat_id`

### FCM Not Working

1. Verify `FCM_SERVER_KEY` in .env
2. Check device token format
3. Test key: Use Firebase console
4. Check notification payload format

### Queued Emails Not Sending

1. Ensure queue driver configured: `php artisan queue:listen`
2. Check queue failed jobs: `php artisan queue:failed`
3. Verify email configuration in `config/mail.php`
4. Check Laravel logs for errors
