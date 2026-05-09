<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CriticalAlert extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'alert_number',
        'patient_id',
        'patient_visit_id',
        'admission_id',
        'created_by',
        'assigned_to',
        'alert_type',
        'severity',
        'alert_title',
        'alert_description',
        'clinical_findings',
        'recommended_action',
        'intervention_taken',
        'status',
        'acknowledged_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'response_time_minutes',
        'notification_sent',
        'requires_escalation',
        'escalated_at',
        'escalated_to',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'response_time_minutes' => 'integer',
        'notification_sent' => 'boolean',
        'requires_escalation' => 'boolean',
        'escalated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($alert) {
            if (empty($alert->alert_number)) {
                $alert->alert_number = static::generateAlertNumber();
            }
        });
    }

    /**
     * Generate unique alert number
     * Format: ALERT-YYYYMMDD-XXXX
     */
    public static function generateAlertNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'ALERT-'.$date;

        $lastAlert = static::where('alert_number', 'like', $prefix.'%')
            ->orderBy('alert_number', 'desc')
            ->first();

        if ($lastAlert) {
            $lastNumber = (int) substr($lastAlert->alert_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix.'-'.$newNumber;
    }

    /**
     * Get severity label with color
     */
    public function getSeverityLabelAttribute()
    {
        $labels = [
            'low' => '🟢 Low',
            'medium' => '🟡 Medium',
            'high' => '🟠 High',
            'critical' => '🔴 Critical',
            'life_threatening' => '⚫ Life Threatening',
        ];

        return $labels[$this->severity] ?? $this->severity;
    }

    /**
     * Get severity color
     */
    public function getSeverityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            'life_threatening' => 'dark',
        ];

        return $colors[$this->severity] ?? 'secondary';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'new' => 'New',
            'acknowledged' => 'Acknowledged',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'false_alarm' => 'False Alarm',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get alert type label
     */
    public function getAlertTypeLabelAttribute()
    {
        $labels = [
            'critical_lab' => 'Critical Lab Result',
            'critical_vitals' => 'Critical Vital Signs',
            'allergy' => 'Allergy Alert',
            'medication_error' => 'Medication Error',
            'cardiac_arrest' => 'Cardiac Arrest',
            'respiratory_distress' => 'Respiratory Distress',
            'sepsis' => 'Sepsis',
            'stroke' => 'Stroke',
            'trauma' => 'Trauma',
            'other' => 'Other',
        ];

        return $labels[$this->alert_type] ?? $this->alert_type;
    }

    /**
     * Check if alert is active
     */
    public function isActive()
    {
        return in_array($this->status, ['new', 'acknowledged', 'in_progress']);
    }

    /**
     * Check if alert requires immediate attention
     */
    public function requiresImmediateAttention()
    {
        return in_array($this->severity, ['critical', 'life_threatening']) && $this->isActive();
    }

    /**
     * Scope: Active alerts
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['new', 'acknowledged', 'in_progress']);
    }

    /**
     * Scope: Critical alerts
     */
    public function scopeCritical($query)
    {
        return $query->whereIn('severity', ['critical', 'life_threatening']);
    }

    /**
     * Scope: Unacknowledged alerts
     */
    public function scopeUnacknowledged($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope: By alert type
     */
    public function scopeType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope: Today's alerts
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Created by
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation: Assigned to
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge()
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'response_time_minutes' => $this->created_at->diffInMinutes(now()),
        ]);
    }

    /**
     * Mark as in progress
     */
    public function markAsInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Resolve alert
     */
    public function resolve($resolvedBy, $resolutionNotes = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'resolution_notes' => $resolutionNotes,
        ]);
    }

    /**
     * Escalate alert
     */
    public function escalate($escalatedTo)
    {
        $this->update([
            'requires_escalation' => true,
            'escalated_at' => now(),
            'escalated_to' => $escalatedTo,
        ]);
    }

    /**
     * Get alert summary
     */
    public function getSummaryAttribute()
    {
        return [
            'alert_number' => $this->alert_number,
            'type' => $this->alert_type_label,
            'severity' => $this->severity_label,
            'severity_color' => $this->severity_color,
            'title' => $this->alert_title,
            'status' => $this->status_label,
            'patient_name' => $this->patient?->full_name,
            'created_at' => $this->created_at,
            'response_time' => $this->response_time_minutes ? $this->response_time_minutes.' min' : 'Pending',
        ];
    }
}
