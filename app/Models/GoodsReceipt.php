<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use BelongsToTenant;

    const STATUS_DRAFT = 'draft';

    const STATUS_RECEIVED = 'received';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_RECEIVED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'tenant_id', 'purchase_order_id', 'warehouse_id', 'received_by',
        'number', 'receipt_date', 'delivery_note', 'status', 'notes',
    ];

    protected $casts = ['receipt_date' => 'date'];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
