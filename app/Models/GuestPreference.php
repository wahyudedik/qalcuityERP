<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestPreference extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'guest_id',
        'category',
        'preference_key',
        'preference_value',
        'priority',
        'is_auto_applied',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_auto_applied' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Scope to get high priority preferences
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 3);
    }

    /**
     * Scope to get auto-applied preferences
     */
    public function scopeAutoApplied($query)
    {
        return $query->where('is_auto_applied', true);
    }

    /**
     * Mark this preference as used
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
