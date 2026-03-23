<?php

namespace App\Notifications;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Reminder $reminder) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⏰ Pengingat: ' . $this->reminder->title)
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pengguna') . '!')
            ->line('Anda memiliki pengingat yang jatuh tempo:')
            ->line('**' . $this->reminder->title . '**')
            ->when($this->reminder->notes, fn($m) => $m->line($this->reminder->notes))
            ->line('Waktu: ' . $this->reminder->remind_at->format('d M Y H:i'))
            ->action('Lihat Pengingat', url('/reminders'))
            ->line('Terima kasih telah menggunakan Qalcuity ERP.');
    }
}
