<?php

namespace App\Notifications;

use App\Models\NotificationPreference;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'work_order_completed', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'work_order_completed', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'work_order_completed', 'push')) {
            $channels[] = 'broadcast';
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Work Order #{$this->workOrder->wo_number} Selesai")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Work Order **#{$this->workOrder->wo_number}** telah selesai diproduksi.")
            ->line("**Produk:** {$this->workOrder->product->name}")
            ->line("**Jumlah:** {$this->workOrder->quantity_produced} unit")
            ->line('**Tanggal Selesai:** '.$this->workOrder->completed_at->format('d/m/Y H:i'))
            ->action('Lihat Work Order', url("/manufacturing/work-orders/{$this->workOrder->id}"))
            ->line('Produk telah ditambahkan ke stok gudang.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'work_order_completed',
            'module' => 'manufacturing',
            'title' => 'Work Order Selesai',
            'message' => "WO #{$this->workOrder->wo_number} - {$this->workOrder->product->name} selesai diproduksi",
            'action_url' => url("/manufacturing/work-orders/{$this->workOrder->id}"),
            'work_order_id' => $this->workOrder->id,
            'wo_number' => $this->workOrder->wo_number,
            'product_name' => $this->workOrder->product->name,
            'quantity_produced' => $this->workOrder->quantity_produced,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'work_order_completed',
            'module' => 'manufacturing',
            'title' => 'Work Order Selesai',
            'message' => "WO #{$this->workOrder->wo_number} - {$this->workOrder->product->name} selesai diproduksi",
            'action_url' => url("/manufacturing/work-orders/{$this->workOrder->id}"),
        ]);
    }
}
