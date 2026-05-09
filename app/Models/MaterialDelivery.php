<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Material Delivery Tracking untuk Konstruksi
 */
class MaterialDelivery extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'delivery_number',
        'supplier_id',
        'supplier_name',
        'material_name',
        'material_category', // cement, steel, sand, aggregate, bricks, etc
        'quantity_ordered',
        'quantity_delivered',
        'unit',
        'unit_price',
        'total_value',
        'expected_date',
        'actual_delivery_date',
        'delivery_status', // pending, in_transit, delivered, partial, cancelled
        'po_number',
        'do_number', // delivery order number
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'received_by',
        'quality_check_status', // passed, failed, pending
        'quality_notes',
        'photos', // JSON array of delivery photos
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'decimal:3',
            'quantity_delivered' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total_value' => 'decimal:2',
            'expected_date' => 'date',
            'actual_delivery_date' => 'datetime',
            'photos' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Check if delivery is on time
     */
    public function isOnTime(): bool
    {
        if (! $this->actual_delivery_date || ! $this->expected_date) {
            return true;
        }

        return $this->actual_delivery_date->lte($this->expected_date->endOfDay());
    }

    /**
     * Calculate days delayed
     */
    public function getDaysDelayed(): int
    {
        if (! $this->actual_delivery_date || ! $this->expected_date) {
            return 0;
        }

        if ($this->isOnTime()) {
            return 0;
        }

        return $this->expected_date->diffInDays($this->actual_delivery_date);
    }

    /**
     * Check if delivery is complete
     */
    public function isComplete(): bool
    {
        return $this->quantity_delivered >= $this->quantity_ordered;
    }

    /**
     * Get shortage quantity
     */
    public function getShortage(): float
    {
        return max(0, $this->quantity_ordered - $this->quantity_delivered);
    }
}
