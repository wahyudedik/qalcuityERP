<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class MaterialShortageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public WorkOrder $workOrder,
        public array $shortageItems
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'material_shortage', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'material_shortage', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'material_shortage', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("⚠️ Kekurangan Material untuk WO #{$this->workOrder->wo_number}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Work Order **#{$this->workOrder->wo_number}** tidak dapat diproses karena kekurangan material.")
            ->line("**Produk:** {$this->workOrder->product->name}")
            ->line("**Material yang kurang:**");

        foreach ($this->shortageItems as $item) {
            $mail->line("• **{$item['material_name']}**: Butuh {$item['required']} {$item['unit']}, Tersedia {$item['available']} {$item['unit']}");
        }

        return $mail
            ->action('Lihat Work Order', url("/manufacturing/work-orders/{$this->workOrder->id}"))
            ->line('Segera lakukan pembelian material atau penyesuaian stok.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'material_shortage',
            'module' => 'manufacturing',
            'title' => 'Kekurangan Material',
            'message' => "WO #{$this->workOrder->wo_number} kekurangan material produksi",
            'action_url' => url("/manufacturing/work-orders/{$this->workOrder->id}"),
            'work_order_id' => $this->workOrder->id,
            'wo_number' => $this->workOrder->wo_number,
            'shortage_items' => $this->shortageItems,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'material_shortage',
            'module' => 'manufacturing',
            'title' => 'Kekurangan Material',
            'message' => "WO #{$this->workOrder->wo_number} kekurangan material produksi",
            'action_url' => url("/manufacturing/work-orders/{$this->workOrder->id}"),
        ]);
    }
}
