<?php

namespace App\Services\Security;

use App\Models\AuditLogEnhanced;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log security event
     */
    public function logEvent(array $data): AuditLogEnhanced
    {
        return AuditLogEnhanced::create([
            'tenant_id' => $data['tenant_id'],
            'user_id' => $data['user_id'] ?? null,
            'event_type' => $data['event_type'],
            'model_type' => $data['model_type'] ?? null,
            'model_id' => $data['model_id'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => $data['ip_address'] ?? Request::ip(),
            'user_agent' => $data['user_agent'] ?? Request::userAgent(),
            'device_type' => $data['device_type'] ?? $this->detectDeviceType(),
            'location' => $data['location'] ?? null,
            'success' => $data['success'] ?? true,
            'failure_reason' => $data['failure_reason'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'created_at' => now(),
        ]);
    }

    /**
     * Log login attempt
     */
    public function logLogin(int $tenantId, int $userId, bool $success, ?string $failureReason = null): void
    {
        $this->logEvent([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => $success ? 'login' : 'failed_login',
            'success' => $success,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Log logout
     */
    public function logLogout(int $tenantId, int $userId): void
    {
        $this->logEvent([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => 'logout',
        ]);
    }

    /**
     * Log CRUD operation
     */
    public function logCrudOperation(
        int $tenantId,
        int $userId,
        string $action,
        string $modelType,
        $modelId,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $this->logEvent([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => strtolower($action), // create, update, delete
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Log permission change
     */
    public function logPermissionChange(
        int $tenantId,
        int $userId,
        int $targetUserId,
        array $oldPermissions,
        array $newPermissions
    ): void {
        $this->logEvent([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => 'permission_change',
            'metadata' => [
                'target_user_id' => $targetUserId,
                'old_permissions' => $oldPermissions,
                'new_permissions' => $newPermissions,
            ],
        ]);
    }

    /**
     * Get audit logs with filters
     */
    public function getLogs(int $tenantId, array $filters = []): array
    {
        $query = AuditLogEnhanced::where('tenant_id', $tenantId);

        // Apply filters
        if (! empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (! empty($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (! empty($filters['model_id'])) {
            $query->where('model_id', $filters['model_id']);
        }

        if (isset($filters['success'])) {
            $query->where('success', $filters['success']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $tenantId, int $userId, string $period = '7 days'): array
    {
        $startDate = now()->sub($period);

        $events = AuditLogEnhanced::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->get()
            ->pluck('count', 'event_type')
            ->toArray();

        return [
            'total_events' => array_sum($events),
            'events_by_type' => $events,
            'period' => $period,
        ];
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDeviceType(): string
    {
        $userAgent = Request::userAgent();

        if (! $userAgent) {
            return 'desktop';
        }

        if (stripos($userAgent, 'Mobile') !== false) {
            return 'mobile';
        } elseif (stripos($userAgent, 'Tablet') !== false) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Export audit logs to CSV
     */
    public function exportToCsv(int $tenantId, array $filters = []): string
    {
        $logs = $this->getLogs($tenantId, array_merge($filters, ['per_page' => 10000]));

        $csv = "ID,User ID,Event Type,Model,Model ID,IP Address,Success,Created At\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%d,%s,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->user_id ?? 0,
                $log->event_type,
                $log->model_type ?? '',
                $log->model_id ?? '',
                $log->ip_address ?? '',
                $log->success ? 'Yes' : 'No',
                $log->created_at->format('Y-m-d H:i:s')
            );
        }

        return $csv;
    }
}
