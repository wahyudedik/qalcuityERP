<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPackage extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'package_code',
        'name',
        'description',
        'destination',
        'category',
        'duration_days',
        'duration_nights',
        'min_pax',
        'max_pax',
        'price_per_person',
        'cost_per_person',
        'currency',
        'status',
        'valid_from',
        'valid_until',
        'inclusions',
        'exclusions',
        'terms_conditions',
        'cancellation_policy',
        'sort_order',
        'is_featured',
        'created_by',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'duration_nights' => 'integer',
        'min_pax' => 'integer',
        'max_pax' => 'integer',
        'price_per_person' => 'decimal:2',
        'cost_per_person' => 'decimal:2',
        'inclusions' => 'array',
        'exclusions' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_featured' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function itineraryDays(): HasMany
    {
        return $this->hasMany(ItineraryDay::class)->orderBy('day_number');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(TourBooking::class);
    }

    public function supplierAllocations(): HasMany
    {
        return $this->hasMany(PackageSupplierAllocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->price_per_person <= 0) {
            return 0;
        }

        $profit = $this->price_per_person - $this->cost_per_person;
        return round(($profit / $this->price_per_person) * 100, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'active' => 'green',
            'inactive' => 'yellow',
            'archived' => 'red',
            default => 'gray'
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'domestic' => 'Domestic',
            'international' => 'International',
            'adventure' => 'Adventure',
            'luxury' => 'Luxury',
            'cultural' => 'Cultural',
            'beach' => 'Beach & Island',
            'mountain' => 'Mountain & Trekking',
            'city_tour' => 'City Tour',
            default => ucfirst(str_replace('_', ' ', $this->category))
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByDestination($query, $destination)
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAvailableOnDate($query, $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->whereNull('valid_from')
                ->orWhere('valid_from', '<=', $date);
        })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $date);
            });
    }

    /**
     * Methods
     */
    public function calculateTotalCost(int $pax): float
    {
        return $this->cost_per_person * $pax;
    }

    public function calculateTotalPrice(int $pax): float
    {
        return $this->price_per_person * $pax;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canBook(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->valid_from && $this->valid_from->gt($today)) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->lt($today)) {
            return false;
        }

        return true;
    }
}
