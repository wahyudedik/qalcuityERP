<?php

namespace App\Notifications;

use App\Models\GoodsReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class GoodsReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public GoodsReceipt $goodsReceipt) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'goods_received', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'goods_received', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'goods_received', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Penerimaan Barang #{$this->goodsReceipt->receipt_number} Telah Dicatat")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Penerimaan barang **#{$this->goodsReceipt->receipt_number}** telah berhasil dicatat.")
            ->line("**PO:** #{$this->goodsReceipt->purchaseOrder->po_number}")
            ->line("**Supplier:** {$this->goodsReceipt->purchaseOrder->supplier->name}")
            ->line("**Tanggal Terima:** " . $this->goodsReceipt->received_date->format('d/m/Y'))
            ->action('Lihat Penerimaan Barang', url("/purchasing/goods-receipts/{$this->goodsReceipt->id}"))
            ->line('Stok telah diperbarui di sistem.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'goods_received',
            'module' => 'purchasing',
            'title' => 'Penerimaan Barang Dicatat',
            'message' => "Barang dari PO #{$this->goodsReceipt->purchaseOrder->po_number} telah diterima",
            'action_url' => url("/purchasing/goods-receipts/{$this->goodsReceipt->id}"),
            'goods_receipt_id' => $this->goodsReceipt->id,
            'receipt_number' => $this->goodsReceipt->receipt_number,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'goods_received',
            'module' => 'purchasing',
            'title' => 'Penerimaan Barang Dicatat',
            'message' => "Barang dari PO #{$this->goodsReceipt->purchaseOrder->po_number} telah diterima",
            'action_url' => url("/purchasing/goods-receipts/{$this->goodsReceipt->id}"),
        ]);
    }
}
