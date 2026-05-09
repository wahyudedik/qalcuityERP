<?php

namespace App\Notifications;

use App\Models\FarmPlot;
use App\Models\NotificationPreference;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlantingScheduleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FarmPlot $plot,
        public string $cropName,
        public Carbon $scheduledDate
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'planting_schedule', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'planting_schedule', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'planting_schedule', 'push')) {
            $channels[] = 'broadcast';
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plantingDate = $this->scheduledDate->format('d/m/Y');

        return (new MailMessage)
            ->subject("🌱 Jadwal Tanam: {$this->cropName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Jadwal tanam untuk **{$this->cropName}** di lahan **{$this->plot->name}** akan dimulai.")
            ->line("**Tanggal Tanam:** {$plantingDate}")
            ->line("**Luas Lahan:** {$this->plot->area} {$this->plot->area_unit}")
            ->line("**Lokasi:** {$this->plot->location}")
            ->action('Lihat Lahan', url("/agriculture/plots/{$this->plot->id}"))
            ->line('Siapkan bibit, pupuk, dan peralatan tanam.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'planting_schedule',
            'module' => 'agriculture',
            'title' => 'Jadwal Tanam',
            'message' => "Jadwal tanam {$this->cropName} di lahan {$this->plot->name}",
            'action_url' => url("/agriculture/plots/{$this->plot->id}"),
            'plot_id' => $this->plot->id,
            'plot_name' => $this->plot->name,
            'crop_name' => $this->cropName,
            'scheduled_date' => $this->scheduledDate->format('Y-m-d'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'planting_schedule',
            'module' => 'agriculture',
            'title' => 'Jadwal Tanam',
            'message' => "Jadwal tanam {$this->cropName} di lahan {$this->plot->name}",
            'action_url' => url("/agriculture/plots/{$this->plot->id}"),
        ]);
    }
}
