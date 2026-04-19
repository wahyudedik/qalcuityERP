<?php

namespace App\Notifications;

use App\Models\CashierSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CashierSessionClosedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CashierSession $session) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'cashier_session_closed', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'cashier_session_closed', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'cashier_session_closed', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $closingBalance = 'Rp ' . number_format($this->session->closing_balance, 0, ',', '.');
        $totalSales = 'Rp ' . number_format($this->session->total_sales, 0, ',', '.');
        $variance = $this->session->closing_balance - $this->session->expected_balance;
        $varianceText = 'Rp ' . number_format(abs($variance), 0, ',', '.');

        $mail = (new MailMessage)
            ->subject("Sesi Kasir Ditutup - {$this->session->cashier->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Sesi kasir telah ditutup oleh **{$this->session->cashier->name}**.")
            ->line("**Waktu Tutup:** " . $this->session->closed_at->format('d/m/Y H:i'))
            ->line("**Total Penjualan:** {$totalSales}")
            ->line("**Saldo Akhir:** {$closingBalance}");

        if (abs($variance) > 0) {
            $varianceLabel = $variance > 0 ? 'Kelebihan' : 'Kekurangan';
            $mail->line("**{$varianceLabel}:** {$varianceText}");
        }

        return $mail
            ->action('Lihat Sesi Kasir', url("/pos/sessions/{$this->session->id}"))
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cashier_session_closed',
            'module' => 'pos',
            'title' => 'Sesi Kasir Ditutup',
            'message' => "Sesi kasir ditutup oleh {$this->session->cashier->name}",
            'action_url' => url("/pos/sessions/{$this->session->id}"),
            'session_id' => $this->session->id,
            'cashier_name' => $this->session->cashier->name,
            'total_sales' => $this->session->total_sales,
            'closing_balance' => $this->session->closing_balance,
            'closed_at' => $this->session->closed_at->toIso8601String(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'cashier_session_closed',
            'module' => 'pos',
            'title' => 'Sesi Kasir Ditutup',
            'message' => "Sesi kasir ditutup oleh {$this->session->cashier->name}",
            'action_url' => url("/pos/sessions/{$this->session->id}"),
        ]);
    }
}
