<?php

namespace App\Notifications;

use App\Models\ProjectTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProjectTask $task) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'task_assigned', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'task_assigned', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'task_assigned', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tugas Baru Ditugaskan: {$this->task->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Anda telah ditugaskan untuk tugas baru: **{$this->task->name}**")
            ->line("**Proyek:** {$this->task->project->name}")
            ->line("**Prioritas:** " . ucfirst($this->task->priority))
            ->line("**Deadline:** " . $this->task->due_date->format('d/m/Y'))
            ->line("**Deskripsi:** {$this->task->description}")
            ->action('Lihat Tugas', url("/projects/tasks/{$this->task->id}"))
            ->line('Segera kerjakan tugas ini sesuai deadline yang ditentukan.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'module' => 'project',
            'title' => 'Tugas Baru Ditugaskan',
            'message' => "Anda ditugaskan: {$this->task->name}",
            'action_url' => url("/projects/tasks/{$this->task->id}"),
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'project_name' => $this->task->project->name,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date->format('Y-m-d'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'task_assigned',
            'module' => 'project',
            'title' => 'Tugas Baru Ditugaskan',
            'message' => "Anda ditugaskan: {$this->task->name}",
            'action_url' => url("/projects/tasks/{$this->task->id}"),
        ]);
    }
}
