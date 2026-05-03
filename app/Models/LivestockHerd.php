<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Carbon|null $entry_date
 * @property Carbon|null $target_harvest_date
 */
class LivestockHerd extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'farm_plot_id',
        'code',
        'name',
        'animal_type',
        'breed',
        'initial_count',
        'current_count',
        'entry_date',
        'entry_age_days',
        'entry_weight_kg',
        'purchase_price',
        'status',
        'target_harvest_date',
        'target_weight_kg',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'target_harvest_date' => 'date',
            'entry_weight_kg' => 'decimal:3',
            'purchase_price' => 'decimal:2',
            'target_weight_kg' => 'decimal:3',
        ];
    }

    public const ANIMAL_TYPES = [
        'ayam_broiler' => 'Ayam Broiler',
        'ayam_layer' => 'Ayam Petelur',
        'sapi' => 'Sapi',
        'kambing' => 'Kambing/Domba',
        'bebek' => 'Bebek/Itik',
        'ikan' => 'Ikan',
        'babi' => 'Babi',
        'kelinci' => 'Kelinci',
        'lainnya' => 'Lainnya',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function plot(): BelongsTo
    {
        return $this->belongsTo(FarmPlot::class, 'farm_plot_id');
    }
    public function movements(): HasMany
    {
        return $this->hasMany(LivestockMovement::class)->orderByDesc('date');
    }
    public function healthRecords(): HasMany
    {
        return $this->hasMany(LivestockHealthRecord::class)->orderByDesc('date');
    }
    public function vaccinations(): HasMany
    {
        return $this->hasMany(LivestockVaccination::class)->orderBy('scheduled_date');
    }
    public function feedLogs(): HasMany
    {
        return $this->hasMany(LivestockFeedLog::class)->orderByDesc('date');
    }
    public function eggProductions(): HasMany
    {
        return $this->hasMany(PoultryEggProduction::class, 'livestock_herd_id');
    }
    public function flockPerformances(): HasMany
    {
        return $this->hasMany(PoultryFlockPerformance::class, 'livestock_herd_id');
    }

    public function animalLabel(): string
    {
        return self::ANIMAL_TYPES[$this->animal_type] ?? $this->animal_type;
    }

    /** Current age in days */
    public function ageDays(): ?int
    {
        return $this->entry_date
            ? $this->entry_date->diffInDays(now()) + $this->entry_age_days
            : null;
    }

    /** Mortality count */
    public function mortalityCount(): int
    {
        return (int) $this->movements()->whereIn('type', ['death', 'cull'])->sum('quantity');
    }

    /** Mortality rate percentage */
    public function mortalityRate(): float
    {
        return $this->initial_count > 0
            ? round(abs($this->mortalityCount()) / $this->initial_count * 100, 2)
            : 0;
    }

    /** Total sold */
    public function soldCount(): int
    {
        return abs((int) $this->movements()->where('type', 'sold')->sum('quantity'));
    }

    /** Total harvested */
    public function harvestedCount(): int
    {
        return abs((int) $this->movements()->where('type', 'harvested')->sum('quantity'));
    }

    /** Total revenue from sales + harvest */
    public function totalRevenue(): float
    {
        return (float) $this->movements()->whereIn('type', ['sold', 'harvested'])->sum('price_total');
    }

    /** Total cost (purchase + feed from activities) */
    public function totalCost(): float
    {
        $purchaseCost = (float) $this->purchase_price;
        $activityCost = $this->plot
            ? (float) FarmPlotActivity::where('farm_plot_id', $this->farm_plot_id)->sum('cost')
            : 0;
        return $purchaseCost + $activityCost;
    }

    /** Days until target harvest */
    public function daysUntilHarvest(): ?int
    {
        return $this->target_harvest_date && $this->target_harvest_date->isFuture()
            ? now()->diffInDays($this->target_harvest_date)
            : null;
    }

    /** Is harvest overdue? */
    public function isHarvestOverdue(): bool
    {
        return $this->target_harvest_date
            && $this->target_harvest_date->isPast()
            && $this->status === 'active';
    }

    // ─── Feed & FCR Calculations ──────────────────────────────────

    /** Total feed consumed (kg) */
    public function totalFeedKg(): float
    {
        return (float) $this->feedLogs()->sum('quantity_kg');
    }

    /** Total feed cost */
    public function totalFeedCost(): float
    {
        return (float) $this->feedLogs()->sum('cost');
    }

    /** Latest average body weight from feed logs */
    public function latestBodyWeight(): ?float
    {
        $latest = $this->feedLogs()->where('avg_body_weight_kg', '>', 0)->first();
        return $latest ? (float) $latest->avg_body_weight_kg : null;
    }

    /** Weight gain = current avg weight - entry weight */
    public function weightGain(): ?float
    {
        $current = $this->latestBodyWeight();
        if (!$current || $this->entry_weight_kg <= 0)
            return null;
        return round($current - (float) $this->entry_weight_kg, 3);
    }

    /**
     * Feed Conversion Ratio (FCR).
     * FCR = Total Feed (kg) / Total Weight Gain (kg)
     * Lower is better. Broiler target: 1.4-1.8
     */
    public function fcr(): ?float
    {
        $totalFeed = $this->totalFeedKg();
        $currentWeight = $this->latestBodyWeight();

        if ($totalFeed <= 0 || !$currentWeight || $this->entry_weight_kg <= 0)
            return null;

        $totalWeightGain = ($currentWeight - (float) $this->entry_weight_kg) * $this->current_count;

        return $totalWeightGain > 0 ? round($totalFeed / $totalWeightGain, 2) : null;
    }

    /** Daily feed consumption average (kg/day) */
    public function avgDailyFeed(): ?float
    {
        $days = $this->feedLogs()->distinct('date')->count('date');
        return $days > 0 ? round($this->totalFeedKg() / $days, 2) : null;
    }

    /** Feed cost per kg of weight gain */
    public function feedCostPerKgGain(): ?float
    {
        $currentWeight = $this->latestBodyWeight();
        if (!$currentWeight || $this->entry_weight_kg <= 0)
            return null;

        $totalWeightGain = ($currentWeight - (float) $this->entry_weight_kg) * $this->current_count;
        $totalFeedCost = $this->totalFeedCost();

        return $totalWeightGain > 0 ? round($totalFeedCost / $totalWeightGain, 0) : null;
    }

    /** Generate herd code */
    public static function generateCode(int $tenantId, string $type): string
    {
        $prefix = match ($type) {
            'ayam_broiler', 'ayam_layer' => 'FLK',
            'sapi' => 'HRD',
            'ikan' => 'PND',
            default => 'LST',
        };
        $count = self::where('tenant_id', $tenantId)->count() + 1;
        return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for active herds
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
