<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalSupply extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'supply_code',
        'supply_name',
        'generic_name',
        'brand',
        'category',
        'subcategory',
        'unit_of_measure',
        'stock_quantity',
        'minimum_stock',
        'maximum_stock',
        'reorder_quantity',
        'expiry_date',
        'has_expiry',
        'expiry_alert_days',
        'expiry_alert_sent',
        'storage_location',
        'bin_location',
        'storage_condition',
        'unit_cost',
        'selling_price',
        'supplier_id',
        'supplier_part_number',
        'is_active',
        'requires_prescription',
        'is_controlled_substance',
        'requires_sterilization',
        'description',
        'specifications',
        'msds_path',
        'image_path',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
        'reorder_quantity' => 'integer',
        'has_expiry' => 'boolean',
        'expiry_alert_days' => 'integer',
        'expiry_alert_sent' => 'boolean',
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
        'requires_prescription' => 'boolean',
        'is_controlled_substance' => 'boolean',
        'requires_sterilization' => 'boolean',
    ];

    /**
     * Scope: Low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->where('is_active', true);
    }

    /**
     * Scope: Out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', 0)
            ->where('is_active', true);
    }

    /**
     * Scope: Expiring soon
     */
    public function scopeExpiringSoon($query, $days = 90)
    {
        return $query->where('has_expiry', true)
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', today())
            ->where('is_active', true);
    }

    /**
     * Scope: Expired
     */
    public function scopeExpired($query)
    {
        return $query->where('has_expiry', true)
            ->where('expiry_date', '<', today())
            ->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Search
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('supply_code', 'like', "%{$searchTerm}%")
                ->orWhere('supply_name', 'like', "%{$searchTerm}%")
                ->orWhere('generic_name', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Check if item is low stock
     */
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    /**
     * Check if item is out of stock
     */
    public function isOutOfStock()
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Check if item is expired
     */
    public function isExpired()
    {
        return $this->has_expiry && $this->expiry_date && $this->expiry_date < today();
    }

    /**
     * Check if item is expiring soon
     */
    public function isExpiringSoon()
    {
        if (! $this->has_expiry || ! $this->expiry_date) {
            return false;
        }

        $daysUntilExpiry = now()->diffInDays($this->expiry_date, false);

        return $daysUntilExpiry <= $this->expiry_alert_days;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (! $this->has_expiry || ! $this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get total stock value
     */
    public function getStockValueAttribute()
    {
        return $this->stock_quantity * $this->unit_cost;
    }
}
