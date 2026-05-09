<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $newUser,
        public string $plainPassword
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role = match ($this->newUser->role) {
            'manager' => 'Manager',
            'staff' => 'Staff',
            default => ucfirst($this->newUser->role),
        };

        return (new MailMessage)
            ->subject("Akun Anda di {$this->newUser->tenant->name} Telah Dibuat")
            ->greeting("Halo, {$this->newUser->name}!")
            ->line("Admin **{$this->newUser->tenant->name}** telah membuat akun untuk Anda di Qalcuity ERP.")
            ->line("**Email:** {$this->newUser->email}")
            ->line("**Password:** {$this->plainPassword}")
            ->line("**Role:** {$role}")
            ->action('Login Sekarang', url('/login'))
            ->line('Segera ganti password Anda setelah login pertama.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
