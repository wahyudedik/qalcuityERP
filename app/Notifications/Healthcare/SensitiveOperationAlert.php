<?php

namespace App\Notifications\Healthcare;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SensitiveOperationAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;

    protected $operation;

    protected $resource;

    protected $timestamp;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, string $operation, $resource)
    {
        $this->user = $user;
        $this->operation = $operation;
        $this->resource = $resource;
        $this->timestamp = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resourceType = is_object($this->resource)
            ? get_class($this->resource)
            : $this->resource;

        $resourceId = is_object($this->resource) && isset($this->resource->id)
            ? $this->resource->id
            : 'N/A';

        return (new MailMessage)
            ->subject('🚨 Healthcare Security Alert: Sensitive Operation Detected')
            ->error()
            ->greeting('Critical Security Alert!')
            ->line('A sensitive operation has been performed in the healthcare system.')
            ->line('**Operation Details:**')
            ->line("• **User:** {$this->user->name} ({$this->user->email})")
            ->line("• **Role:** {$this->user->roles->pluck('name')->join(', ')}")
            ->line("• **Operation:** {$this->operation}")
            ->line("• **Resource Type:** {$resourceType}")
            ->line("• **Resource ID:** {$resourceId}")
            ->line("• **Timestamp:** {$this->timestamp->format('Y-m-d H:i:s')}")
            ->line('• **IP Address:** '.request()->ip())
            ->line('**Review Required:**')
            ->line('This operation requires immediate review by security team.')
            ->action('View Audit Log', url('/healthcare/admin/audit-logs'))
            ->line('If this operation is unauthorized, take immediate action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_type' => 'sensitive_operation',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'operation' => $this->operation,
            'resource_type' => is_object($this->resource) ? get_class($this->resource) : $this->resource,
            'resource_id' => is_object($this->resource) ? ($this->resource->id ?? null) : null,
            'timestamp' => $this->timestamp->toDateTimeString(),
            'ip_address' => request()->ip(),
            'severity' => 'critical',
        ];
    }
}
