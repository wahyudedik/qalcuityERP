<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CheckInReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Reservation $reservation) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'check_in_reminder', 'in_app')) {
            $channels[] = 'database';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'check_in_reminder', 'email')) {
            $channels[] = 'mail';
        }
        if (\App\Models\NotificationPreference::isEnabled($notifiable->id, 'check_in_reminder', 'push')) {
            $channels[] = 'broadcast';
        }
        
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $checkIn = $this->reservation->check_in_date->format('d/m/Y');

        return (new MailMessage)
            ->subject("⏰ Pengingat Check-in: {$this->reservation->guest->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tamu **{$this->reservation->guest->name}** dijadwalkan check-in hari ini.")
            ->line("**Nomor Reservasi:** {$this->reservation->reservation_number}")
            ->line("**Kamar:** {$this->reservation->room->room_number} - {$this->reservation->room->room_type}")
            ->line("**Tanggal Check-in:** {$checkIn}")
            ->action('Lihat Reservasi', url("/hotel/reservations/{$this->reservation->id}"))
            ->line('Pastikan kamar sudah siap dan bersih.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'check_in_reminder',
            'module' => 'hotel',
            'title' => 'Pengingat Check-in',
            'message' => "Tamu {$this->reservation->guest->name} check-in hari ini",
            'action_url' => url("/hotel/reservations/{$this->reservation->id}"),
            'reservation_id' => $this->reservation->id,
            'reservation_number' => $this->reservation->reservation_number,
            'guest_name' => $this->reservation->guest->name,
            'room_number' => $this->reservation->room->room_number,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'check_in_reminder',
            'module' => 'hotel',
            'title' => 'Pengingat Check-in',
            'message' => "Tamu {$this->reservation->guest->name} check-in hari ini",
            'action_url' => url("/hotel/reservations/{$this->reservation->id}"),
        ]);
    }
}
