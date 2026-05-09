<?php

namespace App\Notifications;

use App\Models\NotificationPreference;
use App\Models\TelecomInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TelecomInvoice $invoice,
        public int $daysUntilDue
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'invoice_due', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'invoice_due', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'invoice_due', 'push')) {
            $channels[] = 'broadcast';
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->invoice->due_date->format('d/m/Y');
        $amount = 'Rp '.number_format($this->invoice->total_amount, 0, ',', '.');

        return (new MailMessage)
            ->subject("⚠️ Tagihan Jatuh Tempo dalam {$this->daysUntilDue} Hari")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tagihan pelanggan **{$this->invoice->customer->name}** akan jatuh tempo dalam **{$this->daysUntilDue} hari**.")
            ->line("**Nomor Invoice:** {$this->invoice->invoice_number}")
            ->line("**Jumlah:** {$amount}")
            ->line("**Tanggal Jatuh Tempo:** {$dueDate}")
            ->action('Lihat Invoice', url("/telecom/invoices/{$this->invoice->id}"))
            ->line('Hubungi pelanggan untuk pembayaran.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice_due',
            'module' => 'telecom',
            'title' => 'Tagihan Jatuh Tempo',
            'message' => "Tagihan {$this->invoice->customer->name} jatuh tempo dalam {$this->daysUntilDue} hari",
            'action_url' => url("/telecom/invoices/{$this->invoice->id}"),
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'customer_name' => $this->invoice->customer->name,
            'amount' => $this->invoice->total_amount,
            'days_until_due' => $this->daysUntilDue,
            'due_date' => $this->invoice->due_date->format('Y-m-d'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'invoice_due',
            'module' => 'telecom',
            'title' => 'Tagihan Jatuh Tempo',
            'message' => "Tagihan {$this->invoice->customer->name} jatuh tempo dalam {$this->daysUntilDue} hari",
            'action_url' => url("/telecom/invoices/{$this->invoice->id}"),
        ]);
    }
}
