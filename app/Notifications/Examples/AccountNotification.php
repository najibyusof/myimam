<?php

namespace App\Notifications;

use Illuminate\Mail\Mailable;

/**
 * Example notification for user account announcements
 */
class AccountNotification extends BaseNotification
{
    public function __construct(
        private string $subject,
        private string $message,
        private array $data = [],
    ) {
    }

    public function getChannels(): array
    {
        return ['database', 'email'];
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->subject,
            'message' => $this->message,
            'data' => $this->data,
            'url' => $this->data['url'] ?? null,
        ];
    }

    public function toMail(object $notifiable): ?object
    {
        return new \Illuminate\Mail\Message(function ($mail) use ($notifiable) {
            $mail->to($notifiable->email)
                ->subject($this->subject)
                ->view('emails.account_notification', [
                    'user' => $notifiable,
                    'subject' => $this->subject,
                    'message' => $this->message,
                    'data' => $this->data,
                ]);
        });
    }
}
