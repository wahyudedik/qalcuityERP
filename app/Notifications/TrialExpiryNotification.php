<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * TASK 8.9: Notification for trial expiry
 * Sent at 7 days, 3 days, and 1 day before trial ends
 */
class TrialExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tenant $tenant,
        public int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return $notifiable->getNotificationChannels(static::class);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $urgency = $this->daysRemaining <= 1 ? 'URGENT' : '';
        $subject = $urgency ? "[$urgency] " : '';
        $subject .= "Masa Trial Anda Akan Berakhir dalam {$this->daysRemaining} Hari";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Masa trial Qalcuity ERP untuk **{$this->tenant->name}** akan berakhir dalam **{$this->daysRemaining} hari**.");

        if ($this->daysRemaining <= 1) {
            $message->line('⚠️ **Ini adalah pengingat terakhir!** Setelah masa trial berakhir, akses Anda ke sistem akan dibatasi.');
        } else {
            $message->line('Jangan sampai kehilangan akses ke data bisnis Anda yang berharga.');
        }

        $message->line('Upgrade sekarang untuk terus menggunakan semua fitur Qalcuity ERP tanpa gangguan.')
            ->action('Upgrade Sekarang', route('subscription.index'))
            ->line('Jika Anda memiliki pertanyaan, tim kami siap membantu Anda.')
            ->salutation('Salam hangat, Tim Qalcuity ERP');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trial_expiry',
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'days_remaining' => $this->daysRemaining,
            'trial_ends_at' => $this->tenant->trial_ends_at?->toDateTimeString(),
            'message' => "Masa trial akan berakhir dalam {$this->daysRemaining} hari. Upgrade sekarang untuk melanjutkan akses.",
            'action_url' => route('subscription.index'),
            'action_text' => 'Upgrade Sekarang',
            'urgency' => $this->daysRemaining <= 1 ? 'high' : ($this->daysRemaining <= 3 ? 'medium' : 'low'),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'trial_expiry',
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'days_remaining' => $this->daysRemaining,
            'message' => "Masa trial akan berakhir dalam {$this->daysRemaining} hari",
            'action_url' => route('subscription.index'),
            'urgency' => $this->daysRemaining <= 1 ? 'high' : 'medium',
        ];
    }
}
