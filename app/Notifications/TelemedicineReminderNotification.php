<?php

namespace App\Notifications;

use App\Models\Teleconsultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TelemedicineReminderNotification extends Notification
{
    use Queueable;

    protected $consultation;
    protected $recipientType;

    public function __construct(Teleconsultation $consultation, string $recipientType = 'patient')
    {
        $this->consultation = $consultation;
        $this->recipientType = $recipientType;
    }

    public function via($notifiable): array
    {
        $channels = ['mail', 'database'];

        // Add broadcast channel for push notifications if available
        if (method_exists($notifiable, 'routeNotificationForBroadcast')) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $doctor = $this->consultation->doctor;
        $patient = $this->consultation->patient;
        $meetingUrl = $this->consultation->meeting_url ?? route('healthcare.telemedicine.video-room', $this->consultation->id);

        if ($this->recipientType === 'doctor') {
            return (new MailMessage)
                ->subject('Reminder: Telemedicine Consultation in 30 Minutes')
                ->greeting('Hello Dr. ' . ($doctor->name ?? 'Doctor'))
                ->line('Your telemedicine consultation with ' . ($patient->full_name ?? 'Patient') . ' is scheduled in 30 minutes.')
                ->line('Scheduled Time: ' . $this->consultation->scheduled_time->format('l, F j, Y \a\t g:i A'))
                ->line('Duration: ' . $this->consultation->scheduled_duration . ' minutes')
                ->action('Join Consultation', $meetingUrl)
                ->line('Please ensure you have a stable internet connection.')
                ->line('Review patient medical records before the consultation.')
                ->salutation('Best regards, ' . config('app.name'));
        }

        // Patient notification
        return (new MailMessage)
            ->subject('Reminder: Your Telemedicine Consultation in 30 Minutes')
            ->greeting('Hello ' . ($patient->full_name ?? 'Patient'))
            ->line('Your telemedicine consultation with Dr. ' . ($doctor->name ?? 'Doctor') . ' is scheduled in 30 minutes.')
            ->line('Scheduled Time: ' . $this->consultation->scheduled_time->format('l, F j, Y \a\t g:i A'))
            ->line('Duration: ' . $this->consultation->scheduled_duration . ' minutes')
            ->action('Join Consultation', $meetingUrl)
            ->line('Please test your camera and microphone before joining.')
            ->line('Find a quiet and well-lit place for the consultation.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    public function toArray($notifiable): array
    {
        return [
            'consultation_id' => $this->consultation->id,
            'consultation_number' => $this->consultation->consultation_number,
            'scheduled_time' => $this->consultation->scheduled_time->toISOString(),
            'recipient_type' => $this->recipientType,
            'message' => $this->recipientType === 'doctor'
                ? 'Consultation with patient in 30 minutes'
                : 'Your consultation with doctor in 30 minutes',
        ];
    }

    public function toBroadcast($notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'consultation_id' => $this->consultation->id,
            'consultation_number' => $this->consultation->consultation_number,
            'scheduled_time' => $this->consultation->scheduled_time->toISOString(),
            'recipient_type' => $this->recipientType,
            'message' => $this->recipientType === 'doctor'
                ? 'Consultation with patient in 30 minutes'
                : 'Your consultation with doctor in 30 minutes',
        ]);
    }
}
