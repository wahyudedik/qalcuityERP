<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'variant_id',
        'transaction_date',
        'transaction_type',
        'quantity',
        'balance',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    // Type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            'transfer' => 'Transfer',
            default => ucfirst($this->transaction_type)
        };
    }

    // Scopes
    public function scopeIn($query)
    {
        return $query->where('transaction_type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('transaction_type', 'out');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Relationships
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // Get formatted quantity with sign
    public function getFormattedQuantityAttribute(): string
    {
        $sign = $this->quantity >= 0 ? '+' : '';
        return $sign . number_format($this->quantity);
    }
}
