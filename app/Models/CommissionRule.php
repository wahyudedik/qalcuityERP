<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'type', 'rate', 'tiers', 'basis',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'tiers' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Calculate commission based on rule type.
     */
    public function calculate(float $amount): float
    {
        return match ($this->type) {
            'flat_pct' => round($amount * $this->rate / 100, 2),
            'flat_amount' => (float) $this->rate,
            'tiered' => $this->calculateTiered($amount),
            default => 0,
        };
    }

    private function calculateTiered(float $amount): float
    {
        if (! $this->tiers || ! is_array($this->tiers)) {
            return 0;
        }

        $commission = 0;
        $remaining = $amount;

        $sortedTiers = collect($this->tiers)->sortBy('min')->values();

        foreach ($sortedTiers as $tier) {
            $min = (float) ($tier['min'] ?? 0);
            $max = isset($tier['max']) && $tier['max'] !== null ? (float) $tier['max'] : PHP_FLOAT_MAX;
            $rate = (float) ($tier['rate'] ?? 0);

            if ($amount <= $min) {
                break;
            }

            $taxable = min($amount, $max) - $min;
            if ($taxable > 0) {
                $commission += round($taxable * $rate / 100, 2);
            }
        }

        return $commission;
    }
}
