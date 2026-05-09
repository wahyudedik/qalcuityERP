<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ApprovalRequest $approval) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $workflow = $this->approval->workflow?->name ?? 'Permintaan';
        $approver = $this->approval->approver?->name ?? 'Admin';
        $isApproved = $this->approval->status === 'approved';

        $statusText = $isApproved ? '✅ Disetujui' : '❌ Ditolak';
        $color = $isApproved ? 'success' : 'error';

        return (new MailMessage)
            ->subject("{$statusText}: {$workflow}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Permintaan Anda **{$workflow}** telah **{$statusText}** oleh {$approver}.")
            ->when(! $isApproved && $this->approval->rejection_reason,
                fn ($m) => $m->line("Alasan penolakan: {$this->approval->rejection_reason}")
            )
            ->action('Lihat Detail', url('/approvals'))
            ->salutation('Salam, Qalcuity ERP');
    }
}
