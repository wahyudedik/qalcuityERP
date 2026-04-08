<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingSupply extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'item_name',
        'item_code',
        'category',
        'brand',
        'unit_of_measure',
        'quantity_on_hand',
        'reorder_point',
        'reorder_quantity',
        'unit_cost',
        'last_order_date',
        'supplier_info',
        'storage_location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'integer',
            'reorder_point' => 'integer',
            'reorder_quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'last_order_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function usageRecords(): HasMany
    {
        return $this->hasMany(HousekeepingSupplyUsage::class);
    }

    /**
     * Generate unique item code
     */
    public static function generateItemCode(int $tenantId, string $category): string
    {
        $prefix = strtoupper(substr($category, 0, 3));
        $year = date('Y');
        $count = static::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return "{$prefix}-SUP-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if item needs reordering
     */
    public function needsReorder(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_point;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_on_hand <= 0) {
            return 'out_of_stock';
        } elseif ($this->needsReorder()) {
            return 'low_stock';
        }
        return 'adequate';
    }

    /**
     * Record supply usage
     */
    public function recordUsage(
        int $quantity,
        ?int $roomId = null,
        ?int $taskId = null,
        ?string $notes = null,
        ?int $usedBy = null
    ): HousekeepingSupplyUsage {
        return HousekeepingSupplyUsage::create([
            'tenant_id' => $this->tenant_id,
            'housekeeping_supply_id' => $this->id,
            'housekeeping_task_id' => $taskId,
            'room_id' => $roomId,
            'quantity_used' => $quantity,
            'notes' => $notes,
            'used_by' => $usedBy ?? auth()->id(),
        ]);
    }

    /**
     * Update quantity on hand
     */
    public function adjustQuantity(int $adjustment): void
    {
        if ($adjustment > 0) {
            $this->increment('quantity_on_hand', $adjustment);
        } else {
            $this->decrement('quantity_on_hand', abs($adjustment));
        }
    }
}
