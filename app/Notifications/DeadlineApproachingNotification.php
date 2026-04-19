<?php

namespace App\Notifications;

use App\Models\ProjectTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DeadlineApproachingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProjectTask $task,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'deadline_approaching', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'deadline_approaching', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'deadline_approaching', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->task->due_date->format('d/m/Y');
        $urgency = $this->daysRemaining <= 1 ? '⚠️ URGENT' : '⏰';

        return (new MailMessage)
            ->subject("{$urgency} Deadline Mendekat: {$this->task->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Deadline tugas **{$this->task->name}** akan tiba dalam **{$this->daysRemaining} hari**.")
            ->line("**Proyek:** {$this->task->project->name}")
            ->line("**Deadline:** {$dueDate}")
            ->line("**Status:** " . ucfirst($this->task->status))
            ->line("**Progress:** {$this->task->progress}%")
            ->action('Lihat Tugas', url("/projects/tasks/{$this->task->id}"))
            ->line('Segera selesaikan tugas ini sebelum deadline!')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'deadline_approaching',
            'module' => 'project',
            'title' => 'Deadline Mendekat',
            'message' => "Tugas {$this->task->name} deadline dalam {$this->daysRemaining} hari",
            'action_url' => url("/projects/tasks/{$this->task->id}"),
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'days_remaining' => $this->daysRemaining,
            'due_date' => $this->task->due_date->format('Y-m-d'),
            'progress' => $this->task->progress,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'deadline_approaching',
            'module' => 'project',
            'title' => 'Deadline Mendekat',
            'message' => "Tugas {$this->task->name} deadline dalam {$this->daysRemaining} hari",
            'action_url' => url("/projects/tasks/{$this->task->id}"),
        ]);
    }
}
