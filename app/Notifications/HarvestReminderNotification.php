<?php

namespace App\Notifications;

use App\Models\CropCycle;
use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HarvestReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CropCycle $cropCycle,
        public int $daysUntilHarvest
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'harvest_reminder', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'harvest_reminder', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'harvest_reminder', 'push')) {
            $channels[] = 'broadcast';
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $harvestDate = $this->cropCycle->expected_harvest_date->format('d/m/Y');

        return (new MailMessage)
            ->subject("🌾 Pengingat Panen: {$this->cropCycle->crop_name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Waktu panen untuk **{$this->cropCycle->crop_name}** akan tiba dalam **{$this->daysUntilHarvest} hari**.")
            ->line("**Lahan:** {$this->cropCycle->plot->name}")
            ->line("**Tanggal Panen:** {$harvestDate}")
            ->line("**Luas:** {$this->cropCycle->plot->area} {$this->cropCycle->plot->area_unit}")
            ->action('Lihat Siklus Tanam', url("/agriculture/crop-cycles/{$this->cropCycle->id}"))
            ->line('Siapkan peralatan dan tenaga kerja untuk panen.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'harvest_reminder',
            'module' => 'agriculture',
            'title' => 'Pengingat Panen',
            'message' => "{$this->cropCycle->crop_name} akan dipanen dalam {$this->daysUntilHarvest} hari",
            'action_url' => url("/agriculture/crop-cycles/{$this->cropCycle->id}"),
            'crop_cycle_id' => $this->cropCycle->id,
            'crop_name' => $this->cropCycle->crop_name,
            'days_until_harvest' => $this->daysUntilHarvest,
            'expected_harvest_date' => $this->cropCycle->expected_harvest_date->format('Y-m-d'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'harvest_reminder',
            'module' => 'agriculture',
            'title' => 'Pengingat Panen',
            'message' => "{$this->cropCycle->crop_name} akan dipanen dalam {$this->daysUntilHarvest} hari",
            'action_url' => url("/agriculture/crop-cycles/{$this->cropCycle->id}"),
        ]);
    }
}
