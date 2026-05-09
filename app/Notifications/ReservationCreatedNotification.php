<?php

namespace App\Notifications;

use App\Models\NotificationPreference;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Reservation $reservation) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'reservation_created', 'in_app')) {
            $channels[] = 'database';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'reservation_created', 'email')) {
            $channels[] = 'mail';
        }
        if (NotificationPreference::isEnabled($notifiable->id, 'reservation_created', 'push')) {
            $channels[] = 'broadcast';
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $checkIn = $this->reservation->check_in_date->format('d/m/Y');
        $checkOut = $this->reservation->check_out_date->format('d/m/Y');
        $total = 'Rp '.number_format($this->reservation->total_price, 0, ',', '.');

        return (new MailMessage)
            ->subject("Reservasi Baru: {$this->reservation->guest->name}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Reservasi baru telah dibuat untuk tamu **{$this->reservation->guest->name}**.")
            ->line("**Nomor Reservasi:** {$this->reservation->reservation_number}")
            ->line("**Kamar:** {$this->reservation->room->room_number} - {$this->reservation->room->room_type}")
            ->line("**Check-in:** {$checkIn}")
            ->line("**Check-out:** {$checkOut}")
            ->line("**Total:** {$total}")
            ->action('Lihat Reservasi', url("/hotel/reservations/{$this->reservation->id}"))
            ->line('Pastikan kamar siap sebelum check-in.')
            ->salutation('Salam, Qalcuity ERP');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_created',
            'module' => 'hotel',
            'title' => 'Reservasi Baru',
            'message' => "Reservasi baru dari {$this->reservation->guest->name}",
            'action_url' => url("/hotel/reservations/{$this->reservation->id}"),
            'reservation_id' => $this->reservation->id,
            'reservation_number' => $this->reservation->reservation_number,
            'guest_name' => $this->reservation->guest->name,
            'check_in_date' => $this->reservation->check_in_date->format('Y-m-d'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'reservation_created',
            'module' => 'hotel',
            'title' => 'Reservasi Baru',
            'message' => "Reservasi baru dari {$this->reservation->guest->name}",
            'action_url' => url("/hotel/reservations/{$this->reservation->id}"),
        ]);
    }
}
