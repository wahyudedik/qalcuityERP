<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Auto-capture before/after values on Eloquent model events.
 *
 * Usage: add `use AuditsChanges;` to any model you want to audit.
 * Override $auditExclude to skip sensitive fields.
 */
trait AuditsChanges
{
    public static function bootAuditsChanges(): void
    {
        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty))
                return;

            $exclude = $model->auditExclude ?? ['password', 'remember_token', 'two_factor_secret'];
            $dirty = array_diff_key($dirty, array_flip($exclude));
            if (empty($dirty))
                return;

            $oldValues = [];
            $newValues = [];
            foreach ($dirty as $key => $newVal) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $newVal;
            }

            $label = class_basename(get_class($model));
            $name = $model->name ?? $model->number ?? $model->title ?? "#{$model->id}";

            ActivityLog::record(
                action: strtolower($label) . '_updated',
                description: "{$label} {$name} diperbarui",
                model: $model,
                oldValues: $oldValues,
                newValues: $newValues,
            );

            // TASK-022: Send notification for critical changes
            $model->notifyCriticalChanges($model, $oldValues, $newValues, strtolower($label) . '_updated');

            // Evaluate gamification achievements
            $user = Auth::user();
            if ($user) {
                \App\Services\GamificationService::evaluateAchievements($user, static::class, 'updated');
            }
        });

        static::deleted(function ($model) {
            $exclude = $model->auditExclude ?? ['password', 'remember_token', 'two_factor_secret'];
            $snapshot = array_diff_key($model->toArray(), array_flip($exclude));

            $label = class_basename(get_class($model));
            $name = $model->name ?? $model->number ?? $model->title ?? "#{$model->id}";

            ActivityLog::record(
                action: strtolower($label) . '_deleted',
                description: "{$label} {$name} dihapus",
                model: $model,
                oldValues: $snapshot,
                newValues: [],
            );

            // Evaluate gamification achievements
            $user = Auth::user();
            if ($user) {
                \App\Services\GamificationService::evaluateAchievements($user, static::class, 'deleted');
            }
        });

        static::created(function ($model) {
            $exclude = $model->auditExclude ?? ['password', 'remember_token', 'two_factor_secret'];
            $snapshot = array_diff_key($model->toArray(), array_flip($exclude));

            $label = class_basename(get_class($model));
            $name = $model->name ?? $model->number ?? $model->title ?? "#{$model->id}";

            ActivityLog::record(
                action: strtolower($label) . '_created',
                description: "{$label} {$name} dibuat",
                model: $model,
                oldValues: [],
                newValues: $snapshot,
            );

            // Evaluate gamification achievements
            $user = Auth::user();
            if ($user) {
                \App\Services\GamificationService::evaluateAchievements($user, static::class, 'created');
            }
        });
    }

    /**
     * Fields to exclude from audit snapshots.
     * Override in model: protected array $auditExclude = ['secret_field'];
     */
    public function getAuditExcludeAttribute(): array
    {
        return $this->auditExclude ?? ['password', 'remember_token', 'two_factor_secret'];
    }

    /**
     * TASK-022: Send notification for critical audit changes.
     * 
     * Triggers notifications to admins when sensitive models or fields are modified.
     */
    protected function notifyCriticalChanges($model, array $oldValues, array $newValues, string $action): void
    {
        // Only notify for critical models
        $criticalModels = [
            'User',
            'Role',
            'Permission',
            'Tenant',
            'BankAccount',
            'Invoice',
            'Payment'
        ];

        $modelClass = class_basename(get_class($model));

        if (!in_array($modelClass, $criticalModels)) {
            return;
        }

        // Check for sensitive field changes
        $sensitiveFields = ['password', 'role', 'permissions', 'is_active', 'email', 'status'];
        $changedFields = array_keys($newValues);
        $hasSensitiveChange = !empty(array_intersect($sensitiveFields, $changedFields));

        if (!$hasSensitiveChange) {
            return;
        }

        // Get latest activity log
        $latestLog = \App\Models\ActivityLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('action', $action)
            ->latest()
            ->first();

        if (!$latestLog) {
            return;
        }

        // Determine priority
        $priority = 'high';
        if (in_array($modelClass, ['User', 'Role', 'Permission'])) {
            $priority = 'critical';
        }

        // Get admins — scoped to same tenant as the model if possible
        $tenantId = $model->tenant_id ?? null;
        $adminQuery = \App\Models\User::where(function ($q) {
            $q->where('role', 'admin')->orWhere('role', 'manager');
        });
        if ($tenantId) {
            $adminQuery->where('tenant_id', $tenantId);
        }
        $admins = $adminQuery->get();

        // Send notifications
        foreach ($admins as $admin) {
            try {
                $admin->notify(new \App\Notifications\CriticalAuditChange($latestLog, $priority));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send critical audit notification', [
                    'user_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
