<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bed extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'ward_id',
        'bed_number',
        'bed_name',
        'bed_type',
        'daily_rate',
        'status',
        'current_patient_id',
        'current_admission_id',
        'last_cleaned_at',
        'cleaned_by',
        'facilities',
        'notes',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'facilities' => 'array',
        'last_cleaned_at' => 'datetime',
    ];

    /**
     * Check if bed is available
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Check if bed is occupied
     */
    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    /**
     * Get bed type label
     */
    public function getBedTypeLabelAttribute()
    {
        $labels = [
            'standard' => 'Standard',
            'vip' => 'VIP',
            'vvip' => 'VVIP',
            'icu' => 'ICU',
            'nicu' => 'NICU',
            'isolation' => 'Isolation',
            'maternity' => 'Maternity',
            'pediatric' => 'Pediatric',
        ];

        return $labels[$this->bed_type] ?? $this->bed_type;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'available' => 'Available',
            'occupied' => 'Occupied',
            'maintenance' => 'Maintenance',
            'reserved' => 'Reserved',
            'blocked' => 'Blocked',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Scope: Available beds only
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: By bed type
     */
    public function scopeType($query, $type)
    {
        return $query->where('bed_type', $type);
    }

    /**
     * Scope: Occupied beds
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope: In maintenance
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Relation: Ward
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * Relation: Current patient
     */
    public function currentPatient()
    {
        return $this->belongsTo(Patient::class, 'current_patient_id');
    }

    /**
     * Relation: Current admission
     */
    public function currentAdmission()
    {
        return $this->belongsTo(Admission::class, 'current_admission_id');
    }

    /**
     * Mark bed as occupied
     */
    public function markAsOccupied($patientId, $admissionId)
    {
        $this->update([
            'status' => 'occupied',
            'current_patient_id' => $patientId,
            'current_admission_id' => $admissionId,
        ]);
    }

    /**
     * Mark bed as available (after cleaning)
     */
    public function markAsAvailable($cleanedBy = null)
    {
        $this->update([
            'status' => 'available',
            'current_patient_id' => null,
            'current_admission_id' => null,
            'last_cleaned_at' => now(),
            'cleaned_by' => $cleanedBy,
        ]);
    }

    /**
     * Mark bed as maintenance
     */
    public function markAsMaintenance()
    {
        $this->update([
            'status' => 'maintenance',
            'current_patient_id' => null,
            'current_admission_id' => null,
        ]);
    }

    /**
     * Get bed summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'bed_number' => $this->bed_number,
            'bed_name' => $this->bed_name,
            'type' => $this->bed_type_label,
            'status' => $this->status_label,
            'daily_rate' => $this->daily_rate,
            'ward' => $this->ward?->ward_name,
        ];
    }
}
