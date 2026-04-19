<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LeaveRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public ?string $rejectionReason = null
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'leave_rejected', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'leave_rejected', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'leave_rejected', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Pengajuan Cuti Anda Ditolak")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pengajuan cuti Anda telah **ditolak**.")
            ->line("**Jenis Cuti:** {$this->leaveRequest->leave_type}")
            ->line("**Tanggal:** " . $this->leaveRequest->start_date->format('d/m/Y') . " - " . $this->leaveRequest->end_date->format('d/m/Y'))
            ->line("**Durasi:** {$this->leaveRequest->days} hari");

        if ($this->rejectionReason) {
            $mail->line("**Alasan Penolakan:** {$this->rejectionReason}");
        }

        return $mail
            ->action('Lihat Detail Cuti', url("/hrm/leave-requests/{$this->leaveRequest->id}"))
            ->line('Silakan hubungi atasan Anda untuk informasi lebih lanjut.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'leave_rejected',
            'module' => 'hrm',
            'title' => 'Cuti Ditolak',
            'message' => "Pengajuan cuti Anda ({$this->leaveRequest->start_date->format('d/m/Y')} - {$this->leaveRequest->end_date->format('d/m/Y')}) ditolak",
            'action_url' => url("/hrm/leave-requests/{$this->leaveRequest->id}"),
            'leave_request_id' => $this->leaveRequest->id,
            'rejection_reason' => $this->rejectionReason,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'leave_rejected',
            'module' => 'hrm',
            'title' => 'Cuti Ditolak',
            'message' => "Pengajuan cuti Anda ({$this->leaveRequest->start_date->format('d/m/Y')} - {$this->leaveRequest->end_date->format('d/m/Y')}) ditolak",
            'action_url' => url("/hrm/leave-requests/{$this->leaveRequest->id}"),
        ]);
    }
}
