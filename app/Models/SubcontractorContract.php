<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subcontractor Contract
 */
class SubcontractorContract extends Model
{
    protected $fillable = [
        'tenant_id',
        'subcontractor_id',
        'project_id',
        'contract_number',
        'scope_of_work',
        'contract_value',
        'start_date',
        'end_date',
        'status', // draft, active, completed, terminated
        'payment_terms',
        'retention_percentage',
        'warranty_period_months',
        'performance_rating',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'contract_value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'retention_percentage' => 'decimal:2',
            'performance_rating' => 'decimal:1',
            'warranty_period_months' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubcontractorPayment::class);
    }

    /**
     * Calculate total paid amount
     */
    public function getTotalPaid(): float
    {
        return $this->payments()->where('status', 'paid')->sum('amount');
    }

    /**
     * Calculate remaining balance
     */
    public function getRemainingBalance(): float
    {
        return $this->contract_value - $this->getTotalPaid();
    }

    /**
     * Calculate retention amount
     */
    public function getRetentionAmount(): float
    {
        return $this->contract_value * ($this->retention_percentage / 100);
    }
}
