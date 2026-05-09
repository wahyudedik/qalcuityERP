<?php

namespace App\Notifications;

use App\Models\NotificationPreference;
use App\Models\PurchaseOrder;
use App\Traits\ChecksModuleStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderApprovedNotification extends Notification implements ShouldQueue
{
    use ChecksModuleStatus, Queueable;

    public function __construct(public PurchaseOrder $purchaseOrder) {}

    protected function getModuleKey(): ?string
    {
        return 'purchasing';
    }

    public function via(object $notifiable): array
    {
        // Respect user preferences
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'purchase_order_approved', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'purchase_order_approved', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'purchase_order_approved', 'push')) {
            $channels[] = 'broadcast';
        }

        $channels = $channels ?: ['database']; // fallback to in-app

        // Filter by module status - if module disabled, return empty array
        return $this->filterChannelsByModuleStatus($notifiable, $channels);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = 'Rp '.number_format($this->purchaseOrder->total, 0, ',', '.');

        return (new MailMessage)
            ->subject("Purchase Order #{$this->purchaseOrder->po_number} Telah Disetujui")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Purchase Order **#{$this->purchaseOrder->po_number}** telah disetujui.")
            ->line("**Supplier:** {$this->purchaseOrder->supplier->name}")
            ->line("**Total:** {$total}")
            ->line('**Tanggal:** '.$this->purchaseOrder->po_date->format('d/m/Y'))
            ->action('Lihat Purchase Order', url("/purchasing/purchase-orders/{$this->purchaseOrder->id}"))
            ->line('Silakan lanjutkan proses penerimaan barang.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'purchase_order_approved',
            'module' => 'purchasing',
            'title' => 'Purchase Order Disetujui',
            'message' => "PO #{$this->purchaseOrder->po_number} dari {$this->purchaseOrder->supplier->name} telah disetujui",
            'action_url' => url("/purchasing/purchase-orders/{$this->purchaseOrder->id}"),
            'purchase_order_id' => $this->purchaseOrder->id,
            'po_number' => $this->purchaseOrder->po_number,
            'total' => $this->purchaseOrder->total,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'purchase_order_approved',
            'module' => 'purchasing',
            'title' => 'Purchase Order Disetujui',
            'message' => "PO #{$this->purchaseOrder->po_number} dari {$this->purchaseOrder->supplier->name} telah disetujui",
            'action_url' => url("/purchasing/purchase-orders/{$this->purchaseOrder->id}"),
        ]);
    }
}
