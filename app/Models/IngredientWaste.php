<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ingredient Waste Tracking
 */
class IngredientWaste extends Model
{
    protected $fillable = [
        'tenant_id',
        'inventory_item_id',
        'item_name',
        'quantity_wasted',
        'unit',
        'cost_per_unit',
        'total_waste_cost',
        'waste_type', // spoilage, over_production, preparation_error, expired, other
        'reason',
        'wasted_by',
        'wasted_at',
        'department', // kitchen, bar, storage
        'preventive_action',
    ];

    protected function casts(): array
    {
        return [
            'quantity_wasted' => 'decimal:3',
            'cost_per_unit' => 'decimal:2',
            'total_waste_cost' => 'decimal:2',
            'wasted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function wastedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wasted_by');
    }

    /**
     * Calculate waste cost
     */
    public static function calculateWasteCost(float $quantity, float $costPerUnit): float
    {
        return round($quantity * $costPerUnit, 2);
    }

    /**
     * Get waste type label
     */
    public function getWasteTypeLabel(): string
    {
        return match ($this->waste_type) {
            'spoilage' => 'Spoilage/Rusak',
            'over_production' => 'Over Production',
            'preparation_error' => 'Preparation Error',
            'expired' => 'Expired',
            'other' => 'Other',
            default => ucfirst($this->waste_type),
        };
    }

    /**
     * Scope by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('wasted_at', [$startDate, $endDate]);
    }

    /**
     * Scope by waste type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('waste_type', $type);
    }
}
