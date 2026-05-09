<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    // Status constants
    const STATUS_PENDING = 'pending';

    const STATUS_PENDING_PAYMENT = 'pending_payment';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_PROCESSING = 'processing';

    const STATUS_SHIPPED = 'shipped';

    const STATUS_DELIVERED = 'delivered';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'user_id',
        'cashier_session_id',
        'quotation_id',
        'number',
        'status',
        'date',
        'delivery_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'shipping_address',
        'notes',
        'payment_method',
        'source',
        'payment_type',
        'due_date',
        'currency_code',
        'currency_rate',
        'tax_rate_id',
        'tax_amount',
        // BUG-FIN-004: Withholding tax and tax-inclusive pricing
        'withholding_tax_amount',
        'tax_inclusive',
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
        // Payment tracking
        'paid_amount',
        'change_amount',
        'payment_reference',
        'split_payments',
        'completed_at',
        'stock_deducted_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'delivery_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            // BUG-FIN-004: Withholding tax casting
            'withholding_tax_amount' => 'decimal:2',
            'tax_inclusive' => 'boolean',
            'total' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'currency_rate' => 'float',
            'posted_at' => 'datetime',
            'completed_at' => 'datetime',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'split_payments' => 'array',
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

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }
}
