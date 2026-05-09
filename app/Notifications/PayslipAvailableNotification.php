<?php

namespace App\Notifications;

use App\Models\NotificationPreference;
use App\Models\PayrollItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi slip gaji tersedia — dikirim ke karyawan setelah payroll diproses.
 *
 * Fix: Menggunakan PayrollItem (bukan Payslip yang tidak ada) sebagai data slip gaji.
 */
class PayslipAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PayrollItem $payrollItem) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'payslip_available', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'payslip_available', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'payslip_available', 'push')) {
            $channels[] = 'broadcast';
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $period = $this->payrollItem->payrollRun?->period ?? '-';
        $netSalary = 'Rp '.number_format((float) $this->payrollItem->net_salary, 0, ',', '.');
        $url = url("/payroll/slip/{$this->payrollItem->id}");

        return (new MailMessage)
            ->subject("Slip Gaji Periode {$period} Tersedia")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Slip gaji Anda untuk periode **{$period}** telah tersedia.")
            ->line("**Gaji Bersih:** {$netSalary}")
            ->line('**Status:** '.($this->payrollItem->status === 'paid' ? 'Sudah Dibayar' : 'Diproses'))
            ->action('Lihat Slip Gaji', $url)
            ->line('Anda dapat melihat dan mengunduh slip gaji dalam format PDF melalui tautan di atas.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        $period = $this->payrollItem->payrollRun?->period ?? '-';

        return [
            'type' => 'payslip_available',
            'module' => 'payroll',
            'title' => 'Slip Gaji Tersedia',
            'message' => "Slip gaji periode {$period} telah tersedia",
            'action_url' => url("/payroll/slip/{$this->payrollItem->id}"),
            'item_id' => $this->payrollItem->id,
            'period' => $period,
            'net_salary' => $this->payrollItem->net_salary,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $period = $this->payrollItem->payrollRun?->period ?? '-';

        return new BroadcastMessage([
            'type' => 'payslip_available',
            'module' => 'payroll',
            'title' => 'Slip Gaji Tersedia',
            'message' => "Slip gaji periode {$period} telah tersedia",
            'action_url' => url("/payroll/slip/{$this->payrollItem->id}"),
        ]);
    }
}
