<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'product_id', 'warehouse_id', 'batch_number',
        'quantity', 'cost_price', 'quantity_remaining',
        'manufacture_date', 'expiry_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'manufacture_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** Hari tersisa sebelum expired (negatif = sudah expired) */
    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast() && $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('quantity', '>', 0);
    }

    public function scopeExpiringSoon($query, int $days = 2)
    {
        return $query->active()
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'active')->where('expiry_date', '<', today());
    }
}
