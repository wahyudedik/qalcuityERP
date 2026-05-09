<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property Carbon $appointment_date
 * @property Carbon|null $checked_in_at
 * @property Carbon|null $consultation_started_at
 * @property Carbon|null $consultation_ended_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $last_reminder_sent_at
 */
class Appointment extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    // Status constants
    const STATUS_SCHEDULED = 'scheduled';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_CHECKED_IN = 'checked_in';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_NO_SHOW = 'no_show';

    const STATUS_RESCHEDULED = 'rescheduled';

    const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CONFIRMED,
        self::STATUS_CHECKED_IN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
        self::STATUS_RESCHEDULED,
    ];

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'doctor_id',
        'department_id',
        'created_by',
        'appointment_number',
        'appointment_date',
        'appointment_time',
        'estimated_duration',
        'appointment_type',
        'visit_type',
        'status',
        'reason_for_visit',
        'symptoms',
        'special_requests',
        'is_urgent',
        'reminder_sent_24h',
        'reminder_sent_1h',
        'last_reminder_sent_at',
        'checked_in_at',
        'consultation_started_at',
        'consultation_ended_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'rescheduled_to_id',
        'notification_method',
        'notification_message',
        'visit_id',
        'satisfaction_rating',
        'patient_feedback',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'checked_in_at' => 'datetime',
        'consultation_started_at' => 'datetime',
        'consultation_ended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'is_urgent' => 'boolean',
        'reminder_sent_24h' => 'boolean',
        'reminder_sent_1h' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($appointment) {
            // Auto-generate appointment number if not provided
            if (empty($appointment->appointment_number)) {
                $appointment->appointment_number = static::generateAppointmentNumber();
            }
        });
    }

    /**
     * Generate unique appointment number
     * Format: APT-YYYYMMDD-XXXX
     */
    public static function generateAppointmentNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'APT-'.$date;

        $lastAppointment = static::where('appointment_number', 'like', $prefix.'%')
            ->orderBy('appointment_number', 'desc')
            ->first();

        if ($lastAppointment) {
            $lastNumber = (int) substr($lastAppointment->appointment_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix.'-'.$newNumber;
    }

    /**
     * Get appointment date and time combined
     */
    public function getAppointmentDateTimeAttribute()
    {
        return $this->appointment_date->setTimeFromTimeString($this->appointment_time);
    }

    /**
     * Get estimated end time
     */
    public function getEstimatedEndTimeAttribute()
    {
        $startTime = $this->appointment_date->setTimeFromTimeString($this->appointment_time);

        return $startTime->copy()->addMinutes($this->estimated_duration);
    }

    /**
     * Check if appointment is upcoming
     */
    public function isUpcoming()
    {
        return $this->status === 'scheduled' &&
            $this->appointment_datetime > now();
    }

    /**
     * Check if appointment is today
     */
    public function isToday()
    {
        return $this->appointment_date->isToday();
    }

    /**
     * Check if appointment can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Check if appointment can be rescheduled
     */
    public function canBeRescheduled()
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'scheduled' => 'Dijadwalkan',
            'confirmed' => 'Dikonfirmasi',
            'checked_in' => 'Check-in',
            'in_progress' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'no_show' => 'Tidak Hadir',
            'rescheduled' => 'Dijadwalkan Ulang',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get appointment type label
     */
    public function getAppointmentTypeLabelAttribute()
    {
        $labels = [
            'consultation' => 'Konsultasi',
            'follow_up' => 'Kontrol Ulang',
            'check_up' => 'Medical Check-up',
            'procedure' => 'Tindakan',
            'telemedicine' => 'Telemedicine',
            'emergency' => 'Darurat',
        ];

        return $labels[$this->appointment_type] ?? $this->appointment_type;
    }

    /**
     * Scope: Today's appointments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    /**
     * Scope: Upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('appointment_date', '>=', today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }

    /**
     * Scope: Appointments by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Appointments by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Appointments by doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: Appointments by patient
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: Pending reminders (24 hours before)
     */
    public function scopeNeeds24HourReminder($query)
    {
        return $query->where('status', 'scheduled')
            ->whereDate('appointment_date', now()->addDay())
            ->where('reminder_sent_24h', false);
    }

    /**
     * Scope: Pending reminders (1 hour before)
     */
    public function scopeNeeds1HourReminder($query)
    {
        return $query->where('status', 'confirmed')
            ->whereDate('appointment_date', today())
            ->where('reminder_sent_1h', false);
    }

    /**
     * Relation: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
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
     * Relation: Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relation: Created by user
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation: Cancelled by user
     */
    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Relation: Rescheduled to appointment
     */
    public function rescheduledTo()
    {
        return $this->belongsTo(Appointment::class, 'rescheduled_to_id');
    }

    /**
     * Relation: Patient visit (after completion)
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    /**
     * Confirm appointment
     */
    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
    }

    /**
     * Cancel appointment
     */
    public function cancel($reason, $cancelledBy = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Check-in patient
     */
    public function checkIn()
    {
        $this->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Start consultation
     */
    public function startConsultation()
    {
        $this->update([
            'status' => 'in_progress',
            'consultation_started_at' => now(),
        ]);
    }

    /**
     * Complete appointment
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'consultation_ended_at' => now(),
        ]);
    }

    /**
     * Mark as no-show
     */
    public function markAsNoShow()
    {
        $this->update(['status' => 'no_show']);
    }

    /**
     * Send reminder
     */
    public function sendReminder($hoursBefore)
    {
        if ($hoursBefore == 24) {
            $this->update([
                'reminder_sent_24h' => true,
                'last_reminder_sent_at' => now(),
            ]);
        } elseif ($hoursBefore == 1) {
            $this->update([
                'reminder_sent_1h' => true,
                'last_reminder_sent_at' => now(),
            ]);
        }
    }

    /**
     * Get appointment summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'appointment_number' => $this->appointment_number,
            'patient_name' => $this->patient->full_name,
            'doctor_name' => $this->doctor?->name,
            'appointment_date' => $this->appointment_date->format('Y-m-d'),
            'appointment_time' => $this->appointment_time,
            'type' => $this->appointment_type_label,
            'status' => $this->status_label,
            'is_urgent' => $this->is_urgent,
        ];
    }
}
