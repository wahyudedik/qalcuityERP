<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BelongsToTenant;
    use AuditsChanges, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'user_id',
        'warehouse_id',
        'purchase_requisition_id',
        'rfq_id',
        'number',
        'status',
        'date',
        'expected_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
        'payment_type',
        'due_date',
        'currency_code',
        'currency_rate',
        'tax_rate_id',
        'tax_amount',
        // Task 35: State machine
        'posting_status',
        'posted_by',
        'posted_at',
        'cancel_reason',
        // Task 36: Revision
        'revision_number',
        // Task 37: Numbering
        'doc_sequence',
        'doc_year',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'expected_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'currency_rate' => 'float',
            'posted_at' => 'datetime',
        ];
    }

    public function isPosted(): bool
    {
        return $this->posting_status === 'posted';
    }
    public function isDraft(): bool
    {
        return ($this->posting_status ?? 'draft') === 'draft';
    }

    public function postingStatusLabel(): string
    {
        return match ($this->posting_status ?? 'draft') {
            'draft' => 'Draft',
            'posted' => 'Diposting',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->posting_status ?? 'draft'),
        };
    }

    public function postingStatusColor(): string
    {
        return match ($this->posting_status ?? 'draft') {
            'draft' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
            'posted' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            default => 'bg-gray-100 text-gray-500',
        };
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    public function payable(): HasMany
    {
        return $this->hasMany(Payable::class);
    }
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }
    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }
    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}