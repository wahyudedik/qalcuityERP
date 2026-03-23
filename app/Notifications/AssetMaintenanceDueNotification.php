<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetMaintenanceDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array $items  [['asset_name', 'type', 'scheduled_date', 'days_until'], ...]
     */
    public function __construct(
        public readonly array  $items,
        public readonly string $tenantName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count    = count($this->items);
        $overdue  = array_filter($this->items, fn($i) => $i['days_until'] < 0);
        $upcoming = array_filter($this->items, fn($i) => $i['days_until'] >= 0);

        $mail = (new MailMessage)
            ->subject("🔧 {$count} Jadwal Pemeliharaan Aset — {$this->tenantName}")
            ->greeting("Halo, {$notifiable->name}!");

        if (!empty($overdue)) {
            $mail->line('**Pemeliharaan Terlambat:**');
            foreach ($overdue as $item) {
                $mail->line("• **{$item['asset_name']}** — {$item['type']} | Terlambat " . abs($item['days_until']) . " hari");
            }
        }

        if (!empty($upcoming)) {
            $mail->line('**Pemeliharaan Mendatang:**');
            foreach (array_slice($upcoming, 0, 5) as $item) {
                $label = $item['days_until'] === 0 ? 'Hari ini' : "dalam {$item['days_until']} hari";
                $mail->line("• **{$item['asset_name']}** — {$item['type']} | {$label} ({$item['scheduled_date']})");
            }
        }

        return $mail
            ->action('Lihat Pemeliharaan Aset', url('/assets/maintenance'))
            ->line('Pastikan pemeliharaan dilakukan tepat waktu untuk menjaga kondisi aset.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
