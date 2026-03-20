<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'action', 'model_type', 'model_id',
        'description', 'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];

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
        ]);
    }
}
