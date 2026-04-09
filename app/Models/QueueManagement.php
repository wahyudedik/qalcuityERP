<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueManagement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_number',
        'token_number',
        'queue_type',
        'outpatient_visit_id',
        'patient_id',
        'doctor_id',
        'department_id',
        'queue_setting_id',
        'status',
        'queue_position',
        'estimated_wait_minutes',
        'registered_at',
        'called_at',
        'serving_at',
        'completed_at',
        'actual_wait_minutes',
        'service_duration_minutes',
        'priority',
        'priority_position',
        'notes',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'called_at' => 'datetime',
        'serving_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_wait_minutes' => 'integer',
        'actual_wait_minutes' => 'integer',
        'service_duration_minutes' => 'integer',
        'queue_position' => 'integer',
        'priority_position' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($queue) {
            if (empty($queue->queue_number)) {
                $queue->queue_number = static::generateQueueNumber();
            }
            if (empty($queue->token_number)) {
                $queue->token_number = static::generateTokenNumber($queue->queue_type);
            }
            if (empty($queue->registered_at)) {
                $queue->registered_at = now();
            }
        });
    }

    /**
     * Generate unique queue number
     * Format: Q-YYYYMMDD-XXXX
     */
    public static function generateQueueNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'Q-' . $date;

        $lastQueue = static::where('queue_number', 'like', $prefix . '%')
            ->orderBy('queue_number', 'desc')
            ->first();

        if ($lastQueue) {
            $lastNumber = (int) substr($lastQueue->queue_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Generate token number
     * Format: A001, B002, etc. (based on queue type)
     */
    public static function generateTokenNumber($queueType)
    {
        $typePrefixes = [
            'outpatient' => 'A',
            'specialist' => 'B',
            'pharmacy' => 'C',
            'laboratory' => 'D',
            'radiology' => 'E',
            'billing' => 'F',
            'registration' => 'G',
        ];

        $prefix = $typePrefixes[$queueType] ?? 'Z';
        $date = now()->format('Ymd');
        $fullPrefix = $prefix . '-' . $date;

        $lastQueue = static::where('token_number', 'like', $fullPrefix . '%')
            ->orderBy('token_number', 'desc')
            ->first();

        if ($lastQueue) {
            $lastNumber = (int) substr($lastQueue->token_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'waiting' => 'Waiting',
            'called' => 'Called',
            'serving' => 'Serving',
            'completed' => 'Completed',
            'skipped' => 'Skipped',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get queue type label
     */
    public function getQueueTypeLabelAttribute()
    {
        $labels = [
            'outpatient' => 'Outpatient',
            'specialist' => 'Specialist',
            'pharmacy' => 'Pharmacy',
            'laboratory' => 'Laboratory',
            'radiology' => 'Radiology',
            'billing' => 'Billing',
            'registration' => 'Registration',
        ];

        return $labels[$this->queue_type] ?? $this->queue_type;
    }

    /**
     * Scope: Waiting queue
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting')
            ->orderBy('queue_position');
    }

    /**
     * Scope: Today's queue
     */
    public function scopeToday($query)
    {
        return $query->whereDate('registered_at', today());
    }

    /**
     * Scope: By queue type
     */
    public function scopeType($query, $type)
    {
        return $query->where('queue_type', $type);
    }

    /**
     * Scope: By doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: Priority queue
     */
    public function scopePriority($query)
    {
        return $query->where('priority', '!=', 'normal')
            ->orderBy('priority_position');
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
     * Relation: Outpatient visit
     */
    public function outpatientVisit()
    {
        return $this->belongsTo(OutpatientVisit::class, 'outpatient_visit_id');
    }

    /**
     * Mark as called
     */
    public function markAsCalled()
    {
        $this->update([
            'status' => 'called',
            'called_at' => now(),
            'actual_wait_minutes' => $this->registered_at->diffInMinutes(now()),
        ]);
    }

    /**
     * Mark as serving
     */
    public function markAsServing()
    {
        $this->update([
            'status' => 'serving',
            'serving_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'service_duration_minutes' => $this->serving_at ? $this->serving_at->diffInMinutes(now()) : 0,
        ]);
    }

    /**
     * Mark as skipped
     */
    public function markAsSkipped()
    {
        $this->update(['status' => 'skipped']);
    }

    /**
     * Get queue summary
     */
    public function getSummaryAttribute()
    {
        return [
            'queue_number' => $this->queue_number,
            'token_number' => $this->token_number,
            'type' => $this->queue_type_label,
            'status' => $this->status_label,
            'position' => $this->queue_position,
            'patient_name' => $this->patient?->full_name,
            'doctor_name' => $this->doctor?->full_name,
            'wait_time' => $this->actual_wait_minutes ? $this->actual_wait_minutes . ' min' : 'Waiting',
        ];
    }
}
