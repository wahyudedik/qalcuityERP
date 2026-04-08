<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourSupplier extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'supplier_code',
        'name',
        'type',
        'description',
        'contact_person',
        'contact_phone',
        'contact_email',
        'address',
        'city',
        'country',
        'website',
        'rating',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PackageSupplierAllocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'hotel' => 'Hotel / Accommodation',
            'transport' => 'Transportation',
            'activity' => 'Activity Provider',
            'restaurant' => 'Restaurant',
            'visa_agent' => 'Visa Agent',
            'insurance' => 'Insurance Provider',
            default => ucfirst($this->type)
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'suspended' => 'red',
            default => 'gray'
        };
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
