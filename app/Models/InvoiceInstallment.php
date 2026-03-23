<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceInstallment extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_id', 'installment_number',
        'amount', 'due_date', 'paid_amount', 'status', 'paid_date', 'notes',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date'    => 'date',
        'paid_date'   => 'date',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function remaining(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}
