<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedingSchedule extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'pond_id',
        'feeding_time',
        'feed_type',
        'feed_quantity',
        'feed_cost',
        'protein_content',
        'fat_content',
        'fiber_content',
        'moisture_content',
        'feeding_method',
        'weather_condition',
        'water_temperature',
        'notes',
        'fed_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'feeding_time' => 'datetime',
            'feed_quantity' => 'decimal:2',
            'feed_cost' => 'decimal:2',
            'protein_content' => 'decimal:2',
            'fat_content' => 'decimal:2',
            'fiber_content' => 'decimal:2',
            'moisture_content' => 'decimal:2',
            'water_temperature' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(AquaculturePond::class, 'pond_id');
    }

    public function fedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fed_by');
    }

    /**
     * Scope: Today's feedings
     */
    public function scopeToday($query)
    {
        return $query->whereDate('feeding_time', today());
    }

    /**
     * Scope: Scheduled feedings
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope: Completed feedings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
