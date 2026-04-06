<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subcontractor Payment/Progress Billing
 */
class SubcontractorPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'contract_id',
        'invoice_number',
        'billing_period',
        'work_description',
        'claimed_amount',
        'approved_amount',
        'retention_deducted',
        'net_payable',
        'payment_date',
        'status', // pending, approved, paid, rejected
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'claimed_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'retention_deducted' => 'decimal:2',
            'net_payable' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(SubcontractorContract::class, 'contract_id');
    }
}
