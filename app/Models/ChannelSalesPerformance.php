<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelSalesPerformance extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'sale_date',
        'total_sales',
        'total_units',
        'total_commission',
        'total_discount',
        'net_revenue',
        'order_count',
        'top_products',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_sales' => 'decimal:2',
        'total_units' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'top_products' => 'array',
    ];

    // Calculate net revenue
    public function calculateNetRevenue(): float
    {
        $this->net_revenue = $this->total_sales - $this->total_discount - $this->total_commission;
        return $this->net_revenue;
    }

    // Calculate average order value
    public function getAverageOrderValueAttribute(): float
    {
        if ($this->order_count <= 0) {
            return 0;
        }
        return round($this->total_sales / $this->order_count, 2);
    }

    // Record sale
    public static function recordSale(int $tenantId, int $channelId, float $sales, float $units, float $commission = 0, float $discount = 0): void
    {
        $record = self::firstOrNew([
            'tenant_id' => $tenantId,
            'channel_id' => $channelId,
            'sale_date' => now()->toDateString(),
        ]);

        $record->total_sales = ($record->total_sales ?? 0) + $sales;
        $record->total_units = ($record->total_units ?? 0) + $units;
        $record->total_commission = ($record->total_commission ?? 0) + $commission;
        $record->total_discount = ($record->total_discount ?? 0) + $discount;
        $record->order_count = ($record->order_count ?? 0) + 1;
        $record->calculateNetRevenue();
        $record->save();
    }

    // Scopes
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('sale_date', now()->year);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    // Relationships
    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class);
    }
}