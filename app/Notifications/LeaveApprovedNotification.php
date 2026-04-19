<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LeaveApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'leave_approved', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'leave_approved', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'leave_approved', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pengajuan Cuti Anda Telah Disetujui")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pengajuan cuti Anda telah **disetujui**.")
            ->line("**Jenis Cuti:** {$this->leaveRequest->leave_type}")
            ->line("**Tanggal:** " . $this->leaveRequest->start_date->format('d/m/Y') . " - " . $this->leaveRequest->end_date->format('d/m/Y'))
            ->line("**Durasi:** {$this->leaveRequest->days} hari")
            ->line("**Alasan:** {$this->leaveRequest->reason}")
            ->action('Lihat Detail Cuti', url("/hrm/leave-requests/{$this->leaveRequest->id}"))
            ->line('Selamat menikmati waktu istirahat Anda!')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'leave_approved',
            'module' => 'hrm',
            'title' => 'Cuti Disetujui',
            'message' => "Pengajuan cuti Anda ({$this->leaveRequest->start_date->format('d/m/Y')} - {$this->leaveRequest->end_date->format('d/m/Y')}) telah disetujui",
            'action_url' => url("/hrm/leave-requests/{$this->leaveRequest->id}"),
            'leave_request_id' => $this->leaveRequest->id,
            'start_date' => $this->leaveRequest->start_date->format('Y-m-d'),
            'end_date' => $this->leaveRequest->end_date->format('Y-m-d'),
            'days' => $this->leaveRequest->days,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'leave_approved',
            'module' => 'hrm',
            'title' => 'Cuti Disetujui',
            'message' => "Pengajuan cuti Anda ({$this->leaveRequest->start_date->format('d/m/Y')} - {$this->leaveRequest->end_date->format('d/m/Y')}) telah disetujui",
            'action_url' => url("/hrm/leave-requests/{$this->leaveRequest->id}"),
        ]);
    }
}
