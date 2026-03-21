<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tenant $tenant,
        public int    $daysLeft
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urgency = $this->daysLeft <= 1 ? '🚨' : '⏰';

        return (new MailMessage)
            ->subject("{$urgency} Trial Anda berakhir dalam {$this->daysLeft} hari")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Trial gratis **{$this->tenant->name}** akan berakhir dalam **{$this->daysLeft} hari**.")
            ->line('Upgrade sekarang untuk tetap menggunakan semua fitur Qalcuity ERP tanpa gangguan.')
            ->action('Upgrade Sekarang', url('/subscription'))
            ->line('Butuh bantuan? Hubungi kami via WhatsApp: +62 816-5493-2383')
            ->salutation('Salam, Tim Qalcuity ERP');
    }
}
