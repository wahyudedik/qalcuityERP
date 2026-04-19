<?php

namespace App\Notifications;

use App\Models\TelecomSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PackageExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TelecomSubscription $subscription,
        public int $daysUntilExpiry
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'package_expiry', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'package_expiry', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'package_expiry', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiryDate = $this->subscription->end_date->format('d/m/Y');

        return (new MailMessage)
            ->subject("⚠️ Paket Internet Akan Berakhir dalam {$this->daysUntilExpiry} Hari")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Paket internet pelanggan **{$this->subscription->customer->name}** akan berakhir dalam **{$this->daysUntilExpiry} hari**.")
            ->line("**Paket:** {$this->subscription->package->name}")
            ->line("**Tanggal Berakhir:** {$expiryDate}")
            ->line("**Nomor Pelanggan:** {$this->subscription->customer->customer_number}")
            ->action('Lihat Pelanggan', url("/telecom/customers/{$this->subscription->customer->id}"))
            ->line('Hubungi pelanggan untuk perpanjangan paket.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'package_expiry',
            'module' => 'telecom',
            'title' => 'Paket Akan Berakhir',
            'message' => "Paket {$this->subscription->customer->name} akan berakhir dalam {$this->daysUntilExpiry} hari",
            'action_url' => url("/telecom/customers/{$this->subscription->customer->id}"),
            'subscription_id' => $this->subscription->id,
            'customer_name' => $this->subscription->customer->name,
            'package_name' => $this->subscription->package->name,
            'days_until_expiry' => $this->daysUntilExpiry,
            'end_date' => $this->subscription->end_date->format('Y-m-d'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'package_expiry',
            'module' => 'telecom',
            'title' => 'Paket Akan Berakhir',
            'message' => "Paket {$this->subscription->customer->name} akan berakhir dalam {$this->daysUntilExpiry} hari",
            'action_url' => url("/telecom/customers/{$this->subscription->customer->id}"),
        ]);
    }
}
