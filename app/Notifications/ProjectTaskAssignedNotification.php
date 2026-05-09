<?php

namespace App\Notifications;

use App\Models\ProjectTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectTaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ProjectTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->task->project;
        $due = $this->task->due_date?->format('d M Y') ?? 'Tidak ditentukan';
        $assigner = auth()->user()?->name ?? 'Manajer';

        return (new MailMessage)
            ->subject("📋 Task Baru: {$this->task->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Anda mendapat tugas baru di proyek **{$project?->name}**:")
            ->line("**Task:** {$this->task->name}")
            ->when($this->task->description, fn ($m) => $m->line("**Deskripsi:** {$this->task->description}"))
            ->line("**Deadline:** {$due}")
            ->line("**Bobot:** {$this->task->weight}%")
            ->action('Lihat Proyek', url("/projects/{$project?->id}"))
            ->line('Selesaikan task tepat waktu untuk menjaga progres proyek.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
