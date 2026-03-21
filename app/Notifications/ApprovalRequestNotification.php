<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ApprovalRequest $approval) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $requester = $this->approval->requester?->name ?? 'Seseorang';
        $workflow  = $this->approval->workflow?->name ?? 'Permintaan';

        return (new MailMessage)
            ->subject("Permintaan Persetujuan: {$workflow}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("{$requester} membutuhkan persetujuan Anda untuk: **{$workflow}**")
            ->when($this->approval->notes, fn($m) => $m->line("Catatan: {$this->approval->notes}"))
            ->action('Lihat & Setujui', url('/approvals'))
            ->line('Harap segera ditindaklanjuti.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
