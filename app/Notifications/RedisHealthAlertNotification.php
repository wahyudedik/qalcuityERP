<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

/**
 * Redis Health Alert Notification
 *
 * Sends alerts when Redis health monitoring detects issues with
 * Redis connectivity, authentication, or performance.
 */
class RedisHealthAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Alert title
     *
     * @var string
     */
    private string $title;

    /**
     * Alert details
     *
     * @var array
     */
    private array $details;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, array $details)
    {
        $this->title = $title;
        $this->details = $details;

        // Use database queue to avoid Redis dependency
        $this->onQueue('database');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Add email for critical alerts
        if (($this->details['severity'] ?? 'info') === 'critical') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $severity = $this->details['severity'] ?? 'info';
        $subject = "[{$severity}] {$this->title} - Qalcuity ERP";

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting("Redis Health Alert - {$severity}")
            ->line($this->title)
            ->line('Environment: ' . app()->environment());

        // Add specific details based on alert type
        if (isset($this->details['unhealthy_connections'])) {
            $mailMessage->line('Unhealthy Redis Connections:');
            foreach ($this->details['unhealthy_connections'] as $name => $connection) {
                $mailMessage->line("• {$name}: {$connection['message']} (Response: {$connection['response_time']}ms)");
            }
        }

        if (isset($this->details['auth_failed_connections'])) {
            $mailMessage->line('Authentication Failed Connections:');
            foreach ($this->details['auth_failed_connections'] as $name => $connection) {
                $mailMessage->line("• {$name}: {$connection['message']}");
            }
        }

        if (isset($this->details['recommendations'])) {
            $mailMessage->line('Recommendations:');
            foreach ($this->details['recommendations'] as $recommendation) {
                $mailMessage->line("• {$recommendation}");
            }
        }

        if (isset($this->details['action_required'])) {
            $mailMessage->line('Action Required:')
                ->line($this->details['action_required']);
        }

        if (isset($this->details['error'])) {
            $mailMessage->line('Error Details:')
                ->line($this->details['error']);
        }

        $mailMessage->line('Please check the Redis configuration and server status.')
            ->line('Timestamp: ' . now()->toDateTimeString());

        return $mailMessage;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'severity' => $this->details['severity'] ?? 'info',
            'environment' => app()->environment(),
            'details' => $this->details,
            'timestamp' => now()->toISOString(),
            'type' => 'redis_health_alert',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'severity' => $this->details['severity'] ?? 'info',
            'details' => $this->details,
            'timestamp' => now()->toISOString(),
        ];
    }
}
