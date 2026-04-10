<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatchLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'fishing_trip_id',
        'species_id',
        'grade_id',
        'quantity',
        'total_weight',
        'average_weight',
        'freshness_score',
        'caught_at',
        'latitude',
        'longitude',
        'catch_method',
        'depth',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'total_weight' => 'decimal:2',
            'average_weight' => 'decimal:2',
            'freshness_score' => 'decimal:2',
            'caught_at' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'depth' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class, 'fishing_trip_id');
    }

    /**
     * Alias for fishingTrip - used in controller queries
     */
    public function trip(): BelongsTo
    {
        return $this->fishingTrip();
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(FishSpecies::class, 'species_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(QualityGrade::class, 'grade_id');
    }
}
