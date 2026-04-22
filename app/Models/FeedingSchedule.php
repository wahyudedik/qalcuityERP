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
        'feed_product_id',
        'schedule_date',
        'feeding_time',
        'planned_quantity',
        'actual_quantity',
        'status',
        'fed_by_user_id',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'feeding_time' => 'time',
            'planned_quantity' => 'decimal:2',
            'actual_quantity' => 'decimal:2',
            'completed_at' => 'datetime',
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

    public function feedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'feed_product_id');
    }

    public function fedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fed_by_user_id');
    }

    /**
     * Scope: Today's feedings
     */
    public function scopeToday($query)
    {
        return $query->whereDate('schedule_date', today());
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
