<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'supplier_id', 'user_id', 'warehouse_id', 'number', 'status',
        'date', 'expected_date', 'subtotal', 'discount', 'tax', 'total', 'notes',
        'payment_type', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'date'          => 'date',
            'expected_date' => 'date',
            'due_date'      => 'date',
            'subtotal'      => 'decimal:2',
            'discount'      => 'decimal:2',
            'tax'           => 'decimal:2',
            'total'         => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class); }
    public function payable(): HasMany { return $this->hasMany(Payable::class); }
}
