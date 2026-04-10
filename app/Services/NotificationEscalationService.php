<?php

namespace App\Services;

use App\Models\ErpNotification;
use App\Models\NotificationEscalation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * NotificationEscalationService - Handle automatic escalation of unread notifications.
 * 
 * Escalation levels:
 * - Level 1: User → Manager (after 30 minutes)
 * - Level 2: Manager → Admin (after 1 hour)
 * - Level 3: Admin → Super Admin (after 2 hours)
 */
class NotificationEscalationService
{
    /**
     * Create escalation rule for a notification.
     * 
     * @param ErpNotification $notification The notification to monitor
     * @param int $userId The user who should read it
     * @param array $rules Escalation rules [minutes => escalation_level]
     * @return void
     */
    public function createEscalation(ErpNotification $notification, int $userId, array $rules = []): void
    {
        // Default escalation rules
        if (empty($rules)) {
            $rules = [
                30 => 1,  // After 30 minutes → Level 1
                90 => 2,  // After 90 minutes → Level 2
                210 => 3, // After 3.5 hours → Level 3
            ];
        }

        $user = User::find($userId);
        if (!$user) {
            return;
        }

        foreach ($rules as $minutes => $level) {
            $escalationTarget = $this->getEscalationTarget($user->tenant_id, $level);

            if (!$escalationTarget) {
                continue;
            }

            NotificationEscalation::create([
                'tenant_id' => $user->tenant_id,
                'notification_id' => $notification->id,
                'from_user_id' => $userId,
                'to_user_id' => $escalationTarget->id,
                'escalation_level' => $level,
                'reason' => "Notifikasi tidak dibaca dalam {$minutes} menit",
                'minutes_until_escalation' => $minutes,
                'escalated_at' => $notification->created_at->addMinutes($minutes),
            ]);
        }
    }

    /**
     * Process pending escalations and send notifications.
     * 
     * @return int Number of escalations processed
     */
    public function processEscalations(): int
    {
        $count = 0;

        // Get escalations that are due (escalated_at <= now) and not read
        $escalations = NotificationEscalation::overdue()
            ->with(['notification', 'fromUser', 'toUser'])
            ->get();

        foreach ($escalations as $escalation) {
            try {
                $this->sendEscalationNotification($escalation);
                $count++;
            } catch (\Throwable $e) {
                Log::error("Failed to process escalation #{$escalation->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Send escalation notification to the target user.
     */
    protected function sendEscalationNotification(NotificationEscalation $escalation): void
    {
        $notification = $escalation->notification;
        $fromUser = $escalation->fromUser;
        $toUser = $escalation->toUser;

        // Create new notification for escalation target
        ErpNotification::create([
            'tenant_id' => $escalation->tenant_id,
            'user_id' => $toUser->id,
            'type' => 'escalation_level_' . $escalation->escalation_level,
            'module' => $notification->module ?? 'system',
            'title' => "⚠️ Eskalasi: {$notification->title}",
            'body' => "Notifikasi dari {$fromUser->name} perlu perhatian Anda: {$notification->body}",
            'data' => [
                'original_notification_id' => $notification->id,
                'escalation_id' => $escalation->id,
                'escalation_level' => $escalation->escalation_level,
                'from_user_id' => $fromUser->id,
                'from_user_name' => $fromUser->name,
                'original_type' => $notification->type,
            ],
        ]);

        Log::info("Escalation notification sent: Level {$escalation->escalation_level} to {$toUser->name}");
    }

    /**
     * Get escalation target user based on level.
     * 
     * @param int $tenantId Tenant ID
     * @param int $level Escalation level (1=Manager, 2=Admin, 3=Super Admin)
     * @return User|null
     */
    protected function getEscalationTarget(int $tenantId, int $level): ?User
    {
        return match ($level) {
            1 => User::where('tenant_id', $tenantId)
                ->where('role', 'manager')
                ->first(),
            2 => User::where('tenant_id', $tenantId)
                ->where('role', 'admin')
                ->first(),
            3 => User::where('tenant_id', $tenantId)
                ->where('role', 'super_admin')
                ->first(),
            default => null,
        };
    }

    /**
     * Check if a notification has been escalated.
     */
    public function hasBeenEscalated(int $notificationId): bool
    {
        return NotificationEscalation::where('notification_id', $notificationId)->exists();
    }

    /**
     * Get all escalations for a notification.
     */
    public function getEscalations(int $notificationId)
    {
        return NotificationEscalation::where('notification_id', $notificationId)
            ->with(['fromUser', 'toUser'])
            ->orderBy('escalation_level')
            ->get();
    }

    /**
     * Mark escalation as read when original notification is read.
     */
    public function markEscalationsRead(int $notificationId): void
    {
        NotificationEscalation::where('notification_id', $notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get escalation statistics for a tenant.
     */
    public function getStatistics(int $tenantId): array
    {
        return [
            'total_escalations' => NotificationEscalation::where('tenant_id', $tenantId)->count(),
            'pending_escalations' => NotificationEscalation::where('tenant_id', $tenantId)
                ->where('escalated_at', '>', now())
                ->count(),
            'overdue_escalations' => NotificationEscalation::where('tenant_id', $tenantId)
                ->overdue()
                ->count(),
            'resolved_escalations' => NotificationEscalation::where('tenant_id', $tenantId)
                ->whereNotNull('read_at')
                ->count(),
            'level_1' => NotificationEscalation::where('tenant_id', $tenantId)
                ->where('escalation_level', 1)
                ->count(),
            'level_2' => NotificationEscalation::where('tenant_id', $tenantId)
                ->where('escalation_level', 2)
                ->count(),
            'level_3' => NotificationEscalation::where('tenant_id', $tenantId)
                ->where('escalation_level', 3)
                ->count(),
        ];
    }
}
