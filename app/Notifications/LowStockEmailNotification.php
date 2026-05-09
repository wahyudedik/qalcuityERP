<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $items) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->items);
        $mail = (new MailMessage)
            ->subject("⚠️ {$count} Produk Stok Menipis")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("{$count} produk memiliki stok di bawah batas minimum:");

        foreach (array_slice($this->items, 0, 10) as $item) {
            $mail->line("• **{$item['product']}** — stok: {$item['qty']} {$item['unit']} (min: {$item['min']})");
        }

        if ($count > 10) {
            $mail->line('...dan '.($count - 10).' produk lainnya.');
        }

        return $mail
            ->action('Lihat Inventori', url('/inventory'))
            ->line('Segera lakukan pemesanan ulang untuk menghindari kehabisan stok.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
