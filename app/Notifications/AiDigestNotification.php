<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AiDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly array  $insights,
        public readonly string $period = 'daily',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $periodLabel = $this->period === 'weekly' ? 'Mingguan' : 'Harian';
        $dateLabel   = now()->translatedFormat('l, d F Y');

        $mail = (new MailMessage)
            ->subject("📊 Digest {$periodLabel} Qalcuity ERP — {$this->tenant->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Berikut ringkasan insight AI untuk **{$this->tenant->name}** — {$dateLabel}.");

        // Kelompokkan per severity
        $critical = array_filter($this->insights, fn($i) => $i['severity'] === 'critical');
        $warning  = array_filter($this->insights, fn($i) => $i['severity'] === 'warning');
        $info     = array_filter($this->insights, fn($i) => $i['severity'] === 'info');

        if (!empty($critical)) {
            $mail->line('---')
                 ->line('🔴 **Perlu Tindakan Segera:**');
            foreach ($critical as $insight) {
                $mail->line("**{$insight['title']}**")
                     ->line($insight['body']);
            }
        }

        if (!empty($warning)) {
            $mail->line('---')
                 ->line('🟡 **Perhatian:**');
            foreach ($warning as $insight) {
                $mail->line("**{$insight['title']}**")
                     ->line($insight['body']);
            }
        }

        if (!empty($info)) {
            $mail->line('---')
                 ->line('🟢 **Info:**');
            foreach (array_slice($info, 0, 3) as $insight) {
                $mail->line("**{$insight['title']}**")
                     ->line($insight['body']);
            }
        }

        return $mail
            ->line('---')
            ->action('Buka Dashboard', url('/dashboard'))
            ->line('Digest ini dikirim otomatis oleh Qalcuity AI. Balas email ini jika ada pertanyaan.')
            ->salutation('Salam, Qalcuity AI');
    }
}
