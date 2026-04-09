<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TriageAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'triage_number',
        'patient_id',
        'patient_visit_id',
        'assessed_by',
        'assessment_date',
        'triage_level',
        'triage_level_name',
        'triage_score',
        'temperature',
        'heart_rate',
        'respiratory_rate',
        'systolic_bp',
        'diastolic_bp',
        'spo2',
        'pain_scale',
        'gcs_score',
        'chief_complaint',
        'assessment_notes',
        'initial_treatment',
        'recommendations',
        'requires_immediate_intervention',
        'requires_resuscitation',
        'requires_isolation',
        'disposition',
        'admission_id',
        'assigned_doctor_id',
    ];

    protected $casts = [
        'assessment_date' => 'datetime',
        'temperature' => 'decimal:1',
        'triage_score' => 'integer',
        'heart_rate' => 'integer',
        'respiratory_rate' => 'integer',
        'systolic_bp' => 'integer',
        'diastolic_bp' => 'integer',
        'spo2' => 'integer',
        'pain_scale' => 'integer',
        'gcs_score' => 'integer',
        'requires_immediate_intervention' => 'boolean',
        'requires_resuscitation' => 'boolean',
        'requires_isolation' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($triage) {
            if (empty($triage->triage_number)) {
                $triage->triage_number = static::generateTriageNumber();
            }
            if (empty($triage->assessment_date)) {
                $triage->assessment_date = now();
            }
        });
    }

    /**
     * Generate unique triage number
     * Format: TRI-YYYYMMDD-XXXX
     */
    public static function generateTriageNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'TRI-' . $date;

        $lastTriage = static::where('triage_number', 'like', $prefix . '%')
            ->orderBy('triage_number', 'desc')
            ->first();

        if ($lastTriage) {
            $lastNumber = (int) substr($lastTriage->triage_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Get triage level label with color
     */
    public function getTriageLevelLabelAttribute()
    {
        $labels = [
            'red' => '🔴 Critical (Resusitasi)',
            'yellow' => '🟡 Emergency (Urgent)',
            'green' => '🟢 Urgent (Non-urgent)',
            'black' => '⚫ Deceased',
        ];

        return $labels[$this->triage_level] ?? $this->triage_level;
    }

    /**
     * Get triage level color
     */
    public function getTriageLevelColorAttribute()
    {
        $colors = [
            'red' => 'danger',
            'yellow' => 'warning',
            'green' => 'success',
            'black' => 'dark',
        ];

        return $colors[$this->triage_level] ?? 'secondary';
    }

    /**
     * Check if vital signs are abnormal
     */
    public function hasAbnormalVitals()
    {
        $abnormal = false;

        // Temperature (normal: 36.5-37.5)
        if ($this->temperature && ($this->temperature < 36.5 || $this->temperature > 37.5)) {
            $abnormal = true;
        }

        // Heart rate (normal: 60-100)
        if ($this->heart_rate && ($this->heart_rate < 60 || $this->heart_rate > 100)) {
            $abnormal = true;
        }

        // Respiratory rate (normal: 12-20)
        if ($this->respiratory_rate && ($this->respiratory_rate < 12 || $this->respiratory_rate > 20)) {
            $abnormal = true;
        }

        // Blood pressure (normal: 90-120 / 60-80)
        if ($this->systolic_bp && ($this->systolic_bp < 90 || $this->systolic_bp > 140)) {
            $abnormal = true;
        }

        // SpO2 (normal: 95-100)
        if ($this->spo2 && $this->spo2 < 95) {
            $abnormal = true;
        }

        return $abnormal;
    }

    /**
     * Calculate Early Warning Score (EWS)
     */
    public function calculateEarlyWarningScore()
    {
        $score = 0;

        // Respiratory rate
        if ($this->respiratory_rate) {
            if ($this->respiratory_rate <= 8 || $this->respiratory_rate >= 25)
                $score += 3;
            elseif ($this->respiratory_rate <= 11 || $this->respiratory_rate >= 21)
                $score += 2;
            elseif ($this->respiratory_rate == 12 || $this->respiratory_rate == 20)
                $score += 1;
        }

        // SpO2
        if ($this->spo2) {
            if ($this->spo2 <= 91)
                $score += 3;
            elseif ($this->spo2 <= 93)
                $score += 2;
            elseif ($this->spo2 <= 94)
                $score += 1;
        }

        // Temperature
        if ($this->temperature) {
            if ($this->temperature <= 35.0 || $this->temperature >= 39.1)
                $score += 3;
            elseif ($this->temperature <= 36.0 || $this->temperature >= 38.1)
                $score += 2;
            elseif ($this->temperature <= 36.5 || $this->temperature >= 38.0)
                $score += 1;
        }

        // Blood pressure
        if ($this->systolic_bp) {
            if ($this->systolic_bp <= 90 || $this->systolic_bp >= 220)
                $score += 3;
            elseif ($this->systolic_bp <= 100 || $this->systolic_bp >= 180)
                $score += 2;
            elseif ($this->systolic_bp <= 110 || $this->systolic_bp >= 160)
                $score += 1;
        }

        // Heart rate
        if ($this->heart_rate) {
            if ($this->heart_rate <= 40 || $this->heart_rate >= 131)
                $score += 3;
            elseif ($this->heart_rate <= 50 || $this->heart_rate >= 111)
                $score += 2;
            elseif ($this->heart_rate <= 60 || $this->heart_rate >= 91)
                $score += 1;
        }

        return $score;
    }

    /**
     * Scope: By triage level
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('triage_level', $level);
    }

    /**
     * Scope: Critical cases (red)
     */
    public function scopeCritical($query)
    {
        return $query->where('triage_level', 'red');
    }

    /**
     * Scope: Today's triage
     */
    public function scopeToday($query)
    {
        return $query->whereDate('assessment_date', today());
    }

    /**
     * Scope: Requires immediate intervention
     */
    public function scopeRequiresIntervention($query)
    {
        return $query->where('requires_immediate_intervention', true);
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Patient visit
     */
    public function patientVisit()
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }

    /**
     * Relation: Assessed by
     */
    public function assessedBy()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    /**
     * Relation: Assigned doctor
     */
    public function assignedDoctor()
    {
        return $this->belongsTo(Doctor::class, 'assigned_doctor_id');
    }

    /**
     * Get triage summary
     */
    public function getSummaryAttribute()
    {
        return [
            'triage_number' => $this->triage_number,
            'level' => $this->triage_level_label,
            'color' => $this->triage_level_color,
            'patient_name' => $this->patient?->full_name,
            'chief_complaint' => $this->chief_complaint,
            'assessment_date' => $this->assessment_date,
            'ews_score' => $this->calculateEarlyWarningScore(),
        ];
    }
}
