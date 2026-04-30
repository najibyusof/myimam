<?php

namespace App\Console\Commands;

use App\Services\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class ProcessSubscriptionLifecycleCommand extends Command
{
    protected $signature = 'subscriptions:process-lifecycle {--days=3 : Send reminder this many days before expiry}';

    protected $description = 'Process auto-renewal and send subscription expiry reminders.';

    public function handle(SubscriptionLifecycleService $service): int
    {
        $days = (int) $this->option('days');

        $renewResult = $service->processAutoRenewals();
        $reminderResult = $service->sendExpiryReminders($days);

        $this->info('Subscription lifecycle completed.');
        $this->line('Auto renew created: ' . $renewResult['renewed']);
        $this->line('Auto renew failed: ' . $renewResult['failed']);
        $this->line('WhatsApp reminders sent: ' . $reminderResult['sent']);
        $this->line('WhatsApp reminders skipped: ' . $reminderResult['skipped']);

        return self::SUCCESS;
    }
}
