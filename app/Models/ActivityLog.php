<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'is_ai_action',
        'ai_tool_name',
        'rolled_back_at',
        'rolled_back_by',
    ];

    protected $casts = [
        'old_values'     => 'array',
        'new_values'     => 'array',
        'is_ai_action'   => 'boolean',
        'rolled_back_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function rolledBackByUser()
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    /**
     * Can this entry be rolled back?
     */
    public function isRollbackable(): bool
    {
        return config('audit.rollback_enabled', true)
            && $this->model_type
            && $this->model_id
            && !empty($this->old_values)
            && is_null($this->rolled_back_at)
            && !$this->is_ai_action
            && str_contains($this->action, 'updated');
    }

    /**
     * Rollback this change: restore old_values to the model.
     */
    public function rollback(int $userId): bool
    {
        if (!$this->isRollbackable()) {
            return false;
        }

        $modelClass = $this->model_type;
        $model = $modelClass::find($this->model_id);

        if (!$model) {
            return false;
        }

        $before = collect($model->toArray())
            ->only(array_keys($this->old_values))
            ->toArray();

        $model->update($this->old_values);

        // Mark this entry as rolled back
        $this->update([
            'rolled_back_at' => now(),
            'rolled_back_by' => $userId,
        ]);

        // Create a new audit entry for the rollback
        static::record(
            action: 'rollback',
            description: "Rollback: {$this->description} (log #{$this->id})",
            model: $model,
            oldValues: $before,
            newValues: $this->old_values,
        );

        return true;
    }

    public static function record(
        string $action,
        string $description,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        $user = auth()->user();
        static::create([
            'tenant_id'   => $user?->tenant_id,
            'user_id'     => $user?->id,
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->id,
            'description' => $description,
            'old_values'  => $oldValues ?: null,
            'new_values'  => $newValues ?: null,
            'ip_address'  => request()->ip(),
            'user_agent'  => substr(request()->userAgent() ?? '', 0, 255),
            'is_ai_action' => false,
        ]);
    }

    /**
     * Catat aksi yang dilakukan oleh AI (via tool call).
     */
    public static function recordAi(
        int    $tenantId,
        int    $userId,
        string $toolName,
        string $description,
        array  $args = [],
        array  $result = []
    ): void {
        // Bersihkan data sensitif / terlalu besar dari args
        $cleanArgs = array_diff_key($args, array_flip(['password', 'token', 'secret']));

        // Simpan hanya field result yang relevan (bukan raw data besar)
        $cleanResult = array_intersect_key($result, array_flip(['status', 'message', 'data']));
        if (isset($cleanResult['data']) && is_array($cleanResult['data']) && count($cleanResult['data']) > 20) {
            $cleanResult['data'] = array_slice($cleanResult['data'], 0, 5);
            $cleanResult['data']['_truncated'] = true;
        }

        static::create([
            'tenant_id'    => $tenantId,
            'user_id'      => $userId,
            'action'       => 'ai_' . $toolName,
            'model_type'   => null,
            'model_id'     => null,
            'description'  => $description,
            'old_values'   => $cleanArgs ?: null,
            'new_values'   => $cleanResult ?: null,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr(request()->userAgent() ?? '', 0, 255),
            'is_ai_action' => true,
            'ai_tool_name' => $toolName,
        ]);
    }
}
