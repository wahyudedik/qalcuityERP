<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutpatientVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'queue_setting_id',
        'visit_number',
        'visit_date',
        'visit_time',
        'queue_number',
        'queue_position',
        'visit_type',
        'visit_category',
        'chief_complaint',
        'status',
        'registered_at',
        'called_at',
        'consultation_started_at',
        'consultation_ended_at',
        'estimated_wait_minutes',
        'actual_wait_minutes',
        'consultation_duration_minutes',
        'payment_method',
        'is_insurance',
        'insurance_provider',
        'insurance_policy_number',
        'referred_by_visit_id',
        'referral_letter_number',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'visit_time' => 'datetime:H:i',
        'registered_at' => 'datetime',
        'called_at' => 'datetime',
        'consultation_started_at' => 'datetime',
        'consultation_ended_at' => 'datetime',
        'estimated_wait_minutes' => 'integer',
        'actual_wait_minutes' => 'integer',
        'consultation_duration_minutes' => 'integer',
        'is_insurance' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($visit) {
            if (empty($visit->visit_number)) {
                $visit->visit_number = static::generateVisitNumber();
            }
            if (empty($visit->registered_at)) {
                $visit->registered_at = now();
            }
        });
    }

    /**
     * Generate unique visit number
     * Format: OPD-YYYYMMDD-XXXX
     */
    public static function generateVisitNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'OPD-'.$date;

        $lastVisit = static::where('visit_number', 'like', $prefix.'%')
            ->orderBy('visit_number', 'desc')
            ->first();

        if ($lastVisit) {
            $lastNumber = (int) substr($lastVisit->visit_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix.'-'.$newNumber;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'registered' => 'Registered',
            'waiting' => 'Waiting',
            'called' => 'Called',
            'in_consultation' => 'In Consultation',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Calculate actual wait time
     */
    public function calculateWaitTime()
    {
        if ($this->called_at && $this->registered_at) {
            return $this->registered_at->diffInMinutes($this->called_at);
        }

        return 0;
    }

    /**
     * Calculate consultation duration
     */
    public function calculateConsultationDuration()
    {
        if ($this->consultation_ended_at && $this->consultation_started_at) {
            return $this->consultation_started_at->diffInMinutes($this->consultation_ended_at);
        }

        return 0;
    }

    /**
     * Scope: Today's visits
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', today());
    }

    /**
     * Scope: Active/waiting visits
     */
    public function scopeWaiting($query)
    {
        return $query->whereIn('status', ['registered', 'waiting', 'called']);
    }

    /**
     * Scope: By doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: By status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
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
     * Relation: Queue setting
     */
    public function queueSetting()
    {
        return $this->belongsTo(QueueSetting::class);
    }

    /**
     * Relation: Queue management
     */
    public function queueManagement()
    {
        return $this->hasOne(QueueManagement::class, 'outpatient_visit_id');
    }

    /**
     * Mark as called
     */
    public function markAsCalled()
    {
        $this->update([
            'status' => 'called',
            'called_at' => now(),
            'actual_wait_minutes' => $this->calculateWaitTime(),
        ]);
    }

    /**
     * Mark as in consultation
     */
    public function markAsInConsultation()
    {
        $this->update([
            'status' => 'in_consultation',
            'consultation_started_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'consultation_ended_at' => now(),
            'consultation_duration_minutes' => $this->calculateConsultationDuration(),
        ]);
    }

    /**
     * Get visit summary
     */
    public function getSummaryAttribute()
    {
        return [
            'visit_number' => $this->visit_number,
            'queue_number' => $this->queue_number,
            'patient_name' => $this->patient->full_name,
            'doctor_name' => $this->doctor?->full_name,
            'status' => $this->status_label,
            'visit_time' => $this->visit_time,
            'wait_time' => $this->actual_wait_minutes.' minutes',
        ];
    }
}
