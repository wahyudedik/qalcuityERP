<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diagnosis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visit_id',
        'doctor_id',
        'icd10_code',
        'icd10_description',
        'diagnosis_notes',
        'diagnosis_type',
        'status',
        'priority',
        'notes',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    /**
     * Get diagnosis type label
     */
    public function getDiagnosisTypeLabelAttribute()
    {
        $labels = [
            'primary' => 'Primary Diagnosis',
            'secondary' => 'Secondary Diagnosis',
            'differential' => 'Differential Diagnosis',
            'working' => 'Working Diagnosis',
        ];

        return $labels[$this->diagnosis_type] ?? $this->diagnosis_type;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'provisional' => 'Provisional',
            'confirmed' => 'Confirmed',
            'ruled_out' => 'Ruled Out',
            'chronic' => 'Chronic',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Scope: Primary diagnosis only
     */
    public function scopePrimary($query)
    {
        return $query->where('diagnosis_type', 'primary');
    }

    /**
     * Scope: Confirmed diagnosis only
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope: By ICD-10 code
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('icd10_code', $code);
    }

    /**
     * Relation: Visit
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    /**
     * Relation: Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Relation: Patient (through visit)
     */
    public function patient()
    {
        return $this->hasOneThrough(Patient::class, PatientVisit::class, 'id', 'id', 'visit_id', 'patient_id');
    }

    /**
     * Get formatted diagnosis
     */
    public function getFormattedAttribute()
    {
        return "{$this->icd10_code} - {$this->icd10_description}";
    }
}
