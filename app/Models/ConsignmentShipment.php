<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentShipment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'number', 'partner_id', 'warehouse_id',
        'ship_date', 'status', 'total_cost', 'total_retail',
        'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'ship_date' => 'date',
            'total_cost' => 'decimal:2',
            'total_retail' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(ConsignmentPartner::class, 'partner_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsignmentShipmentItem::class);
    }

    public function salesReports(): HasMany
    {
        return $this->hasMany(ConsignmentSalesReport::class);
    }

    public function remainingQty(): float
    {
        return $this->items->sum(fn ($i) => $i->quantity_sent - $i->quantity_sold - $i->quantity_returned);
    }

    public static function generateNumber(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;

        return 'CSG-'.date('Ym').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
