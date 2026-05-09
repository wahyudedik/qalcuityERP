<?php

namespace App\Notifications\Healthcare;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification
{
    use Queueable;

    protected $appointment;

    protected $channel;

    public function __construct(Appointment $appointment, string $channel = 'email')
    {
        $this->appointment = $appointment;
        $this->channel = $channel;
    }

    public function via($notifiable): array
    {
        $channels = [];

        if ($this->channel === 'email' || $this->channel === 'all') {
            $channels[] = 'mail';
        }

        $channels[] = 'database';

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $patient = $this->appointment->patient;
        $doctor = $this->appointment->doctor;
        $appointmentDate = $this->appointment->appointment_date;

        return (new MailMessage)
            ->subject('Appointment Reminder - '.config('app.name'))
            ->greeting('Hello '.($patient->name ?? 'Patient'))
            ->line('This is a friendly reminder about your upcoming appointment.')
            ->line('Date: '.$appointmentDate->format('l, F j, Y'))
            ->line('Time: '.$appointmentDate->format('g:i A'))
            ->line('Doctor: '.($doctor->name ?? 'TBA'))
            ->line('Department: '.($this->appointment->department ?? 'General'))
            ->action('View Appointment', route('healthcare.appointments.show', $this->appointment->id))
            ->line('Please arrive 15 minutes early.')
            ->line('If you need to reschedule or cancel, please contact us at least 24 hours in advance.')
            ->salutation('Best regards, '.config('app.name'));
    }

    public function toArray($notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'appointment_date' => $this->appointment->appointment_date->toISOString(),
            'doctor_name' => $this->appointment->doctor?->name,
            'department' => $this->appointment->department,
            'status' => $this->appointment->status,
            'message' => 'Reminder: You have an appointment on '.$this->appointment->appointment_date->format('M j, Y \a\t g:i A'),
        ];
    }
}
