<?php

namespace App\Notifications;

/**
 * Example notification for financial transactions
 */
class FinanceNotification extends BaseNotification
{
    public function __construct(
        private string $type, // 'income', 'expense', 'transfer', 'approval'
        private string $amount,
        private string $description,
        private array $details = [],
    ) {
    }

    public function getChannels(): array
    {
        return ['database', 'email', 'telegram', 'fcm'];
    }

    public function getSubject(): string
    {
        return match ($this->type) {
            'income' => 'Pendapatan Baru: ' . $this->amount,
            'expense' => 'Pengeluaran Baru: ' . $this->amount,
            'transfer' => 'Pemindahan Akaun: ' . $this->amount,
            'approval' => 'Perlu Kelulusan: ' . $this->amount,
            default => 'Notifikasi Kewangan',
        };
    }

    public function getMessage(): string
    {
        return $this->description;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'details' => $this->details,
        ];
    }

    public function toMail(object $notifiable): ?object
    {
        return new \Illuminate\Mail\Message(function ($mail) use ($notifiable) {
            $mail->to($notifiable->email)
                ->subject($this->getSubject())
                ->view('emails.finance_notification', [
                    'user' => $notifiable,
                    'type' => $this->type,
                    'amount' => $this->amount,
                    'description' => $this->description,
                    'details' => $this->details,
                ]);
        });
    }

    public function toTelegram(object $notifiable): ?string
    {
        return sprintf(
            "<b>%s</b>\n\n%s\n\nBesaran: <b>%s</b>",
            $this->getSubject(),
            $this->description,
            $this->amount
        );
    }

    public function toFCM(object $notifiable): ?array
    {
        return [
            'title' => $this->getSubject(),
            'body' => $this->description,
            'icon' => 'finance_icon',
            'data' => [
                'type' => $this->type,
                'amount' => $this->amount,
            ],
            'priority' => 'high',
        ];
    }
}
