<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessViolation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'violation_number',
        'user_id',
        'user_name',
        'ip_address',
        'violation_type',
        'description',
        'severity',
        'patient_id',
        'model_type',
        'model_id',
        'action_attempted',
        'user_role',
        'required_role',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'notification_sent',
        'reported_to_compliance',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'is_resolved' => 'boolean',
        'notification_sent' => 'boolean',
        'reported_to_compliance' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($violation) {
            if (empty($violation->violation_number)) {
                $violation->violation_number = static::generateViolationNumber();
            }
        });
    }

    public static function generateViolationNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'VIOLATION-'.$date;

        $lastViolation = static::where('violation_number', 'like', $prefix.'%')
            ->orderBy('violation_number', 'desc')
            ->first();

        if ($lastViolation) {
            $lastNumber = (int) substr($lastViolation->violation_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix.'-'.$newNumber;
    }

    public function getSeverityLabelAttribute()
    {
        $labels = [
            'low' => '🟢 Low',
            'medium' => '🟡 Medium',
            'high' => '🟠 High',
            'critical' => '🔴 Critical',
        ];

        return $labels[$this->severity] ?? $this->severity;
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolve($resolvedBy, $notes = null)
    {
        $this->update([
            'is_resolved' => true,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function getSummaryAttribute()
    {
        return [
            'violation_number' => $this->violation_number,
            'type' => $this->violation_type,
            'severity' => $this->severity_label,
            'user' => $this->user_name,
            'description' => $this->description,
            'is_resolved' => $this->is_resolved,
            'created_at' => $this->created_at,
        ];
    }
}
