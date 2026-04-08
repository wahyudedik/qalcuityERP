<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingSupplyUsage extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'housekeeping_supply_id',
        'housekeeping_task_id',
        'room_id',
        'quantity_used',
        'notes',
        'used_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity_used' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function housekeepingSupply(): BelongsTo
    {
        return $this->belongsTo(HousekeepingSupply::class);
    }

    public function housekeepingTask(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTask::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Boot method to handle usage logic
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($usage) {
            // Reduce inventory
            $usage->housekeepingSupply->adjustQuantity(-$usage->quantity_used);
        });

        static::deleted(function ($usage) {
            // Restore inventory
            $usage->housekeepingSupply->adjustQuantity($usage->quantity_used);
        });
    }
}
