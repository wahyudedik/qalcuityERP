<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array $invoices  [['number', 'customer', 'amount', 'days_overdue'], ...]
     */
    public function __construct(
        public readonly array  $invoices,
        public readonly string $tenantName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count       = count($this->invoices);
        $totalAmount = array_sum(array_column($this->invoices, 'amount'));

        $mail = (new MailMessage)
            ->subject("⚠️ {$count} Invoice Jatuh Tempo — {$this->tenantName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("{$count} invoice senilai **Rp " . number_format($totalAmount, 0, ',', '.') . "** belum dibayar dan sudah melewati jatuh tempo:");

        foreach (array_slice($this->invoices, 0, 8) as $inv) {
            $mail->line("• **#{$inv['number']}** — {$inv['customer']} | Rp " .
                number_format($inv['amount'], 0, ',', '.') .
                " | Terlambat {$inv['days_overdue']} hari");
        }

        if ($count > 8) {
            $mail->line('...dan ' . ($count - 8) . ' invoice lainnya.');
        }

        return $mail
            ->action('Lihat Invoice', url('/invoices'))
            ->line('Segera lakukan penagihan untuk menjaga arus kas bisnis Anda.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
