<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicine_name',
        'generic_name',
        'brand_name',
        'medicine_type',
        'strength',
        'dosage',
        'frequency',
        'route',
        'duration_days',
        'special_instructions',
        'quantity',
        'quantity_dispensed',
        'is_dispensed',
        'notes',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'quantity' => 'integer',
        'quantity_dispensed' => 'integer',
        'is_dispensed' => 'boolean',
    ];

    /**
     * Get full medicine name
     */
    public function getFullMedicineNameAttribute()
    {
        if ($this->brand_name && $this->generic_name) {
            return "{$this->brand_name} ({$this->generic_name})";
        }

        return $this->brand_name ?? $this->generic_name ?? $this->medicine_name;
    }

    /**
     * Get dosage instructions
     */
    public function getDosageInstructionsAttribute()
    {
        $instructions = [];

        if ($this->dosage) {
            $instructions[] = $this->dosage;
        }

        if ($this->frequency) {
            $instructions[] = $this->frequency;
        }

        if ($this->route && $this->route !== 'oral') {
            $instructions[] = $this->route;
        }

        if ($this->duration_days) {
            $instructions[] = "for {$this->duration_days} days";
        }

        return implode(', ', $instructions);
    }

    /**
     * Check if fully dispensed
     */
    public function isFullyDispensed()
    {
        return $this->quantity_dispensed >= $this->quantity;
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->quantity_dispensed);
    }

    /**
     * Relation: Prescription
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Mark as dispensed
     */
    public function markAsDispensed($quantity = null)
    {
        $this->update([
            'is_dispensed' => true,
            'quantity_dispensed' => $quantity ?? $this->quantity,
        ]);
    }
}
