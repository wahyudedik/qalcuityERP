<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'purchase_order_id', 'supplier_id', 'warehouse_id',
        'created_by', 'number', 'return_date', 'reason', 'status',
        'subtotal', 'tax_amount', 'total', 'refund_method', 'refund_amount',
        'is_cross_period', 'notes',
    ];

    protected $casts = [
        'return_date'     => 'date',
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total'           => 'decimal:2',
        'refund_amount'   => 'decimal:2',
        'is_cross_period' => 'boolean',
    ];

    public function tenant(): BelongsTo        { return $this->belongsTo(Tenant::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function supplier(): BelongsTo      { return $this->belongsTo(Supplier::class); }
    public function warehouse(): BelongsTo     { return $this->belongsTo(Warehouse::class); }
    public function creator(): BelongsTo       { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany           { return $this->hasMany(PurchaseReturnItem::class); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'sent'      => 'Dikirim ke Supplier',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
            'sent'      => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            'completed' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            default     => 'bg-gray-100 text-gray-500',
        };
    }
}
