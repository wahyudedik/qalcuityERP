<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'commodity',
        'market_name',
        'location',
        'price_per_kg',
        'currency',
        'unit',
        'quality_grade',
        'price_date',
        'price_source',
        'previous_price',
        'price_change_percent',
        'market_notes',
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'previous_price' => 'decimal:2',
        'price_change_percent' => 'float',
        'price_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeCommodity($query, string $commodity)
    {
        return $query->where('commodity', $commodity);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('price_date', '>=', now()->subDays($days));
    }

    public function getPriceChangeAttribute(): string
    {
        if (!$this->price_change_percent)
            return '0%';

        $sign = $this->price_change_percent > 0 ? '+' : '';
        return "{$sign}{$this->price_change_percent}%";
    }

    public function getChangeDirectionAttribute(): string
    {
        if (!$this->price_change_percent)
            return 'stable';
        return $this->price_change_percent > 0 ? 'up' : 'down';
    }
}
