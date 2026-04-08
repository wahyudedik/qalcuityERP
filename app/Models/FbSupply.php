<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FbSupply extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'name',
        'unit',
        'current_stock',
        'minimum_stock',
        'cost_per_unit',
        'category_id',
        'supplier_name',
        'last_restocked_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
            'last_restocked_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'category_id'); // Placeholder - can be extended later
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FbSupplyTransaction::class, 'supply_id');
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class, 'supply_id');
    }

    /**
     * Check if stock is below minimum
     */
    public function needsRestock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->needsRestock()) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    /**
     * Calculate total inventory value
     */
    public function getInventoryValueAttribute(): float
    {
        return $this->current_stock * $this->cost_per_unit;
    }

    /**
     * Add stock
     */
    public function addStock(float $quantity, ?float $unitCost = null, ?string $reference = null): void
    {
        $this->increment('current_stock', $quantity);

        if ($unitCost) {
            $this->update(['cost_per_unit' => $unitCost]);
        }

        $this->update(['last_restocked_at' => now()]);

        // Record transaction
        FbSupplyTransaction::create([
            'tenant_id' => $this->tenant_id,
            'supply_id' => $this->id,
            'transaction_type' => 'purchase',
            'quantity' => $quantity,
            'unit_cost' => $unitCost ?? $this->cost_per_unit,
            'total_cost' => $quantity * ($unitCost ?? $this->cost_per_unit),
            'reference' => $reference,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Deduct stock (for usage)
     */
    public function deductStock(float $quantity, string $reference = null, string $notes = null): void
    {
        if ($this->current_stock < $quantity) {
            throw new \Exception("Insufficient stock for {$this->name}. Available: {$this->current_stock}, Required: {$quantity}");
        }

        $this->decrement('current_stock', $quantity);

        // Record transaction
        FbSupplyTransaction::create([
            'tenant_id' => $this->tenant_id,
            'supply_id' => $this->id,
            'transaction_type' => 'usage',
            'quantity' => -$quantity,
            'unit_cost' => $this->cost_per_unit,
            'total_cost' => -$quantity * $this->cost_per_unit,
            'reference' => $reference,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get low stock supplies
     */
    public static function getLowStockSupplies(int $tenantId)
    {
        return static::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->get();
    }
}
