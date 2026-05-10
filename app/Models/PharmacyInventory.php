<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_code',
        'item_name',
        'generic_name',
        'brand_name',
        'item_type',
        'medication_type',
        'drug_class',
        'therapeutic_category',
        'stock_quantity',
        'minimum_stock',
        'maximum_stock',
        'reorder_quantity',
        'unit_of_measure',
        'stock_in_transit',
        'reserved_stock',
        'cost_price',
        'selling_price',
        'markup_percentage',
        'supplier_name',
        'supplier_contact',
        'last_order_date',
        'expiry_date',
        'has_expiry',
        'expiry_alert_days',
        'expiry_alert_sent',
        'storage_requirement',
        'storage_location',
        'batch_number',
        'requires_prescription',
        'controlled_substance',
        'bpom_number',
        'registration_number',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
        'reorder_quantity' => 'integer',
        'stock_in_transit' => 'integer',
        'reserved_stock' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'last_order_date' => 'date',
        'expiry_date' => 'date',
        'has_expiry' => 'boolean',
        'expiry_alert_sent' => 'boolean',
        'requires_prescription' => 'boolean',
        'controlled_substance' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get available stock
     */
    public function getAvailableStockAttribute()
    {
        return $this->stock_quantity - $this->reserved_stock;
    }

    /**
     * Alias: name → item_name (for view compatibility)
     */
    public function getNameAttribute(): string
    {
        return $this->item_name ?? '';
    }

    /**
     * Alias: reorder_level → minimum_stock (for view compatibility)
     */
    public function getReorderLevelAttribute(): int
    {
        return $this->minimum_stock ?? 0;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    /**
     * Check if stock is out
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
        return $this->has_expiry && $this->expiry_date && $this->expiry_date < now();
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

        return $daysUntilExpiry <= $this->expiry_alert_days && $daysUntilExpiry >= 0;
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
     * Get item type label
     */
    public function getItemTypeLabelAttribute()
    {
        $labels = [
            'medication' => 'Medication',
            'supplement' => 'Supplement',
            'medical_supply' => 'Medical Supply',
            'vaccine' => 'Vaccine',
            'herbal' => 'Herbal',
        ];

        return $labels[$this->item_type] ?? $this->item_type;
    }

    /**
     * Get medication type label
     */
    public function getMedicationTypeLabelAttribute()
    {
        $labels = [
            'tablet' => 'Tablet',
            'capsule' => 'Capsule',
            'syrup' => 'Syrup',
            'injection' => 'Injection',
            'topical' => 'Topical',
            'inhaler' => 'Inhaler',
            'drop' => 'Drop',
            'suppository' => 'Suppository',
            'powder' => 'Powder',
            'other' => 'Other',
        ];

        return $labels[$this->medication_type] ?? $this->medication_type;
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMarginAttribute()
    {
        if ($this->selling_price > 0) {
            return (($this->selling_price - $this->cost_price) / $this->selling_price) * 100;
        }

        return 0;
    }

    /**
     * Get stock value
     */
    public function getStockValueAttribute()
    {
        return $this->stock_quantity * $this->cost_price;
    }

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
        return $query->where('stock_quantity', '<=', 0)
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
     * Scope: Expired items
     */
    public function scopeExpired($query)
    {
        return $query->where('has_expiry', true)
            ->where('expiry_date', '<', today())
            ->where('is_active', true);
    }

    /**
     * Scope: By item type
     */
    public function scopeType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    /**
     * Scope: Controlled substances
     */
    public function scopeControlled($query)
    {
        return $query->where('controlled_substance', true);
    }

    /**
     * Scope: Requires prescription
     */
    public function scopeRequiresPrescription($query)
    {
        return $query->where('requires_prescription', true);
    }

    /**
     * Scope: Search by code or name
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('item_code', 'like', "%{$searchTerm}%")
                ->orWhere('item_name', 'like', "%{$searchTerm}%")
                ->orWhere('generic_name', 'like', "%{$searchTerm}%")
                ->orWhere('brand_name', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Receive stock
     */
    public function receiveStock($quantity, $batchNumber = null, $expiryDate = null)
    {
        $this->increment('stock_quantity', $quantity);

        if ($batchNumber) {
            $this->update(['batch_number' => $batchNumber]);
        }

        if ($expiryDate) {
            $this->update([
                'expiry_date' => $expiryDate,
                'has_expiry' => true,
                'expiry_alert_sent' => false,
            ]);
        }

        $this->update(['last_order_date' => today()]);
    }

    /**
     * Issue/dispense stock
     */
    public function issueStock($quantity)
    {
        if ($this->available_stock < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$this->available_stock}, Requested: {$quantity}");
        }

        $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Reserve stock for prescription
     */
    public function reserveStock($quantity)
    {
        if ($this->available_stock < $quantity) {
            throw new \Exception('Insufficient available stock for reservation');
        }

        $this->increment('reserved_stock', $quantity);
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock($quantity)
    {
        $this->decrement('reserved_stock', $quantity);
    }

    /**
     * Adjust stock (for corrections)
     */
    public function adjustStock($newQuantity, $reason = null)
    {
        $oldQuantity = $this->stock_quantity;
        $this->update(['stock_quantity' => $newQuantity]);

        return [
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => $newQuantity - $oldQuantity,
            'reason' => $reason,
        ];
    }

    /**
     * Get pharmacy item summary
     */
    public function getSummaryAttribute()
    {
        return [
            'item_code' => $this->item_code,
            'item_name' => $this->item_name,
            'generic_name' => $this->generic_name,
            'type' => $this->item_type_label,
            'stock_status' => $this->stock_status,
            'stock_quantity' => $this->stock_quantity,
            'available_stock' => $this->available_stock,
            'reserved_stock' => $this->reserved_stock,
            'selling_price' => $this->selling_price,
            'is_expired' => $this->isExpired(),
            'days_until_expiry' => $this->days_until_expiry,
        ];
    }
}
