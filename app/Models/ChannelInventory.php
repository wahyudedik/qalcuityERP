<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelInventory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'formula_id',
        'allocated_stock',
        'sold_stock',
        'available_stock',
        'reserved_stock',
        'last_restock_date',
    ];

    protected $casts = [
        'allocated_stock' => 'decimal:2',
        'sold_stock' => 'decimal:2',
        'available_stock' => 'decimal:2',
        'reserved_stock' => 'decimal:2',
        'last_restock_date' => 'date',
    ];

    // Calculate available stock
    public function calculateAvailableStock(): float
    {
        $this->available_stock = $this->allocated_stock - $this->sold_stock - $this->reserved_stock;
        return $this->available_stock;
    }

    // Check if low stock
    public function isLowStock(float $threshold = 10): bool
    {
        $this->calculateAvailableStock();
        return $this->available_stock < $threshold;
    }

    // Restock
    public function restock(float $quantity): void
    {
        $this->allocated_stock += $quantity;
        $this->calculateAvailableStock();
        $this->last_restock_date = now();
        $this->save();
    }

    // Sell stock
    public function sell(float $quantity): bool
    {
        $this->calculateAvailableStock();
        if ($this->available_stock < $quantity) {
            return false;
        }
        $this->sold_stock += $quantity;
        $this->calculateAvailableStock();
        $this->save();
        return true;
    }

    // Relationships
    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }
}