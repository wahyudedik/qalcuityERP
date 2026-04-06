<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaIngredient extends Model
{
    protected $fillable = [
        'tenant_id',
        'formula_id',
        'product_id',
        'inci_name',
        'common_name',
        'cas_number',
        'quantity',
        'unit',
        'percentage',
        'function',
        'phase',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'percentage' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get function label
     */
    public function getFunctionLabelAttribute(): string
    {
        $functions = [
            'emollient' => 'Emollient',
            'preservative' => 'Preservative',
            'active' => 'Active Ingredient',
            'fragrance' => 'Fragrance',
            'emulsifier' => 'Emulsifier',
            'thickener' => 'Thickener',
            'humectant' => 'Humectant',
            'surfactant' => 'Surfactant',
            'colorant' => 'Colorant',
            'solvent' => 'Solvent',
            'ph_adjuster' => 'pH Adjuster',
            'antioxidant' => 'Antioxidant',
            'other' => 'Other',
        ];

        return $functions[$this->function] ?? ucfirst(str_replace('_', ' ', $this->function ?? 'Unknown'));
    }

    /**
     * Get function color for UI
     */
    public function getFunctionColorAttribute(): string
    {
        return match ($this->function) {
            'active' => 'red',
            'preservative' => 'yellow',
            'emollient' => 'blue',
            'fragrance' => 'purple',
            'emulsifier' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get phase label
     */
    public function getPhaseLabelAttribute(): string
    {
        return match ($this->phase) {
            'oil_phase' => 'Oil Phase',
            'water_phase' => 'Water Phase',
            'cool_down_phase' => 'Cool Down Phase',
            default => ucfirst(str_replace('_', ' ', $this->phase ?? 'N/A'))
        };
    }

    /**
     * Check if ingredient is active
     */
    public function isActiveIngredient(): bool
    {
        return $this->function === 'active';
    }

    /**
     * Check if ingredient is preservative
     */
    public function isPreservative(): bool
    {
        return $this->function === 'preservative';
    }

    /**
     * Get cost for this ingredient (if linked to product)
     */
    public function getCostAttribute(): float
    {
        if (!$this->product) {
            return 0;
        }

        // Assuming product has avg_cost or latest purchase price
        $unitCost = $this->product->avg_cost ?? 0;
        return $unitCost * $this->quantity;
    }

    /**
     * Get CAS number formatted with link
     */
    public function getCasNumberLinkAttribute(): ?string
    {
        if (!$this->cas_number) {
            return null;
        }

        return 'https://commonchemistry.cas.org/detail?cas_rn=' . $this->cas_number;
    }

    /**
     * Scopes
     */
    public function scopeByFunction($query, string $function)
    {
        return $query->where('function', $function);
    }

    public function scopeByPhase($query, string $phase)
    {
        return $query->where('phase', $phase);
    }

    public function scopeActiveIngredients($query)
    {
        return $query->where('function', 'active');
    }

    public function scopePreservatives($query)
    {
        return $query->where('function', 'preservative');
    }

    /**
     * Validate ingredient concentration
     */
    public function isConcentrationSafe(): bool
    {
        // Common safe concentration limits (percentage)
        $safeLimits = [
            'preservative' => 2.0,
            'fragrance' => 3.0,
            'active' => 10.0,
            'colorant' => 5.0,
        ];

        $limit = $safeLimits[$this->function] ?? 100.0;
        return $this->percentage <= $limit;
    }

    /**
     * Get safety warning
     */
    public function getSafetyWarningAttribute(): ?string
    {
        if (!$this->isConcentrationSafe()) {
            return "Concentration {$this->percentage}% exceeds recommended limit";
        }

        return null;
    }
}
