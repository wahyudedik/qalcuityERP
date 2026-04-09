<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'doctor_id',
        'prescription_number',
        'prescription_date',
        'diagnosis_summary',
        'special_instructions',
        'status',
        'valid_until',
        'is_dispensed',
        'dispensed_at',
        'dispensed_by',
        'pharmacy_location',
        'notes',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'valid_until' => 'date',
        'is_dispensed' => 'boolean',
        'dispensed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = static::generatePrescriptionNumber();
            }
        });
    }

    /**
     * Generate unique prescription number
     * Format: RX-YYYYMMDD-XXXX
     */
    public static function generatePrescriptionNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'RX-' . $date;

        $lastPrescription = static::where('prescription_number', 'like', $prefix . '%')
            ->orderBy('prescription_number', 'desc')
            ->first();

        if ($lastPrescription) {
            $lastNumber = (int) substr($lastPrescription->prescription_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Check if prescription is expired
     */
    public function isExpired()
    {
        if ($this->valid_until) {
            return now()->gt($this->valid_until);
        }

        // Default: 30 days from prescription date
        return now()->gt($this->prescription_date->copy()->addDays(30));
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Scope: Active prescriptions only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Not yet dispensed
     */
    public function scopePendingDispensing($query)
    {
        return $query->where('is_dispensed', false)
            ->whereIn('status', ['active', 'draft']);
    }

    /**
     * Scope: For specific patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: By doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Relation: Visit
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Relation: Dispensed by user
     */
    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    /**
     * Relation: Prescription items
     */
    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    /**
     * Mark as dispensed
     */
    public function markAsDispensed($userId = null)
    {
        $this->update([
            'is_dispensed' => true,
            'dispensed_at' => now(),
            'dispensed_by' => $userId,
            'status' => 'completed',
        ]);
    }

    /**
     * Get prescription summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'prescription_number' => $this->prescription_number,
            'patient_name' => $this->patient->full_name,
            'doctor_name' => $this->doctor->full_name,
            'prescription_date' => $this->prescription_date->format('Y-m-d'),
            'status' => $this->status_label,
            'is_dispensed' => $this->is_dispensed,
            'items_count' => $this->items()->count(),
        ];
    }
}
