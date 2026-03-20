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
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function items(): HasMany { return $this->hasMany(SalesOrderItem::class); }
    public function invoice(): HasMany { return $this->hasMany(Invoice::class); }
}
