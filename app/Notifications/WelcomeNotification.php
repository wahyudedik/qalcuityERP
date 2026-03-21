<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Selamat datang di Qalcuity ERP 🎉')
            ->greeting("Halo, {$this->user->name}!")
            ->line("Akun Anda untuk **{$this->user->tenant->name}** telah berhasil dibuat.")
            ->line('Anda mendapatkan akses **trial gratis 14 hari** ke semua fitur Qalcuity ERP.')
            ->action('Mulai Sekarang', url('/dashboard'))
            ->line('Jika ada pertanyaan, balas email ini atau hubungi kami via WhatsApp.')
            ->salutation('Salam, Tim Qalcuity ERP');
    }
}
