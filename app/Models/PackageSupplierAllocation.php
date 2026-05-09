<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageSupplierAllocation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'tour_package_id',
        'supplier_id',
        'service_type',
        'service_description',
        'day_number',
        'cost_per_unit',
        'unit_type',
        'details',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'cost_per_unit' => 'decimal:2',
        'details' => 'array',
    ];

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(TourSupplier::class, 'supplier_id');
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            'accommodation' => 'Accommodation',
            'transport' => 'Transportation',
            'activity' => 'Activity',
            'meal' => 'Meal',
            'guide' => 'Tour Guide',
            default => ucfirst($this->service_type)
        };
    }
}
