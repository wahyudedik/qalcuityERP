<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk SuperAdmin ketika biaya AI tenant melebihi threshold.
 *
 * Requirements: 6.10
 */
class AiCostThresholdExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $tenantName  Nama tenant yang melebihi threshold
     * @param  int  $tenantId  ID tenant
     * @param  float  $totalCost  Total biaya AI bulan ini (IDR)
     * @param  float  $threshold  Threshold yang dikonfigurasi (IDR)
     * @param  string  $period  Periode (format: Y-m)
     */
    public function __construct(
        public readonly string $tenantName,
        public readonly int $tenantId,
        public readonly float $totalCost,
        public readonly float $threshold,
        public readonly string $period,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $overAmount = $this->totalCost - $this->threshold;
        $overPercent = round(($this->totalCost / $this->threshold - 1) * 100, 1);

        return (new MailMessage)
            ->subject("🚨 Alert Biaya AI — {$this->tenantName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line('**Biaya AI tenant melebihi threshold yang dikonfigurasi.**')
            ->line("**Tenant:** {$this->tenantName} (ID: {$this->tenantId})")
            ->line("**Periode:** {$this->period}")
            ->line('**Total Biaya:** Rp '.number_format($this->totalCost, 2, ',', '.'))
            ->line('**Threshold:** Rp '.number_format($this->threshold, 2, ',', '.'))
            ->line('**Kelebihan:** Rp '.number_format($overAmount, 2, ',', '.')." (+{$overPercent}%)")
            ->action('Lihat Detail Biaya AI', url("/super-admin/ai/cost-report?tenant_id={$this->tenantId}"))
            ->line('Tinjau penggunaan AI tenant ini untuk memastikan tidak ada anomali atau penyalahgunaan.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ai_cost_threshold_exceeded',
            'tenant_name' => $this->tenantName,
            'tenant_id' => $this->tenantId,
            'total_cost' => $this->totalCost,
            'threshold' => $this->threshold,
            'period' => $this->period,
            'message' => "Biaya AI tenant {$this->tenantName} melebihi threshold: Rp ".
                number_format($this->totalCost, 2, ',', '.'),
        ];
    }
}
