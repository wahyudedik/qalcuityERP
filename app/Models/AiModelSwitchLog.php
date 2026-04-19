<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AiModelSwitchLog extends Model
{
    use BelongsToTenant;
protected $fillable = [
        'from_model',
        'to_model',
        'reason',
        'error_message',
        'request_context',
        'triggered_by_tenant_id',
        'switched_at',
    ];

    protected $casts = [
        'switched_at' => 'datetime',
    ];

    /**
     * Scope to filter records switched within the last $days days.
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('switched_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to filter records by reason.
     */
    public function scopeByReason(Builder $query, string $reason): Builder
    {
        return $query->where('reason', $reason);
    }
}
