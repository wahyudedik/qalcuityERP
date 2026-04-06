<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierRfqResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'rfq_id',
        'supplier_id',
        'quoted_price',
        'currency',
        'lead_time_days',
        'minimum_order_quantity',
        'terms_and_conditions',
        'additional_notes',
        'valid_until',
        'status',
        'submitted_at',
        'accepted_at',
        'accepted_by'
    ];

    protected $casts = [
        'quoted_price' => 'decimal:2',
        'valid_until' => 'date',
        'submitted_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'rfq_id');
    }
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status !== 'accepted';
    }
}
