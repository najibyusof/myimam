<?php

namespace App\Providers;

use App\Notifications\NotificationDispatcher;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationDispatcher::class, function ($app) {
            return new NotificationDispatcher();
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(NotificationDispatcher::class)
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/notifications.php' => config_path('notifications.php'),
        ], 'notifications');
    }
}
