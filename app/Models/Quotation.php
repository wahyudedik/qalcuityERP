<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use BelongsToTenant;

    const STATUS_DRAFT    = 'draft';
    const STATUS_SENT     = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED  = 'expired';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SENT,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_EXPIRED,
    ];

    protected $fillable = [
        'tenant_id', 'customer_id', 'user_id', 'number', 'status',
        'date', 'valid_until', 'subtotal', 'discount', 'tax', 'total', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date',
            'valid_until' => 'date',
            'subtotal'    => 'decimal:2',
            'discount'    => 'decimal:2',
            'tax'         => 'decimal:2',
            'total'       => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(QuotationItem::class); }
    public function salesOrders(): HasMany { return $this->hasMany(SalesOrder::class); }
}
