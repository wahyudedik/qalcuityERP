<?php

namespace App\Notifications;

use App\Models\PayrollRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PayrollRun $run) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = 'Rp '.number_format($this->run->total_net, 0, ',', '.');

        return (new MailMessage)
            ->subject("Penggajian {$this->run->period} Telah Diproses")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Penggajian periode **{$this->run->period}** telah berhasil diproses.")
            ->line("**Total Gaji Bersih:** {$total}")
            ->line('**Status:** '.ucfirst($this->run->status))
            ->action('Lihat Detail Penggajian', url('/payroll'))
            ->line('Segera tandai sebagai dibayar setelah transfer dilakukan.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
