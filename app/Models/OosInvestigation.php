<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OosInvestigation extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'test_result_id',
        'batch_id',
        'oos_number',
        'oos_type',
        'description',
        'root_cause',
        'corrective_action',
        'preventive_action',
        'severity',
        'status',
        'assigned_to',
        'investigated_by',
        'discovery_date',
        'completion_date',
    ];

    protected $casts = [
        'discovery_date' => 'datetime',
        'completion_date' => 'datetime',
    ];

    // Severity labels
    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
            default => 'Medium'
        };
    }

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'investigating' => 'Investigating',
            'completed' => 'Completed',
            'closed' => 'Closed',
            default => 'Open'
        };
    }

    // Type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->oos_type) {
            'laboratory' => 'Laboratory',
            'manufacturing' => 'Manufacturing',
            'stability' => 'Stability',
            'complaint' => 'Customer Complaint',
            default => ucfirst($this->oos_type)
        };
    }

    // Check if critical
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    // Check if completed
    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->status === 'closed';
    }

    // Start investigation
    public function startInvestigation(int $userId): void
    {
        $this->status = 'investigating';
        $this->investigated_by = $userId;
        $this->save();
    }

    // Update root cause
    public function updateRootCause(string $rootCause): void
    {
        $this->root_cause = $rootCause;
        $this->save();
    }

    // Add corrective action
    public function addCorrectiveAction(string $action): void
    {
        $this->corrective_action = $action;
        $this->save();
    }

    // Add preventive action
    public function addPreventiveAction(string $action): void
    {
        $this->preventive_action = $action;
        $this->save();
    }

    // Complete investigation
    public function complete(int $userId): void
    {
        $this->status = 'completed';
        $this->investigated_by = $userId;
        $this->completion_date = now();
        $this->save();
    }

    // Close investigation
    public function close(): void
    {
        $this->status = 'closed';
        $this->save();
    }

    // Get days open
    public function getDaysOpenAttribute(): int
    {
        $endDate = $this->completion_date ?? now();
        return $this->discovery_date->diffInDays($endDate);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'investigating']);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('severity', 'high');
    }

    public function scopeType($query, $type)
    {
        return $query->where('oos_type', $type);
    }

    // Relationships
    public function testResult(): BelongsTo
    {
        return $this->belongsTo(QCTestResult::class, 'test_result_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CosmeticBatchRecord::class, 'batch_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    // Generate next OOS number
    public static function getNextOosNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'OOS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
