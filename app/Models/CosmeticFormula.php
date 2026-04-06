<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CosmeticFormula extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'formula_code',
        'formula_name',
        'product_type',
        'brand',
        'target_ph',
        'actual_ph',
        'shelf_life_months',
        'batch_size',
        'batch_unit',
        'total_cost',
        'cost_per_unit',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'target_ph' => 'decimal:2',
        'actual_ph' => 'decimal:2',
        'shelf_life_months' => 'integer',
        'batch_size' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(FormulaIngredient::class, 'formula_id')->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormulaVersion::class, 'formula_id')->orderByDesc('created_at');
    }

    public function stabilityTests(): HasMany
    {
        return $this->hasMany(StabilityTest::class, 'formula_id');
    }

    /**
     * Status helpers
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isTesting(): bool
    {
        return $this->status === 'testing';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isInProduction(): bool
    {
        return $this->status === 'production';
    }

    public function isDiscontinued(): bool
    {
        return $this->status === 'discontinued';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'testing' => 'In Testing',
            'approved' => 'Approved',
            'production' => 'In Production',
            'discontinued' => 'Discontinued',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'testing' => 'yellow',
            'approved' => 'green',
            'production' => 'blue',
            'discontinued' => 'red',
            default => 'gray'
        };
    }

    /**
     * Calculate total cost from ingredients
     */
    public function calculateTotalCost(): float
    {
        $totalCost = $this->ingredients()->sum('cost');
        $this->total_cost = $totalCost;

        if ($this->batch_size > 0) {
            $this->cost_per_unit = round($totalCost / $this->batch_size, 2);
        }

        $this->save();
        return $totalCost;
    }

    /**
     * Check if pH is within acceptable range
     */
    public function isPhWithinRange(float $tolerance = 0.5): bool
    {
        if (!$this->target_ph || !$this->actual_ph) {
            return false;
        }

        $diff = abs($this->target_ph - $this->actual_ph);
        return $diff <= $tolerance;
    }

    /**
     * Get next formula code
     */
    public static function getNextFormulaCode(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'CF-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'production']);
    }

    public function scopeInTesting($query)
    {
        return $query->where('status', 'testing');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get ingredient count by function
     */
    public function getIngredientFunctionCounts(): array
    {
        return $this->ingredients()
            ->selectRaw('function, COUNT(*) as count')
            ->groupBy('function')
            ->pluck('count', 'function')
            ->toArray();
    }

    /**
     * Check if formula is ready for production
     */
    public function isReadyForProduction(): bool
    {
        // Must be approved
        if (!$this->isApproved()) {
            return false;
        }

        // Must have ingredients
        if ($this->ingredients()->count() === 0) {
            return false;
        }

        // Must have pH measured
        if (!$this->actual_ph) {
            return false;
        }

        // Must have passing stability test
        $passingTest = $this->stabilityTests()
            ->where('overall_result', 'Pass')
            ->exists();

        return $passingTest;
    }
}
