<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'triage_nurse_id',
        'emergency_doctor_id',
        'admission_id',
        'case_number',
        'arrival_time',
        'triage_time',
        'treatment_started_at',
        'treatment_ended_at',
        'disposition_time',
        'triage_level',
        'triage_code',
        'chief_complaint',
        'mechanism_of_injury',
        'arrival_mode',
        'brought_by',
        'status',
        'disposition',
        'door_to_triage_minutes',
        'door_to_doctor_minutes',
        'door_to_treatment_minutes',
        'total_er_duration_minutes',
        'is_critical',
        'requires_isolation',
        'requires_immediate_intervention',
        'alert_sent',
        'notes',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'triage_time' => 'datetime',
        'treatment_started_at' => 'datetime',
        'treatment_ended_at' => 'datetime',
        'disposition_time' => 'datetime',
        'door_to_triage_minutes' => 'integer',
        'door_to_doctor_minutes' => 'integer',
        'door_to_treatment_minutes' => 'integer',
        'total_er_duration_minutes' => 'integer',
        'is_critical' => 'boolean',
        'requires_isolation' => 'boolean',
        'requires_immediate_intervention' => 'boolean',
        'alert_sent' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($case) {
            if (empty($case->case_number)) {
                $case->case_number = static::generateCaseNumber();
            }
        });
    }

    /**
     * Generate unique case number
     * Format: ER-YYYYMMDD-XXXX
     */
    public static function generateCaseNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'ER-' . $date;

        $lastCase = static::where('case_number', 'like', $prefix . '%')
            ->orderBy('case_number', 'desc')
            ->first();

        if ($lastCase) {
            $lastNumber = (int) substr($lastCase->case_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Get triage level label
     */
    public function getTriageLevelLabelAttribute()
    {
        $labels = [
            'red' => 'Resuscitation (Immediate)',
            'orange' => 'Emergent (Very Urgent)',
            'yellow' => 'Urgent',
            'green' => 'Less Urgent',
            'black' => 'Expectant (Deceased)',
        ];

        return $labels[$this->triage_level] ?? $this->triage_level;
    }

    /**
     * Get triage color for display
     */
    public function getTriageColorAttribute()
    {
        $colors = [
            'red' => '#FF0000',
            'orange' => '#FFA500',
            'yellow' => '#FFFF00',
            'green' => '#00FF00',
            'black' => '#000000',
        ];

        return $colors[$this->triage_level] ?? '#CCCCCC';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'triaged' => 'Triaged',
            'waiting' => 'Waiting',
            'in_treatment' => 'In Treatment',
            'critical' => 'Critical',
            'stable' => 'Stable',
            'admitted' => 'Admitted',
            'transferred' => 'Transferred',
            'discharged' => 'Discharged',
            'ama' => 'Against Medical Advice',
            'deceased' => 'Deceased',
            'referred' => 'Referred',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Scope: Active cases only
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['triaged', 'waiting', 'in_treatment', 'critical', 'stable']);
    }

    /**
     * Scope: Critical cases
     */
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true)
            ->orWhere('triage_level', 'red');
    }

    /**
     * Scope: By triage level
     */
    public function scopeTriageLevel($query, $level)
    {
        return $query->where('triage_level', $level);
    }

    /**
     * Scope: Today's cases
     */
    public function scopeToday($query)
    {
        return $query->whereDate('arrival_time', today());
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Triage nurse
     */
    public function triageNurse()
    {
        return $this->belongsTo(User::class, 'triage_nurse_id');
    }

    /**
     * Relation: Emergency doctor
     */
    public function emergencyDoctor()
    {
        return $this->belongsTo(Doctor::class, 'emergency_doctor_id');
    }

    /**
     * Relation: Admission
     */
    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    /**
     * Relation: Triage assessments
     */
    public function triageAssessments()
    {
        return $this->hasMany(TriageAssessment::class, 'case_id');
    }

    /**
     * Relation: Latest triage assessment
     */
    public function latestTriageAssessment()
    {
        return $this->hasOne(TriageAssessment::class, 'case_id')->latest('assessment_time');
    }

    /**
     * Relation: Emergency treatments
     */
    public function treatments()
    {
        return $this->hasMany(EmergencyTreatment::class, 'case_id');
    }

    /**
     * Relation: ER alerts
     */
    public function alerts()
    {
        return $this->hasMany(ErAlert::class, 'case_id');
    }

    /**
     * Calculate door to triage time
     */
    public function calculateDoorToTriage()
    {
        if ($this->triage_time) {
            return $this->arrival_time->diffInMinutes($this->triage_time);
        }
        return null;
    }

    /**
     * Calculate total ER duration
     */
    public function calculateTotalDuration()
    {
        $endTime = $this->disposition_time ?? $this->treatment_ended_at ?? now();
        return $this->arrival_time->diffInMinutes($endTime);
    }

    /**
     * Check if case requires immediate attention
     */
    public function requiresImmediateAttention()
    {
        return $this->triage_level === 'red' ||
            $this->triage_level === 'orange' ||
            $this->requires_immediate_intervention ||
            $this->is_critical;
    }

    /**
     * Get case summary
     */
    public function getSummaryAttribute()
    {
        return [
            'case_number' => $this->case_number,
            'patient_name' => $this->patient->full_name,
            'triage_level' => $this->triage_level,
            'triage_label' => $this->triage_level_label,
            'status' => $this->status_label,
            'arrival_time' => $this->arrival_time->format('Y-m-d H:i'),
            'chief_complaint' => $this->chief_complaint,
            'is_critical' => $this->is_critical,
            'wait_time_minutes' => $this->calculateDoorToTriage(),
        ];
    }
}
