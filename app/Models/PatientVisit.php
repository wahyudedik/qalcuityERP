<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientVisit extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'registered_by',
        'visit_number',
        'visit_type',
        'visit_date',
        'visit_time',
        'chief_complaint',
        'visit_reason',
        'visit_status',
        'queue_number',
        'queue_called_at',
        'consultation_started_at',
        'consultation_ended_at',
        'is_referral',
        'referral_from',
        'referral_to',
        'referral_reason',
        'department',
        'room_number',
        'primary_diagnosis',
        'secondary_diagnosis',
        'icd10_code',
        'outcome',
        'treatment_summary',
        'follow_up_instructions',
        'next_visit_date',
        'payment_status',
        'consultation_fee',
        'total_charges',
        'satisfaction_rating',
        'patient_feedback',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'next_visit_date' => 'date',
        'queue_called_at' => 'datetime',
        'consultation_started_at' => 'datetime',
        'consultation_ended_at' => 'datetime',
        'is_referral' => 'boolean',
        'consultation_fee' => 'decimal:2',
        'total_charges' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($visit) {
            // Auto-generate visit number if not provided
            if (empty($visit->visit_number)) {
                $visit->visit_number = static::generateVisitNumber();
            }
        });

        static::created(function ($visit) {
            // Increment patient's total visits
            $visit->patient->incrementVisits();
        });
    }

    /**
     * Generate unique visit number
     * Format: VIS-YYYYMMDD-XXXX
     */
    public static function generateVisitNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'VIS-'.$date;

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
     * Get consultation duration in minutes
     */
    public function getConsultationDurationAttribute()
    {
        if (! $this->consultation_started_at || ! $this->consultation_ended_at) {
            return null;
        }

        return $this->consultation_started_at->diffInMinutes($this->consultation_ended_at);
    }

    /**
     * Get waiting time in minutes
     */
    public function getWaitingTimeAttribute()
    {
        if (! $this->created_at || ! $this->queue_called_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->queue_called_at);
    }

    /**
     * Check if visit is completed
     */
    public function isCompleted()
    {
        return $this->visit_status === 'completed';
    }

    /**
     * Check if visit is in progress
     */
    public function isInProgress()
    {
        return in_array($this->visit_status, ['waiting', 'in_consultation']);
    }

    /**
     * Get visit type label
     */
    public function getVisitTypeLabelAttribute()
    {
        $labels = [
            'outpatient' => 'Rawat Jalan',
            'inpatient' => 'Rawat Inap',
            'emergency' => 'IGD',
            'telemedicine' => 'Telemedicine',
            'home_care' => 'Home Care',
        ];

        return $labels[$this->visit_type] ?? $this->visit_type;
    }

    /**
     * Get visit status label
     */
    public function getVisitStatusLabelAttribute()
    {
        $labels = [
            'registered' => 'Terdaftar',
            'waiting' => 'Menunggu',
            'in_consultation' => 'Konsultasi',
            'completed' => 'Selesai',
            'referred' => 'Dirujuk',
            'cancelled' => 'Dibatalkan',
        ];

        return $labels[$this->visit_status] ?? $this->visit_status;
    }

    /**
     * Scope: Today's visits
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', today());
    }

    /**
     * Scope: Visits by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('visit_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Visits by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('visit_status', $status);
    }

    /**
     * Scope: Visits by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('visit_type', $type);
    }

    /**
     * Scope: Waiting patients
     */
    public function scopeWaiting($query)
    {
        return $query->where('visit_status', 'waiting')
            ->orderBy('queue_number');
    }

    /**
     * Scope: Visits requiring follow-up
     */
    public function scopeRequiresFollowUp($query)
    {
        return $query->whereNotNull('next_visit_date')
            ->whereNotIn('visit_status', ['cancelled', 'completed']);
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
     * Relation: Registered by user
     */
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Relation: Medical records
     */
    public function medicalRecords()
    {
        return $this->hasMany(PatientMedicalRecord::class, 'visit_id');
    }

    /**
     * Relation: Prescriptions
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Relation: Lab orders
     */
    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    /**
     * Update visit status
     */
    public function updateStatus($status)
    {
        $this->update(['visit_status' => $status]);

        // Track timestamps
        if ($status === 'in_consultation' && ! $this->consultation_started_at) {
            $this->update(['consultation_started_at' => now()]);
        }

        if ($status === 'completed' && ! $this->consultation_ended_at) {
            $this->update(['consultation_ended_at' => now()]);
        }
    }

    /**
     * Get visit summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'visit_number' => $this->visit_number,
            'patient_name' => $this->patient->full_name,
            'visit_type' => $this->visit_type_label,
            'status' => $this->visit_status_label,
            'queue_number' => $this->queue_number,
            'doctor' => $this->doctor?->name,
            'department' => $this->department,
            'visit_date' => $this->visit_date->format('Y-m-d'),
            'consultation_duration' => $this->consultation_duration,
            'waiting_time' => $this->waiting_time,
        ];
    }
}
