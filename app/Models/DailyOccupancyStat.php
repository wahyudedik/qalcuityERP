<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyOccupancyStat extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'stat_date',
        'total_rooms',
        'available_rooms',
        'occupied_rooms',
        'out_of_order_rooms',
        'occupancy_percentage',
        'check_ins',
        'check_outs',
        'stay_over',
        'no_shows',
        'cancellations',
        'average_length_of_stay',
    ];

    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'total_rooms' => 'integer',
            'available_rooms' => 'integer',
            'occupied_rooms' => 'integer',
            'out_of_order_rooms' => 'integer',
            'occupancy_percentage' => 'decimal:2',
            'check_ins' => 'integer',
            'check_outs' => 'integer',
            'stay_over' => 'integer',
            'no_shows' => 'integer',
            'cancellations' => 'integer',
            'average_length_of_stay' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Calculate occupancy percentage
     */
    public function calculateOccupancyPercentage(): void
    {
        if ($this->total_rooms > 0) {
            $this->occupancy_percentage = ($this->occupied_rooms / $this->total_rooms) * 100;
        } else {
            $this->occupancy_percentage = 0;
        }

        $this->save();
    }

    /**
     * Get or create stat for date
     */
    public static function getOrCreateForDate(int $tenantId, \Carbon\Carbon $date): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'stat_date' => $date],
            ['total_rooms' => 0]
        );
    }
}
