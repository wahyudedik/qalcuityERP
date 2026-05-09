<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackagingMaterial extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'material_name',
        'material_type',
        'material_category',
        'sku',
        'supplier_name',
        'unit_cost',
        'dimensions',
        'color',
        'material_composition',
        'is_recyclable',
        'is_active',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'is_recyclable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->material_type) {
            'primary' => 'Primary Packaging',
            'secondary' => 'Secondary Packaging',
            'tertiary' => 'Tertiary Packaging',
            default => ucfirst($this->material_type)
        };
    }

    // Category labels
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->material_category) {
            'bottle' => 'Bottle',
            'tube' => 'Tube',
            'jar' => 'Jar',
            'box' => 'Box',
            'carton' => 'Carton',
            'label' => 'Label',
            'cap' => 'Cap',
            'pump' => 'Pump',
            default => ucfirst($this->material_category)
        };
    }

    // Generate next SKU
    public static function getNextSku(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return 'PKG-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('material_type', 'primary');
    }

    public function scopeSecondary($query)
    {
        return $query->where('material_type', 'secondary');
    }

    public function scopeRecyclable($query)
    {
        return $query->where('is_recyclable', true);
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }
}
