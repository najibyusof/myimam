<?php

namespace App\Notifications;

/**
 * Example notification for approval workflows
 */
class ApprovalNotification extends BaseNotification
{
    public function __construct(
        private string $approvalType, // 'baucar', 'transfer', 'user'
        private int $approvalId,
        private string $action, // 'pending', 'approved', 'rejected'
        private ?string $reason = null,
    ) {
    }

    public function getChannels(): array
    {
        return ['database', 'email', 'telegram'];
    }

    public function getSubject(): string
    {
        return match ($this->action) {
            'pending' => "Tunggu Kelulusan: {$this->approvalType} #{$this->approvalId}",
            'approved' => "Diluluskan: {$this->approvalType} #{$this->approvalId}",
            'rejected' => "Ditolak: {$this->approvalType} #{$this->approvalId}",
            default => "Notifikasi Kelulusan",
        };
    }

    public function getMessage(): string
    {
        return $this->reason ?? 'Tiada maklumat tambahan.';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'approval_type' => $this->approvalType,
            'approval_id' => $this->approvalId,
            'action' => $this->action,
            'reason' => $this->reason,
        ];
    }

    public function toMail(object $notifiable): ?object
    {
        return new \Illuminate\Mail\Message(function ($mail) use ($notifiable) {
            $mail->to($notifiable->email)
                ->subject($this->getSubject())
                ->view('emails.approval_notification', [
                    'user' => $notifiable,
                    'approval_type' => $this->approvalType,
                    'approval_id' => $this->approvalId,
                    'action' => $this->action,
                    'reason' => $this->reason,
                ]);
        });
    }

    public function toTelegram(object $notifiable): ?string
    {
        $text = "<b>" . $this->getSubject() . "</b>\n\n";

        if ($this->reason) {
            $text .= "Sebab: " . $this->reason;
        }

        return $text;
    }
}
