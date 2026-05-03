<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk SuperAdmin ketika persentase fallback event melebihi threshold.
 *
 * Requirements: 10.3
 */
class AiFallbackAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param string $useCase Use case yang mengalami fallback tinggi
     * @param int $totalRequests Total request dalam periode
     * @param int $fallbackCount Jumlah fallback event
     * @param float $fallbackPercent Persentase fallback
     * @param string $period Deskripsi periode (e.g., "1 jam terakhir")
     */
    public function __construct(
        public readonly string $useCase,
        public readonly int $totalRequests,
        public readonly int $fallbackCount,
        public readonly float $fallbackPercent,
        public readonly string $period,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Alert Fallback AI — {$this->useCase}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("**Persentase fallback event untuk use case melebihi threshold 20%.**")
            ->line("**Use Case:** {$this->useCase}")
            ->line("**Periode:** {$this->period}")
            ->line("**Total Request:** " . number_format($this->totalRequests, 0, ',', '.'))
            ->line("**Fallback Event:** " . number_format($this->fallbackCount, 0, ',', '.'))
            ->line("**Persentase Fallback:** {$this->fallbackPercent}%")
            ->action('Lihat Monitoring AI', url('/super-admin/ai/monitor'))
            ->line('Provider utama untuk use case ini mungkin mengalami masalah. Periksa status provider dan pertimbangkan untuk mengubah routing rule.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ai_fallback_alert',
            'use_case' => $this->useCase,
            'total_requests' => $this->totalRequests,
            'fallback_count' => $this->fallbackCount,
            'fallback_percent' => $this->fallbackPercent,
            'period' => $this->period,
            'message' => "Fallback event untuk {$this->useCase} mencapai {$this->fallbackPercent}% dalam {$this->period}",
        ];
    }
}
