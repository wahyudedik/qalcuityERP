<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read Pharmacy|null $pharmacy
 */
class TelemedicinePrescription extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'consultation_id',
        'patient_id',
        'doctor_id',
        'pharmacy_id',
        'prescription_number',
        'prescription_date',
        'valid_until',
        'prescription_data',
        'diagnosis',
        'icd10_code',
        'instructions',
        'special_notes',
        'status',
        'sent_to_pharmacy',
        'sent_at',
        'pharmacy_status',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'valid_until' => 'date',
        'prescription_data' => 'array',
        'sent_to_pharmacy' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the consultation that owns the prescription
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Teleconsultation::class);
    }

    /**
     * Get the patient
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the pharmacy
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    /**
     * Scope: Active prescriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('valid_until', '>=', today());
    }

    /**
     * Scope: Prescriptions for a doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: Prescriptions for a patient
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Check if prescription is expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until < today() || $this->status === 'expired';
    }

    /**
     * Check if prescription can be dispensed
     */
    public function canDispense(): bool
    {
        return $this->status === 'active'
            && ! $this->isExpired()
            && $this->sent_to_pharmacy
            && in_array($this->pharmacy_status, ['confirmed', 'preparing', 'ready']);
    }
}
