<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatchLog extends Model
{
    use HasFactory, BelongsToTenant;

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

    protected $casts = [
        'quantity' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'average_weight' => 'decimal:2',
        'freshness_score' => 'decimal:2',
        'caught_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'depth' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function fishingTrip()
    {
        return $this->belongsTo(FishingTrip::class);
    }

    public function species()
    {
        return $this->belongsTo(FishSpecies::class, 'species_id');
    }

    public function grade()
    {
        return $this->belongsTo(QualityGrade::class, 'grade_id');
    }

    public function freshnessAssessment()
    {
        return $this->hasOne(FreshnessAssessment::class);
    }

    public function getEstimatedValueAttribute(): float
    {
        $basePrice = $this->species?->market_price_per_kg ?? 0;
        $multiplier = $this->grade?->price_multiplier ?? 1.0;

        return $this->total_weight * $basePrice * $multiplier;
    }
}
