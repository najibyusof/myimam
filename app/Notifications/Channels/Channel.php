<?php

namespace App\Notifications\Channels;

use App\Models\NotificationLog;

abstract class Channel
{
    protected NotificationLog $log;

    public function setLog(NotificationLog $log): self
    {
        $this->log = $log;

        return $this;
    }

    abstract public function send(object $notifiable, object $notification): bool;

    protected function logSuccess(): void
    {
        if ($this->log) {
            $this->log->markAsSent();
        }
    }

    protected function logFailure(string $error): void
    {
        if ($this->log) {
            $this->log->markAsFailed($error);
        }
    }
}
