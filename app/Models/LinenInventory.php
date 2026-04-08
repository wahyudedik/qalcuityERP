<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinenInventory extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'item_name',
        'item_code',
        'category',
        'size',
        'color',
        'material',
        'par_level',
        'total_quantity',
        'available_quantity',
        'in_use_quantity',
        'soiled_quantity',
        'damaged_quantity',
        'unit_cost',
        'last_purchase_date',
        'supplier_info',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'par_level' => 'integer',
            'total_quantity' => 'integer',
            'available_quantity' => 'integer',
            'in_use_quantity' => 'integer',
            'soiled_quantity' => 'integer',
            'damaged_quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'last_purchase_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(LinenMovement::class);
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

        return "{$prefix}-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update quantities after movement
     */
    public function updateQuantities(): void
    {
        $this->total_quantity = $this->available_quantity + $this->in_use_quantity + $this->soiled_quantity + $this->damaged_quantity;
        $this->save();
    }

    /**
     * Check if item is below par level
     */
    public function isBelowParLevel(): bool
    {
        return $this->available_quantity < ($this->par_level * $this->tenant()->first()?->rooms()->count() ?? 1);
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->available_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->isBelowParLevel()) {
            return 'low_stock';
        }
        return 'adequate';
    }

    /**
     * Record linen movement
     */
    public function recordMovement(
        string $type,
        int $quantity,
        ?int $roomId = null,
        ?int $fromId = null,
        ?int $toId = null,
        ?string $reason = null,
        ?int $recordedBy = null
    ): LinenMovement {
        return LinenMovement::create([
            'tenant_id' => $this->tenant_id,
            'linen_inventory_id' => $this->id,
            'movement_type' => $type,
            'quantity' => $quantity,
            'room_id' => $roomId,
            'from_location' => $fromId,
            'to_location' => $toId,
            'reason' => $reason,
            'recorded_by' => $recordedBy ?? auth()->id(),
        ]);
    }
}
