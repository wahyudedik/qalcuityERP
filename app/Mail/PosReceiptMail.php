<?php

namespace App\Mail;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PosReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public SalesOrder $order;
    public string $storeName;
    public string $storeAddress;
    public string $footerText;

    public function __construct(SalesOrder $order)
    {
        $this->order       = $order->load(['items.product', 'customer', 'user']);
        $this->storeName   = $order->tenant?->name ?? config('app.name', 'Qalcuity ERP');
        $this->storeAddress = '';
        $this->footerText  = 'Terima kasih atas kunjungan Anda!';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Struk Pembelian #' . $this->order->number . ' — ' . $this->storeName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.pos-receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
