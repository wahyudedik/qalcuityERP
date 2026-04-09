<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientMedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'record_type',
        'chief_complaint',
        'history_of_present_illness',
        'past_medical_history',
        'family_history',
        'social_history',
        'vital_signs',
        'physical_examination',
        'examination_findings',
        'diagnosis',
        'differential_diagnosis',
        'treatment_plan',
        'medications_prescribed',
        'procedures_performed',
        'doctor_notes',
        'patient_instructions',
        'follow_up_date',
        'follow_up_instructions',
        'status',
        'is_emergency',
        'requires_follow_up',
        'doctor_signature',
        'signed_at',
        'notes',
    ];

    protected $casts = [
        'vital_signs' => 'array',
        'follow_up_date' => 'date',
        'signed_at' => 'datetime',
        'is_emergency' => 'boolean',
        'requires_follow_up' => 'boolean',
    ];

    /**
     * Calculate BMI from vital signs
     */
    public function getBmiAttribute()
    {
        if (!$this->vital_signs || !isset($this->vital_signs['weight']) || !isset($this->vital_signs['height'])) {
            return null;
        }

        $weight = $this->vital_signs['weight']; // kg
        $height = $this->vital_signs['height'] / 100; // cm to m

        if ($height > 0) {
            return round($weight / ($height * $height), 1);
        }

        return null;
    }

    /**
     * Get BMI category
     */
    public function getBmiCategoryAttribute()
    {
        $bmi = $this->bmi;

        if (!$bmi) {
            return null;
        }

        if ($bmi < 18.5) {
            return 'Underweight';
        } elseif ($bmi < 25) {
            return 'Normal';
        } elseif ($bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }

    /**
     * Check if vital signs are abnormal
     */
    public function hasAbnormalVitalSigns()
    {
        if (!$this->vital_signs) {
            return false;
        }

        $abnormal = false;

        // Blood pressure (normal: 90-120 / 60-80)
        if (isset($this->vital_signs['bp_systolic'])) {
            if ($this->vital_signs['bp_systolic'] < 90 || $this->vital_signs['bp_systolic'] > 140) {
                $abnormal = true;
            }
        }

        if (isset($this->vital_signs['bp_diastolic'])) {
            if ($this->vital_signs['bp_diastolic'] < 60 || $this->vital_signs['bp_diastolic'] > 90) {
                $abnormal = true;
            }
        }

        // Heart rate (normal: 60-100)
        if (isset($this->vital_signs['heart_rate'])) {
            if ($this->vital_signs['heart_rate'] < 60 || $this->vital_signs['heart_rate'] > 100) {
                $abnormal = true;
            }
        }

        // Temperature (normal: 36.5-37.5)
        if (isset($this->vital_signs['temperature'])) {
            if ($this->vital_signs['temperature'] < 36.5 || $this->vital_signs['temperature'] > 37.5) {
                $abnormal = true;
            }
        }

        // SpO2 (normal: 95-100)
        if (isset($this->vital_signs['spo2'])) {
            if ($this->vital_signs['spo2'] < 95) {
                $abnormal = true;
            }
        }

        return $abnormal;
    }

    /**
     * Get abnormal vital signs list
     */
    public function getAbnormalVitalSignsAttribute()
    {
        if (!$this->vital_signs) {
            return [];
        }

        $abnormal = [];

        if (isset($this->vital_signs['bp_systolic']) && ($this->vital_signs['bp_systolic'] < 90 || $this->vital_signs['bp_systolic'] > 140)) {
            $abnormal[] = 'Blood Pressure (Systolic): ' . $this->vital_signs['bp_systolic'];
        }

        if (isset($this->vital_signs['heart_rate']) && ($this->vital_signs['heart_rate'] < 60 || $this->vital_signs['heart_rate'] > 100)) {
            $abnormal[] = 'Heart Rate: ' . $this->vital_signs['heart_rate'];
        }

        if (isset($this->vital_signs['temperature']) && ($this->vital_signs['temperature'] < 36.5 || $this->vital_signs['temperature'] > 37.5)) {
            $abnormal[] = 'Temperature: ' . $this->vital_signs['temperature'] . '°C';
        }

        if (isset($this->vital_signs['spo2']) && $this->vital_signs['spo2'] < 95) {
            $abnormal[] = 'SpO2: ' . $this->vital_signs['spo2'] . '%';
        }

        return $abnormal;
    }

    /**
     * Scope: Records requiring follow-up
     */
    public function scopeRequiresFollowUp($query)
    {
        return $query->where('requires_follow_up', true)
            ->where('status', 'completed');
    }

    /**
     * Scope: Follow-up due today or overdue
     */
    public function scopeFollowUpDue($query)
    {
        return $query->where('requires_follow_up', true)
            ->whereDate('follow_up_date', '<=', now())
            ->where('status', 'completed');
    }

    /**
     * Scope: Emergency records
     */
    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }

    /**
     * Scope: Completed records
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Records by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
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
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Relation: Visit
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    /**
     * Mark record as completed
     */
    public function complete($doctorSignature = null)
    {
        $this->update([
            'status' => 'completed',
            'doctor_signature' => $doctorSignature,
            'signed_at' => now(),
        ]);
    }

    /**
     * Check if record is signed
     */
    public function isSigned()
    {
        return !empty($this->doctor_signature) && !empty($this->signed_at);
    }

    /**
     * Get record summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'type' => $this->record_type,
            'chief_complaint' => $this->chief_complaint,
            'diagnosis' => $this->diagnosis,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'has_abnormal_vitals' => $this->hasAbnormalVitalSigns(),
            'bmi' => $this->bmi,
        ];
    }
}
