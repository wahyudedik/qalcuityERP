<?php

namespace App\Notifications\Healthcare;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AfterHoursAccessAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $request;
    protected $accessTime;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $request)
    {
        $this->user = $user;
        $this->request = $request;
        $this->accessTime = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = config('healthcare.notifications.alert_channels', ['mail', 'database']);

        // Add SMS if configured
        if (config('healthcare.security.alert_phone')) {
            $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $routeName = $this->request->route() ? $this->request->route()->getName() : 'N/A';

        return (new MailMessage)
            ->subject('🔒 Healthcare Security Alert: After-Hours Access Detected')
            ->error()
            ->greeting('Security Alert!')
            ->line('An after-hours access to the healthcare system has been detected.')
            ->line('**Access Details:**')
            ->line("• **User:** {$this->user->name} ({$this->user->email})")
            ->line("• **Role:** {$this->user->roles->pluck('name')->join(', ')}")
            ->line("• **Access Time:** {$this->accessTime->format('Y-m-d H:i:s')}")
            ->line("• **Route:** {$routeName}")
            ->line("• **URL:** {$this->request->fullUrl()}")
            ->line("• **IP Address:** {$this->request->ip()}")
            ->line("• **User Agent:** {$this->request->userAgent()}")
            ->line('**Action Required:**')
            ->line('Please verify if this access is authorized.')
            ->action('View Audit Log', url('/healthcare/admin/audit-logs'))
            ->line('If this access is suspicious, please investigate immediately.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_type' => 'after_hours_access',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'access_time' => $this->accessTime->toDateTimeString(),
            'route' => $this->request->route() ? $this->request->route()->getName() : null,
            'url' => $this->request->fullUrl(),
            'ip_address' => $this->request->ip(),
            'severity' => 'warning',
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        return "HEALTHCARE ALERT: After-hours access by {$this->user->name} at {$this->accessTime->format('H:i')}. Route: {$this->request->route()->getName()}. IP: {$this->request->ip()}. Verify immediately.";
    }
}
