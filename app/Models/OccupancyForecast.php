<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OccupancyForecast extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'room_type_id',
        'forecast_date',
        'total_rooms',
        'projected_booked',
        'projected_available',
        'projected_occupancy_rate',
        'projected_adr',
        'projected_revpar',
        'confidence_level',
        'factors',
    ];

    protected function casts(): array
    {
        return [
            'forecast_date' => 'date',
            'total_rooms' => 'integer',
            'projected_booked' => 'integer',
            'projected_available' => 'integer',
            'projected_occupancy_rate' => 'decimal:2',
            'projected_adr' => 'decimal:2',
            'projected_revpar' => 'decimal:2',
            'confidence_level' => 'decimal:2',
            'factors' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Calculate forecast accuracy after date passes
     */
    public function calculateAccuracy(int $actualBooked): float
    {
        if ($this->projected_booked == 0) {
            return 0;
        }

        $difference = abs($this->projected_booked - $actualBooked);
        $accuracy = 100 - (($difference / $this->total_rooms) * 100);

        return max(0, min(100, round($accuracy, 2)));
    }

    /**
     * Get occupancy rate as percentage string
     */
    public function getOccupancyRatePercentageAttribute(): string
    {
        return number_format($this->projected_occupancy_rate, 1) . '%';
    }
}
