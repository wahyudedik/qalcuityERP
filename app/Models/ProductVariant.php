<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'variant_name',
        'sku',
        'barcode',
        'variant_attributes',
        'price',
        'cost_price',
        'stock_quantity',
        'reorder_level',
        'status',
        'notes',
    ];

    protected $casts = [
        'variant_attributes' => 'array',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'discontinued' => 'Discontinued',
            default => 'Unknown'
        };
    }

    // Check if low stock
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    // Check if out of stock
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    // Generate SKU automatically
    public static function generateSKU(string $formulaCode, array $attributes): string
    {
        // Take first 3 chars of each attribute value
        $attributeCodes = collect($attributes)
            ->map(function ($value) {
                return strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $value), 0, 4));
            })
            ->implode('-');

        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        return strtoupper($formulaCode) . '-' . $attributeCodes . '-' . $random;
    }

    // Add stock
    public function addStock(int $quantity, string $type = 'in', string $notes = ''): void
    {
        $this->stock_quantity += $quantity;
        $this->save();

        // Record inventory transaction
        $this->inventoryTransactions()->create([
            'tenant_id' => $this->tenant_id,
            'transaction_date' => now(),
            'transaction_type' => $type,
            'quantity' => $quantity,
            'balance' => $this->stock_quantity,
            'notes' => $notes,
        ]);
    }

    // Remove stock
    public function removeStock(int $quantity, string $type = 'out', string $notes = ''): void
    {
        if ($this->stock_quantity < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $this->stock_quantity -= $quantity;
        $this->save();

        // Record inventory transaction
        $this->inventoryTransactions()->create([
            'tenant_id' => $this->tenant_id,
            'transaction_date' => now(),
            'transaction_type' => $type,
            'quantity' => -$quantity,
            'balance' => $this->stock_quantity,
            'notes' => $notes,
        ]);
    }

    // Get specific variant attribute value
    public function getVariantAttributeValue(string $attributeName): ?string
    {
        return $this->variant_attributes[$attributeName] ?? null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // Relationships
    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'variant_id');
    }

    // Calculate margin
    public function getMarginAttribute(): ?float
    {
        if (!$this->price || !$this->cost_price || $this->price <= 0) {
            return null;
        }
        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }

    // Get formatted attributes for display
    public function getFormattedAttributesAttribute(): string
    {
        if (!$this->variant_attributes) {
            return '';
        }

        return collect($this->variant_attributes)
            ->map(function ($value, $key) {
                return ucfirst($key) . ': ' . $value;
            })
            ->implode(', ');
    }
}
