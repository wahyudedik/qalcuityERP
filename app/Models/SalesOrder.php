<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'customer_id', 'user_id', 'quotation_id', 'number', 'status',
        'date', 'delivery_date', 'subtotal', 'discount', 'tax', 'total',
        'shipping_address', 'notes', 'payment_method', 'source',
        'payment_type', 'due_date',
        'currency_code', 'currency_rate', 'tax_rate_id', 'tax_amount',
        // Task 35: State machine
        'posting_status', 'posted_by', 'posted_at', 'cancel_reason',
        // Task 36: Revision
        'revision_number',
        // Task 37: Numbering
        'doc_sequence', 'doc_year',
    ];

    protected function casts(): array
    {
        return [
            'date'          => 'date',
            'delivery_date' => 'date',
            'due_date'      => 'date',
            'subtotal'      => 'decimal:2',
            'discount'      => 'decimal:2',
            'tax'           => 'decimal:2',
            'total'         => 'decimal:2',
            'tax_amount'    => 'decimal:2',
            'currency_rate' => 'float',
            'posted_at'     => 'datetime',
        ];
    }

    public function isPosted(): bool { return $this->posting_status === 'posted'; }
    public function isDraft(): bool  { return ($this->posting_status ?? 'draft') === 'draft'; }

    public function taxRate(): BelongsTo { return $this->belongsTo(TaxRate::class); }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function items(): HasMany           { return $this->hasMany(SalesOrderItem::class); }
    public function invoice(): HasMany         { return $this->hasMany(Invoice::class); }
    public function deliveryOrders(): HasMany  { return $this->hasMany(DeliveryOrder::class); }
    public function salesReturns(): HasMany    { return $this->hasMany(SalesReturn::class); }
}
