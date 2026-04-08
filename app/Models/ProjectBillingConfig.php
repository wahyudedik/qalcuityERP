<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBillingConfig extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'project_id', 'tenant_id', 'billing_type', 'hourly_rate',
        'retainer_amount', 'retainer_cycle', 'fixed_price',
        'retention_pct', 'contract_value', 'retention_release_days',
        'next_billing_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate'           => 'decimal:2',
            'retainer_amount'       => 'decimal:2',
            'fixed_price'           => 'decimal:2',
            'retention_pct'         => 'decimal:2',
            'contract_value'        => 'decimal:2',
            'retention_release_days'=> 'integer',
            'next_billing_date'     => 'date',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    /**
     * Calculate retention amount from a gross amount.
     */
    public function calcRetention(float $grossAmount): float
    {
        return $this->retention_pct > 0
            ? round($grossAmount * $this->retention_pct / 100, 2)
            : 0;
    }

    /**
     * Total retention held across all invoices for this project.
     */
    public function totalRetentionHeld(): float
    {
        return ProjectInvoice::where('project_id', $this->project_id)
            ->sum(\Illuminate\Support\Facades\DB::raw('retention_amount - retention_released'));
    }

    /**
     * Is this a termin/progress-based billing?
     */
    public function isTermin(): bool
    {
        return $this->billing_type === 'termin';
    }
}
