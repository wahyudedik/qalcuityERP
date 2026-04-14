<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'sales_order_id', 'warehouse_id', 'created_by',
        'number', 'delivery_date', 'status', 'shipping_address',
        'courier', 'tracking_number', 'notes',
    ];

    protected $casts = ['delivery_date' => 'date'];

    public function tenant(): BelongsTo     { return $this->belongsTo(Tenant::class); }
    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function warehouse(): BelongsTo  { return $this->belongsTo(Warehouse::class); }
    public function creator(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany        { return $this->hasMany(DeliveryOrderItem::class); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'shipped'   => 'Dikirim',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
            'shipped'   => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            'delivered' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            default     => 'bg-gray-100 text-gray-500',
        };
    }
}