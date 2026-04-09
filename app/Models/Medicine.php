<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'medicine_code',
        'name',
        'generic_name',
        'manufacturer',
        'brand',
        'dosage_form',
        'strength',
        'route',
        'atc_code',
        'storage_type',
        'storage_instructions',
        'unit_price',
        'purchase_price',
        'selling_price',
        'markup_percentage',
        'total_stock',
        'minimum_stock',
        'reorder_point',
        'maximum_stock',
        'requires_prescription',
        'is_controlled_substance',
        'drug_classification',
        'description',
        'contraindications',
        'side_effects',
        'dosage_instructions',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'total_stock' => 'integer',
        'minimum_stock' => 'integer',
        'reorder_point' => 'integer',
        'maximum_stock' => 'integer',
        'requires_prescription' => 'boolean',
        'is_controlled_substance' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Check if stock is low
     */
    public function isLowStock()
    {
        return $this->total_stock <= $this->reorder_point;
    }

    /**
     * Check if out of stock
     */
    public function isOutOfStock()
    {
        return $this->total_stock <= 0;
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
     * Scope: Active medicines only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Low stock
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('total_stock', '<=', 'reorder_point');
    }

    /**
     * Scope: Out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('total_stock', '<=', 0);
    }

    /**
     * Scope: Search by name or generic name
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('generic_name', 'like', "%{$searchTerm}%")
                ->orWhere('medicine_code', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Relation: Category
     */
    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    /**
     * Relation: Stock batches
     */
    public function stocks()
    {
        return $this->hasMany(MedicineStock::class);
    }

    /**
     * Relation: Available stock batches
     */
    public function availableStocks()
    {
        return $this->hasMany(MedicineStock::class)
            ->where('status', 'available')
            ->where('is_expired', false);
    }

    /**
     * Relation: Suppliers
     */
    public function suppliers()
    {
        return $this->hasMany(MedicineSupplier::class);
    }

    /**
     * Get full medicine name
     */
    public function getFullNameAttribute()
    {
        if ($this->brand && $this->generic_name) {
            return "{$this->brand} ({$this->generic_name}) - {$this->strength}";
        }

        return "{$this->name} - {$this->strength}";
    }

    /**
     * Calculate markup
     */
    public function calculateMarkup()
    {
        if ($this->purchase_price > 0) {
            return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
        }
        return 0;
    }
}
