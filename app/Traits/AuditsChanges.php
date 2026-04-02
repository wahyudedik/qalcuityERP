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
            if (empty($dirty)) return;

            $exclude = $model->auditExclude ?? ['password', 'remember_token', 'two_factor_secret'];
            $dirty   = array_diff_key($dirty, array_flip($exclude));
            if (empty($dirty)) return;

            $oldValues = [];
            $newValues = [];
            foreach ($dirty as $key => $newVal) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $newVal;
            }

            $label = class_basename(get_class($model));
            $name  = $model->name ?? $model->number ?? $model->title ?? "#{$model->id}";

            ActivityLog::record(
                action: strtolower($label) . '_updated',
                description: "{$label} {$name} diperbarui",
                model: $model,
                oldValues: $oldValues,
                newValues: $newValues,
            );

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
            $name  = $model->name ?? $model->number ?? $model->title ?? "#{$model->id}";

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
            $name  = $model->name ?? $model->number ?? $model->title ?? "#{$model->id}";

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
}
