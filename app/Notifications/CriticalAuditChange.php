<?php

namespace App\Notifications;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalAuditChange extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Critical models that should trigger notifications
     */
    protected array $criticalModels = [
        'App\Models\User',
        'App\Models\Role',
        'App\Models\Permission',
        'App\Models\Tenant',
        'App\Models\BankAccount',
        'App\Models\Invoice',
        'App\Models\Payment',
        'App\Models\Product',
    ];

    /**
     * Critical actions that should trigger notifications
     */
    protected array $criticalActions = [
        'deleted',
        'role_updated',
        'permission_updated',
        'password_updated',
    ];

    public function __construct(
        public ActivityLog $activityLog,
        public string $priority = 'high' // low, medium, high, critical
    ) {
    }

    /**
     * Determine if this change should trigger a notification
     */
    public function shouldNotify(): bool
    {
        $modelClass = $this->activityLog->model_type;
        $action = $this->activityLog->action;

        // Check if model is critical
        $isCriticalModel = in_array($modelClass, $this->criticalModels);

        // Check if action is critical
        $isCriticalAction = collect($this->criticalActions)->contains(fn($ca) => str_contains($action, $ca));

        // Check if sensitive fields changed
        $sensitiveFields = ['password', 'role', 'permissions', 'is_active', 'email', 'bank_account'];
        $changedFields = array_keys($this->activityLog->new_values ?? []);
        $hasSensitiveChange = !empty(array_intersect($sensitiveFields, $changedFields));

        return $isCriticalModel || $isCriticalAction || $hasSensitiveChange;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        // For critical priority, also send via other channels if configured
        if ($this->priority === 'critical') {
            // Could add: 'slack', 'webhook', etc.
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $log = $this->activityLog;
        $modelClass = $log->model_type ? class_basename($log->model_type) : 'Unknown';
        $changedFields = array_keys($log->new_values ?? []);
        $userName = $log->user?->name ?? 'System';

        $mailMessage = (new MailMessage)
            ->subject("🔍 Audit Alert: {$log->action} on {$modelClass}")
            ->greeting('Audit Trail Alert')
            ->line("A significant change has been detected in the system.")
            ->line("**Action:** {$log->action}")
            ->line("**Model:** {$modelClass} #{$log->model_id}")
            ->line("**User:** {$userName}")
            ->line("**Time:** {$log->created_at->format('d/m/Y H:i:s')}")
            ->line("**Fields Changed:** " . implode(', ', $changedFields))
            ->action('View Audit Trail', url('/audit'))
            ->line('Please review this change in the Audit Trail viewer.');

        // Add priority-specific styling
        if ($this->priority === 'critical') {
            $mailMessage->error();
        } elseif ($this->priority === 'high') {
            // For high priority, we can add a warning line instead
            $mailMessage->line('⚠️ This change requires attention.');
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $log = $this->activityLog;
        $modelClass = $log->model_type ? class_basename($log->model_type) : 'Unknown';

        return [
            'type' => 'critical_audit_change',
            'priority' => $this->priority,
            'activity_log_id' => $log->id,
            'action' => $log->action,
            'model_type' => $modelClass,
            'model_id' => $log->model_id,
            'user_name' => $log->user?->name ?? 'System',
            'description' => $log->description,
            'changed_fields' => array_keys($log->new_values ?? []),
            'created_at' => $log->created_at->toIso8601String(),
            'url' => "/audit#row-{$log->id}",
        ];
    }
}
