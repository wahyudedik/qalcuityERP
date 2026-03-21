<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'action', 'model_type', 'model_id',
        'description', 'old_values', 'new_values', 'ip_address', 'user_agent',
        'is_ai_action', 'ai_tool_name',
    ];

    protected $casts = [
        'old_values'   => 'array',
        'new_values'   => 'array',
        'is_ai_action' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

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
