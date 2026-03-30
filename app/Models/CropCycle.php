<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CropCycle extends Model
{
    protected $fillable = [
        'farm_plot_id', 'tenant_id', 'number', 'crop_name', 'crop_variety', 'season',
        'plan_prep_start', 'plan_plant_date', 'plan_harvest_date',
        'actual_prep_start', 'actual_plant_date', 'actual_harvest_date', 'actual_end_date',
        'phase', 'target_yield_qty', 'target_yield_unit', 'actual_yield_qty',
        'estimated_budget', 'actual_cost',
        'seed_quantity', 'seed_unit', 'seed_source', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'plan_prep_start'   => 'date',
            'plan_plant_date'   => 'date',
            'plan_harvest_date' => 'date',
            'actual_prep_start' => 'date',
            'actual_plant_date' => 'date',
            'actual_harvest_date'=> 'date',
            'actual_end_date'   => 'date',
            'target_yield_qty'  => 'decimal:3',
            'actual_yield_qty'  => 'decimal:3',
            'estimated_budget'  => 'decimal:2',
            'actual_cost'       => 'decimal:2',
            'seed_quantity'     => 'decimal:3',
        ];
    }

    public const PHASE_LABELS = [
        'planning'     => '📋 Perencanaan',
        'land_prep'    => '🚜 Persiapan Lahan',
        'planting'     => '🌱 Penanaman',
        'vegetative'   => '🌿 Vegetatif',
        'generative'   => '🌸 Generatif',
        'harvest'      => '🌾 Panen',
        'post_harvest' => '📦 Pasca Panen',
        'completed'    => '✅ Selesai',
        'cancelled'    => '❌ Dibatalkan',
    ];

    public const PHASE_COLORS = [
        'planning'     => 'gray',
        'land_prep'    => 'amber',
        'planting'     => 'blue',
        'vegetative'   => 'emerald',
        'generative'   => 'purple',
        'harvest'      => 'green',
        'post_harvest' => 'slate',
        'completed'    => 'green',
        'cancelled'    => 'red',
    ];

    public const PHASE_ORDER = [
        'planning' => 0, 'land_prep' => 1, 'planting' => 2,
        'vegetative' => 3, 'generative' => 4, 'harvest' => 5,
        'post_harvest' => 6, 'completed' => 7, 'cancelled' => 8,
    ];

    public function plot(): BelongsTo { return $this->belongsTo(FarmPlot::class, 'farm_plot_id'); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function activities(): HasMany { return $this->hasMany(FarmPlotActivity::class)->orderByDesc('date'); }

    public function phaseLabel(): string { return self::PHASE_LABELS[$this->phase] ?? $this->phase; }
    public function phaseColor(): string { return self::PHASE_COLORS[$this->phase] ?? 'gray'; }
    public function phaseIndex(): int { return self::PHASE_ORDER[$this->phase] ?? 0; }

    /** Progress percentage based on phase (0-100) */
    public function progressPercent(): float
    {
        if ($this->phase === 'cancelled') return 0;
        if ($this->phase === 'completed') return 100;
        $maxPhase = 7; // completed index
        return round(($this->phaseIndex() / $maxPhase) * 100, 0);
    }

    /** Duration in days from start to now or end */
    public function durationDays(): ?int
    {
        $start = $this->actual_prep_start ?? $this->actual_plant_date ?? $this->plan_prep_start;
        if (!$start) return null;
        $end = $this->actual_end_date ?? now();
        return $start->diffInDays($end);
    }

    /** Days until planned harvest */
    public function daysUntilHarvest(): ?int
    {
        $target = $this->plan_harvest_date;
        return $target && $target->isFuture() ? now()->diffInDays($target) : null;
    }

    /** Is harvest overdue? */
    public function isHarvestOverdue(): bool
    {
        return $this->plan_harvest_date
            && $this->plan_harvest_date->isPast()
            && !in_array($this->phase, ['harvest', 'post_harvest', 'completed', 'cancelled']);
    }

    /** Yield achievement percentage */
    public function yieldPercent(): float
    {
        return $this->target_yield_qty > 0
            ? round(($this->actual_yield_qty / $this->target_yield_qty) * 100, 1)
            : 0;
    }

    /** Budget usage percentage */
    public function budgetUsedPercent(): float
    {
        return $this->estimated_budget > 0
            ? round(($this->actual_cost / $this->estimated_budget) * 100, 1)
            : 0;
    }

    /** Cost per unit yield */
    public function costPerUnit(): ?float
    {
        return $this->actual_yield_qty > 0
            ? round($this->actual_cost / $this->actual_yield_qty, 2)
            : null;
    }

    /** Recalculate actual_cost and actual_yield from activities */
    public function recalculate(): void
    {
        $this->actual_cost = $this->activities()->sum('cost');
        $this->actual_yield_qty = $this->activities()
            ->where('activity_type', 'harvesting')
            ->sum('harvest_qty');
        $this->save();
    }

    /** Advance to next phase */
    public function advancePhase(?string $toPhase = null): bool
    {
        $order = array_keys(self::PHASE_ORDER);
        $currentIdx = $this->phaseIndex();

        if ($toPhase) {
            $targetIdx = self::PHASE_ORDER[$toPhase] ?? null;
            if ($targetIdx === null || $targetIdx <= $currentIdx) return false;
            $this->update(['phase' => $toPhase]);
            return true;
        }

        // Auto-advance to next sequential phase
        $nextIdx = $currentIdx + 1;
        if ($nextIdx >= count($order)) return false;
        $this->update(['phase' => $order[$nextIdx]]);
        return true;
    }

    /** Generate cycle number */
    public static function generateNumber(int $tenantId, string $plotCode): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;
        return 'CC-' . $plotCode . '-' . date('Y') . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
    }
}
