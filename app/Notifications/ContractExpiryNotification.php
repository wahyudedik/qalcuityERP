<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ContractExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Employee $employee,
        public int $daysUntilExpiry
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'contract_expiry', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'contract_expiry', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'contract_expiry', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiryDate = $this->employee->contract_end_date->format('d/m/Y');
        
        return (new MailMessage)
            ->subject("⚠️ Kontrak Karyawan Akan Berakhir dalam {$this->daysUntilExpiry} Hari")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Kontrak karyawan **{$this->employee->name}** akan berakhir dalam **{$this->daysUntilExpiry} hari**.")
            ->line("**Tanggal Berakhir:** {$expiryDate}")
            ->line("**Posisi:** {$this->employee->position}")
            ->line("**Departemen:** {$this->employee->department}")
            ->action('Lihat Data Karyawan', url("/hrm/employees/{$this->employee->id}"))
            ->line('Segera lakukan perpanjangan kontrak atau proses terminasi sesuai kebijakan perusahaan.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'contract_expiry',
            'module' => 'hrm',
            'title' => 'Kontrak Karyawan Akan Berakhir',
            'message' => "Kontrak {$this->employee->name} akan berakhir dalam {$this->daysUntilExpiry} hari",
            'action_url' => url("/hrm/employees/{$this->employee->id}"),
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'days_until_expiry' => $this->daysUntilExpiry,
            'contract_end_date' => $this->employee->contract_end_date->format('Y-m-d'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'contract_expiry',
            'module' => 'hrm',
            'title' => 'Kontrak Karyawan Akan Berakhir',
            'message' => "Kontrak {$this->employee->name} akan berakhir dalam {$this->daysUntilExpiry} hari",
            'action_url' => url("/hrm/employees/{$this->employee->id}"),
        ]);
    }
}
