<?php

namespace App\Console\Commands;

use App\Services\SubscriptionManagementService;
use Illuminate\Console\Command;

class SyncSubscriptionStatusCommand extends Command
{
    protected $signature = 'subscriptions:sync-status';

    protected $description = 'Synchronize subscription expiry and tenant snapshot status.';

    public function handle(SubscriptionManagementService $service): int
    {
        $result = $service->syncSubscriptionStatuses();

        $this->info('Subscription sync completed.');
        $this->line('Expired subscriptions updated: ' . $result['expired_subscriptions']);
        $this->line('Masjid snapshot synced: ' . $result['synced_masjids']);

        return self::SUCCESS;
    }
}
