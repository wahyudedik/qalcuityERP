<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SuspiciousAiActivityNotification — Notifikasi ke admin tenant ketika
 * terdeteksi pola penggunaan AI yang mencurigakan (banyak write ops dalam waktu singkat).
 *
 * Requirement 9.6: ERP_Agent SHALL membatasi laju eksekusi dan mengirimkan
 * notifikasi kepada administrator tenant.
 */
class SuspiciousAiActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $tenantName,
        public readonly int    $userId,
        public readonly string $userName,
        public readonly int    $writeOpsCount,
        public readonly int    $windowSeconds,
        public readonly string $detectedAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $windowMinutes = round($this->windowSeconds / 60, 1);

        return (new MailMessage)
            ->subject("⚠️ Aktivitas AI Mencurigakan — {$this->tenantName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Sistem mendeteksi pola penggunaan AI yang tidak biasa pada akun Anda.')
            ->line("**Pengguna:** {$this->userName}")
            ->line("**Jumlah operasi write:** {$this->writeOpsCount} dalam {$windowMinutes} menit terakhir")
            ->line("**Waktu deteksi:** {$this->detectedAt}")
            ->line('Laju eksekusi AI untuk pengguna ini telah dibatasi sementara sebagai tindakan pencegahan.')
            ->action('Lihat Audit Log AI', url('/agent/audit'))
            ->line('Jika aktivitas ini sah, pengguna dapat mencoba kembali setelah beberapa saat.')
            ->line('Jika Anda mencurigai penyalahgunaan, segera tinjau log aktivitas dan nonaktifkan akun pengguna tersebut.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'suspicious_ai_activity',
            'tenant_id'       => $this->tenantId,
            'user_id'         => $this->userId,
            'user_name'       => $this->userName,
            'write_ops_count' => $this->writeOpsCount,
            'window_seconds'  => $this->windowSeconds,
            'detected_at'     => $this->detectedAt,
            'message'         => "Terdeteksi {$this->writeOpsCount} operasi write AI dalam {$this->windowSeconds} detik oleh {$this->userName}.",
            'url'             => '/agent/audit',
        ];
    }
}
