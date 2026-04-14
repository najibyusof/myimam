<?php

namespace App\Notifications\Channels;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailChannel extends Channel
{
    public function send(object $notifiable, object $notification): bool
    {
        try {
            if (!method_exists($notification, 'toMail')) {
                return false;
            }

            $mail = $notification->toMail($notifiable);

            if ($mail instanceof Mailable) {
                Mail::to($notifiable->email)->queue($mail);
                $this->logSuccess();

                return true;
            }

            return false;
        } catch (Throwable $e) {
            $this->logFailure($e->getMessage());

            return false;
        }
    }
}
