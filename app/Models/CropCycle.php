<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CropCycle extends Model
{
use HasFactory, BelongsToTenant;

    // Crop cycle status constants
    const STATUS_PLANNING     = 'planning';
    const STATUS_LAND_PREP    = 'land_prep';
    const STATUS_PLANTING     = 'planting';
    const STATUS_VEGETATIVE   = 'vegetative';
    const STATUS_GENERATIVE   = 'generative';
    const STATUS_HARVEST      = 'harvest';
    const STATUS_POST_HARVEST = 'post_harvest';
    const STATUS_COMPLETED    = 'completed';
    const STATUS_CANCELLED    = 'cancelled';

    const STATUSES = [
        self::STATUS_PLANNING,
        self::STATUS_LAND_PREP,
        self::STATUS_PLANTING,
        self::STATUS_VEGETATIVE,
        self::STATUS_GENERATIVE,
        self::STATUS_HARVEST,
        self::STATUS_POST_HARVEST,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    // Growth stage constants (used by growth_stage column)
    const PHASE_PLANTED          = 'planted';
    const PHASE_VEGETATIVE       = 'vegetative';
    const PHASE_FLOWERING        = 'flowering';
    const PHASE_FRUITING         = 'fruiting';
    const PHASE_READY_TO_HARVEST = 'ready_to_harvest';

    const PHASES = [
        self::PHASE_PLANTED,
        self::PHASE_VEGETATIVE,
        self::PHASE_FLOWERING,
        self::PHASE_FRUITING,
        self::PHASE_READY_TO_HARVEST,
    ];

    protected $fillable = [
        'tenant_id',
        'crop_name',
        'variety',
        'area_hectares',
        'field_location',
        'planting_date',
        'expected_harvest_date',
        'actual_harvest_date',
        'growth_stage',
        'days_to_harvest',
        'estimated_yield_tons',
        'actual_yield_tons',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'planting_date' => 'date',
        'expected_harvest_date' => 'date',
        'actual_harvest_date' => 'date',
        'area_hectares' => 'float',
        'estimated_yield_tons' => 'float',
        'actual_yield_tons' => 'float',
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function pestDetections()
    {
        return $this->hasMany(PestDetection::class);
    }
    public function irrigationSchedules()
    {
        return $this->hasMany(IrrigationSchedule::class);
    }

    public function getDaysSincePlantedAttribute(): int
    {
        return Carbon::parse($this->planting_date)->diffInDays(Carbon::now());
    }

    public function getDaysToHarvestAttribute(): int
    {
        if (!$this->expected_harvest_date)
            return 0;
        return max(0, Carbon::now()->diffInDays($this->expected_harvest_date, false));
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!$this->expected_harvest_date)
            return 0;
        $total = Carbon::parse($this->planting_date)->diffInDays($this->expected_harvest_date);
        $elapsed = $this->days_since_planted;
        return min(100, max(0, ($elapsed / $total) * 100));
    }

    public function updateGrowthStage(): void
    {
        $days = $this->days_since_planted;

        if ($days < 7) {
            $this->growth_stage = 'planted';
        } elseif ($days < 30) {
            $this->growth_stage = 'vegetative';
        } elseif ($days < 60) {
            $this->growth_stage = 'flowering';
        } elseif ($days < 80) {
            $this->growth_stage = 'fruiting';
        } else {
            $this->growth_stage = 'ready_to_harvest';
        }

        $this->save();
    }
}