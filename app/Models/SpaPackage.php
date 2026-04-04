<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpaPackage extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'package_price',
        'regular_price',
        'savings',
        'total_duration_minutes',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'package_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'savings' => 'decimal:2',
            'total_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SpaPackageItem::class, 'package_id')->orderBy('sequence_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SpaBooking::class, 'package_id');
    }

    /**
     * Calculate savings percentage
     */
    public function getSavingsPercentageAttribute(): float
    {
        if ($this->regular_price == 0) {
            return 0;
        }
        return (($this->regular_price - $this->package_price) / $this->regular_price) * 100;
    }

    /**
     * Recalculate package price based on items
     */
    public function recalculatePrice(): void
    {
        $regularPrice = 0;
        $totalDuration = 0;

        foreach ($this->items as $item) {
            $regularPrice += $item->treatment->price;
            $totalDuration += $item->duration_override ?? $item->treatment->duration_minutes;
        }

        $this->update([
            'regular_price' => $regularPrice,
            'total_duration_minutes' => $totalDuration,
            'savings' => max(0, $regularPrice - $this->package_price),
        ]);
    }
}
