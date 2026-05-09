<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teleconsultation extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'consultation_number',
        'consultation_date',
        'scheduled_time',
        'actual_start_time',
        'actual_end_time',
        'scheduled_duration',
        'actual_duration',
        'platform',
        'consultation_type',
        'meeting_id',
        'meeting_url',
        'meeting_password',
        'meeting_details',
        'status',
        'chief_complaint',
        'medical_history',
        'diagnosis',
        'icd10_code',
        'treatment_plan',
        'doctor_notes',
        'consultation_fee',
        'discount',
        'total_amount',
        'payment_status',
        'paid_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'rescheduled_to',
        'reschedule_reason',
        'notes',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'scheduled_duration' => 'integer',
        'actual_duration' => 'integer',
        'consultation_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'meeting_details' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Scope: By status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Scheduled today
     */
    public function scopeScheduledToday($query)
    {
        return $query->whereDate('scheduled_time', today())
            ->where('status', 'scheduled');
    }

    /**
     * Scope: In progress
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: By doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: By patient
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: Upcoming
     */
    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['scheduled', 'waiting'])
            ->where('scheduled_time', '>=', now())
            ->orderBy('scheduled_time');
    }

    /**
     * Check if consultation can be started
     */
    public function canStart()
    {
        return in_array($this->status, ['scheduled', 'waiting'])
            && $this->scheduled_time <= now()->addMinutes(15);
    }

    /**
     * Check if consultation is active
     */
    public function isActive()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if consultation is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if consultation can be cancelled
     */
    public function canCancel()
    {
        return in_array($this->status, ['scheduled', 'waiting']);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'waiting' => 'Waiting Room',
            'in_progress' => 'In Consultation',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
        };
    }

    /**
     * Get platform label
     */
    public function getPlatformLabelAttribute()
    {
        return match ($this->platform) {
            'video' => 'Video Call',
            'voice' => 'Voice Call',
            'chat' => 'Chat Consultation',
        };
    }

    /**
     * Calculate actual duration
     */
    public function calculateDuration()
    {
        if ($this->actual_start_time && $this->actual_end_time) {
            return $this->actual_start_time->diffInMinutes($this->actual_end_time);
        }

        return null;
    }

    /**
     * RELATIONSHIPS
     */

    /**
     * Get the patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the visit.
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    /**
     * Get feedback for this consultation.
     */
    public function feedback()
    {
        return $this->hasOne(TeleconsultationFeedback::class, 'consultation_id');
    }

    /**
     * Get recordings for this consultation.
     */
    public function recordings()
    {
        return $this->hasMany(TeleconsultationRecording::class, 'consultation_id');
    }

    /**
     * Get prescriptions for this consultation.
     */
    public function prescriptions()
    {
        return $this->hasMany(TelemedicinePrescription::class, 'consultation_id');
    }

    /**
     * Get payment for this consultation.
     */
    public function payment()
    {
        return $this->hasOne(TeleconsultationPayment::class, 'consultation_id');
    }

    /**
     * Get tenant settings.
     */
    public function tenantSetting()
    {
        $tenantId = $this->patient ? $this->patient->tenant_id : null;
        if (! $tenantId) {
            return null;
        }

        return TelemedicineSetting::getForTenant($tenantId);
    }

    /**
     * HELPER METHODS
     */

    /**
     * Check if consultation can be joined.
     */
    public function canJoin(): bool
    {
        return in_array($this->status, ['scheduled', 'waiting', 'in_progress'])
            && $this->scheduled_time <= now()->addMinutes(30);
    }

    /**
     * Check if patient is in waiting room.
     */
    public function isInWaitingRoom(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Get meeting URL.
     */
    public function getMeetingUrl(): string
    {
        return $this->meeting_url ?? '';
    }

    /**
     * Generate meeting details for Jitsi.
     */
    public function generateMeetingDetails(): void
    {
        $tenantId = $this->patient ? $this->patient->tenant_id : 1;
        $roomName = sprintf(
            'consultation_%d_%d_%d',
            $tenantId,
            $this->id,
            time()
        );

        $settings = TelemedicineSetting::getForTenant($tenantId);
        $meetingUrl = sprintf('%s/%s', $settings->jitsi_server_url, $roomName);

        $this->update([
            'meeting_id' => $roomName,
            'meeting_url' => $meetingUrl,
            'meeting_details' => [
                'room_name' => $roomName,
                'jitsi_server' => $settings->jitsi_server_url,
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }
}
