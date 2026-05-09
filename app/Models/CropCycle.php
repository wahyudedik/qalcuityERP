<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropCycle extends Model
{
    use BelongsToTenant, HasFactory;

    // Crop cycle status constants
    const STATUS_PLANNING = 'planning';

    const STATUS_LAND_PREP = 'land_prep';

    const STATUS_PLANTING = 'planting';

    const STATUS_VEGETATIVE = 'vegetative';

    const STATUS_GENERATIVE = 'generative';

    const STATUS_HARVEST = 'harvest';

    const STATUS_POST_HARVEST = 'post_harvest';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

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
    const PHASE_PLANTED = 'planted';

    const PHASE_VEGETATIVE = 'vegetative';

    const PHASE_FLOWERING = 'flowering';

    const PHASE_FRUITING = 'fruiting';

    const PHASE_READY_TO_HARVEST = 'ready_to_harvest';

    const PHASES = [
        self::PHASE_PLANTED,
        self::PHASE_VEGETATIVE,
        self::PHASE_FLOWERING,
        self::PHASE_FRUITING,
        self::PHASE_READY_TO_HARVEST,
    ];

    const PHASE_ORDER = [
        'planning' => 0,
        'land_prep' => 1,
        'planting' => 2,
        'vegetative' => 3,
        'generative' => 4,
        'harvest' => 5,
        'post_harvest' => 6,
        'completed' => 7,
        'cancelled' => 8,
    ];

    const PHASE_COLORS = [
        'planning' => 'gray',
        'land_prep' => 'yellow',
        'planting' => 'lime',
        'vegetative' => 'green',
        'generative' => 'emerald',
        'harvest' => 'orange',
        'post_harvest' => 'blue',
        'completed' => 'teal',
        'cancelled' => 'red',
    ];

    const PHASE_LABELS = [
        'planning' => 'Perencanaan',
        'land_prep' => 'Persiapan Lahan',
        'planting' => 'Penanaman',
        'vegetative' => 'Vegetatif',
        'generative' => 'Generatif',
        'harvest' => 'Panen',
        'post_harvest' => 'Pasca Panen',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
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
        'plan_harvest_date' => 'date',
        'area_hectares' => 'float',
        'estimated_yield_tons' => 'float',
        'actual_yield_tons' => 'float',
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plot()
    {
        return $this->belongsTo(FarmPlot::class, 'farm_plot_id');
    }

    public function pestDetections()
    {
        return $this->hasMany(PestDetection::class);
    }

    public function irrigationSchedules()
    {
        return $this->hasMany(IrrigationSchedule::class);
    }

    public function phaseColor(): string
    {
        return self::PHASE_COLORS[$this->phase ?? 'planning'] ?? 'gray';
    }

    public function phaseLabel(): string
    {
        return self::PHASE_LABELS[$this->phase ?? 'planning'] ?? ucfirst($this->phase ?? 'planning');
    }

    public function phaseIndex(): int
    {
        return self::PHASE_ORDER[$this->phase ?? 'planning'] ?? 0;
    }

    public function isHarvestOverdue(): bool
    {
        if (! $this->plan_harvest_date) {
            return false;
        }

        return $this->plan_harvest_date->isPast()
            && ! in_array($this->phase, ['harvest', 'post_harvest', 'completed', 'cancelled']);
    }

    public function daysUntilHarvest(): ?int
    {
        if (! $this->plan_harvest_date) {
            return null;
        }

        $days = (int) now()->diffInDays($this->plan_harvest_date, false);

        return $days > 0 ? $days : null;
    }

    public function getDaysSincePlantedAttribute(): int
    {
        return Carbon::parse($this->planting_date)->diffInDays(Carbon::now());
    }

    public function getDaysToHarvestAttribute(): int
    {
        if (! $this->expected_harvest_date) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->expected_harvest_date, false));
    }

    public function getProgressPercentageAttribute(): float
    {
        if (! $this->expected_harvest_date) {
            return 0;
        }
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
