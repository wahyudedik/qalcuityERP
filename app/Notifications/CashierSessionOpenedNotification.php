<?php

namespace App\Notifications;

use App\Models\CashierSession;
use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CashierSessionOpenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CashierSession $session) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'cashier_session_opened', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'cashier_session_opened', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'cashier_session_opened', 'push')) {
            $channels[] = 'broadcast';
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $openingBalance = 'Rp '.number_format($this->session->opening_balance, 0, ',', '.');

        return (new MailMessage)
            ->subject("Sesi Kasir Dibuka - {$this->session->cashier->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Sesi kasir telah dibuka oleh **{$this->session->cashier->name}**.")
            ->line('**Waktu Buka:** '.$this->session->opened_at->format('d/m/Y H:i'))
            ->line("**Saldo Awal:** {$openingBalance}")
            ->line('**Lokasi:** '.($this->session->register_name ?? 'Kasir Utama'))
            ->action('Lihat Sesi Kasir', url("/pos/sessions/{$this->session->id}"))
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cashier_session_opened',
            'module' => 'pos',
            'title' => 'Sesi Kasir Dibuka',
            'message' => "Sesi kasir dibuka oleh {$this->session->cashier->name}",
            'action_url' => url("/pos/sessions/{$this->session->id}"),
            'session_id' => $this->session->id,
            'cashier_name' => $this->session->cashier->name,
            'opening_balance' => $this->session->opening_balance,
            'opened_at' => $this->session->opened_at->toIso8601String(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'cashier_session_opened',
            'module' => 'pos',
            'title' => 'Sesi Kasir Dibuka',
            'message' => "Sesi kasir dibuka oleh {$this->session->cashier->name}",
            'action_url' => url("/pos/sessions/{$this->session->id}"),
        ]);
    }
}
