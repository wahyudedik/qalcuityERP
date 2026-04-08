<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use BelongsToTenant;
    /**
     * Thread-local AI context flag.
     * Set to true before any AI-driven CRUD operations so that record() tags
     * those entries as is_ai_action=true automatically.
     * Reset to false afterwards.
     */
    public static bool $aiContext = false;
    public static ?string $aiContextTool = null;

    /**
     * Begin an AI context scope — all record() calls within the closure are
     * tagged as AI actions without needing to call recordAi() separately.
     *
     * Usage:
     *   ActivityLog::withAiContext('my_tool', function () {
     *       $model->update([...]);  // AuditsChanges trait calls record() internally
     *   });
     */
    public static function withAiContext(string $toolName, callable $callback): mixed
    {
        self::$aiContext = true;
        self::$aiContextTool = $toolName;
        try {
            return $callback();
        } finally {
            self::$aiContext = false;
            self::$aiContextTool = null;
        }
    }

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
        'old_values' => 'array',
        'new_values' => 'array',
        'is_ai_action' => 'boolean',
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
     *
     * Returns an array: ['ok' => bool, 'message' => string, 'conflicts' => array]
     * 'conflicts' lists fields that were changed again after this entry was recorded,
     * i.e., the current value differs from new_values — indicating a later edit exists.
     */
    public function rollback(int $userId): array
    {
        if (!$this->isRollbackable()) {
            return ['ok' => false, 'message' => 'Entry ini tidak dapat di-rollback.', 'conflicts' => []];
        }

        $modelClass = $this->model_type;
        $model = $modelClass::find($this->model_id);

        if (!$model) {
            return ['ok' => false, 'message' => 'Record tidak ditemukan — mungkin sudah dihapus.', 'conflicts' => []];
        }

        // ── Conflict detection ────────────────────────────────────────
        // Compare current model values against what we recorded as new_values.
        // If they differ, a later edit has occurred — report the conflicts so the
        // caller can warn the user before overwriting.
        $current = collect($model->toArray())->only(array_keys($this->old_values))->toArray();
        $conflicts = [];

        foreach ($this->new_values ?? [] as $field => $recordedNewVal) {
            $currentVal = $current[$field] ?? null;
            // Cast to string for comparison to handle type mismatches (int vs "1")
            if ((string) $currentVal !== (string) $recordedNewVal) {
                $conflicts[$field] = [
                    'recorded_at_time_of_change' => $recordedNewVal,
                    'current_value' => $currentVal,
                ];
            }
        }

        $before = $current;

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

        return [
            'ok' => true,
            'message' => 'Rollback berhasil.',
            'conflicts' => $conflicts,
        ];
    }

    public static function record(
        string $action,
        string $description,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        $user = auth()->user();
        $isAi = static::$aiContext;
        $aiTool = static::$aiContextTool;
        static::create([
            'tenant_id' => $user?->tenant_id,
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
            'is_ai_action' => $isAi,
            'ai_tool_name' => $isAi ? $aiTool : null,
        ]);
    }

    /**
     * Catat aksi yang dilakukan oleh AI (via tool call).
     */
    public static function recordAi(
        int $tenantId,
        int $userId,
        string $toolName,
        string $description,
        array $args = [],
        array $result = []
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
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => 'ai_' . $toolName,
            'model_type' => null,
            'model_id' => null,
            'description' => $description,
            'old_values' => $cleanArgs ?: null,
            'new_values' => $cleanResult ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
            'is_ai_action' => true,
            'ai_tool_name' => $toolName,
        ]);
    }
}
