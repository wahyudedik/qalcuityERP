<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = 'Rp ' . number_format($this->invoice->total_amount, 0, ',', '.');
        $due    = \Carbon\Carbon::parse($this->invoice->due_date)->translatedFormat('d F Y');

        return (new MailMessage)
            ->subject("Invoice {$this->invoice->number} dari {$this->invoice->tenant->name}")
            ->greeting("Halo, {$this->invoice->customer->name}!")
            ->line("Anda menerima invoice dari **{$this->invoice->tenant->name}**.")
            ->line("**Nomor Invoice:** {$this->invoice->number}")
            ->line("**Total:** {$amount}")
            ->line("**Jatuh Tempo:** {$due}")
            ->line('Invoice PDF terlampir pada email ini.')
            ->salutation("Terima kasih, {$this->invoice->tenant->name}");
    }
}
