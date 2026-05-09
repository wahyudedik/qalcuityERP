<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $tenantName,
        public readonly string $plan,
        public readonly float $amount,
        public readonly string $reason = '',
        public readonly string $orderId = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("❌ Pembayaran Langganan Gagal — {$this->tenantName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pembayaran langganan **{$this->plan}** Anda sebesar **Rp ".
                number_format($this->amount, 0, ',', '.').'** gagal diproses.');

        if ($this->orderId) {
            $mail->line("**Order ID:** {$this->orderId}");
        }

        if ($this->reason) {
            $mail->line("**Alasan:** {$this->reason}");
        }

        return $mail
            ->line('Akses Anda mungkin terbatas jika pembayaran tidak segera diselesaikan.')
            ->action('Coba Bayar Lagi', url('/subscription'))
            ->line('Jika Anda membutuhkan bantuan, hubungi tim support kami.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
