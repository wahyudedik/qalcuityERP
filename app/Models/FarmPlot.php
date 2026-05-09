<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmPlot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'code', 'name', 'area_size', 'area_unit',
        'location', 'soil_type', 'irrigation_type', 'ownership', 'rent_cost',
        'current_crop', 'status', 'planted_at', 'expected_harvest',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'area_size' => 'decimal:3',
            'rent_cost' => 'decimal:2',
            'planted_at' => 'date',
            'expected_harvest' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public const STATUS_LABELS = [
        'idle' => 'Kosong / Bera',
        'preparing' => 'Persiapan Lahan',
        'planted' => 'Sudah Ditanam',
        'growing' => 'Masa Pertumbuhan',
        'ready_harvest' => 'Siap Panen',
        'harvesting' => 'Sedang Dipanen',
        'post_harvest' => 'Pasca Panen',
    ];

    public const STATUS_COLORS = [
        'idle' => 'gray',
        'preparing' => 'amber',
        'planted' => 'blue',
        'growing' => 'emerald',
        'ready_harvest' => 'green',
        'harvesting' => 'purple',
        'post_harvest' => 'slate',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(FarmPlotActivity::class)->orderByDesc('date');
    }

    public function cropCycles(): HasMany
    {
        return $this->hasMany(CropCycle::class)->orderByDesc('created_at');
    }

    public function activeCycle(): ?CropCycle
    {
        return $this->cropCycles()->whereNotIn('phase', ['completed', 'cancelled'])->first();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    /** Days since planted */
    public function daysSincePlanted(): ?int
    {
        return $this->planted_at ? $this->planted_at->diffInDays(now()) : null;
    }

    /** Days until expected harvest */
    public function daysUntilHarvest(): ?int
    {
        return $this->expected_harvest && $this->expected_harvest->isFuture()
            ? now()->diffInDays($this->expected_harvest)
            : null;
    }

    /** Is harvest overdue? */
    public function isHarvestOverdue(): bool
    {
        return $this->expected_harvest
            && $this->expected_harvest->isPast()
            && in_array($this->status, ['planted', 'growing', 'ready_harvest']);
    }

    /** Total cost from all activities */
    public function totalCost(): float
    {
        return (float) $this->activities()->sum('cost');
    }

    /** Total harvest qty */
    public function totalHarvest(): float
    {
        return (float) $this->activities()->where('activity_type', 'harvesting')->sum('harvest_qty');
    }

    /** Cost per unit harvested */
    public function costPerUnit(): ?float
    {
        $harvest = $this->totalHarvest();

        return $harvest > 0 ? round($this->totalCost() / $harvest, 2) : null;
    }
}
