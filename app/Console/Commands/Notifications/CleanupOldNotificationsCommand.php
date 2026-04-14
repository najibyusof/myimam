<?php

namespace App\Console\Commands\Notifications;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupOldNotificationsCommand extends Command
{
    protected $signature = 'notifications:cleanup {--days=30 : Number of days to retain}';

    protected $description = 'Clean up old notifications from database';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $date = now()->subDays($days);

        $deleted = Notification::where('created_at', '<', $date)->delete();

        $this->info("Deleted {$deleted} old notifications.");

        return 0;
    }
}
