<?php

namespace App\Notifications;

use App\Models\ConstructionProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProjectMilestoneNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ConstructionProject $project,
        public string $milestoneName,
        public float $progressPercentage
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'project_milestone', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'project_milestone', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'project_milestone', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🎯 Milestone Proyek Tercapai: {$this->milestoneName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Proyek **{$this->project->name}** telah mencapai milestone: **{$this->milestoneName}**")
            ->line("**Progress:** {$this->progressPercentage}%")
            ->line("**Lokasi:** {$this->project->location}")
            ->line("**Klien:** {$this->project->client_name}")
            ->action('Lihat Proyek', url("/construction/projects/{$this->project->id}"))
            ->line('Selamat! Lanjutkan ke tahap berikutnya.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_milestone',
            'module' => 'construction',
            'title' => 'Milestone Proyek Tercapai',
            'message' => "Proyek {$this->project->name} mencapai milestone: {$this->milestoneName}",
            'action_url' => url("/construction/projects/{$this->project->id}"),
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'milestone_name' => $this->milestoneName,
            'progress_percentage' => $this->progressPercentage,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'project_milestone',
            'module' => 'construction',
            'title' => 'Milestone Proyek Tercapai',
            'message' => "Proyek {$this->project->name} mencapai milestone: {$this->milestoneName}",
            'action_url' => url("/construction/projects/{$this->project->id}"),
        ]);
    }
}
