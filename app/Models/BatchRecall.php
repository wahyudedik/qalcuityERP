<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BatchRecall extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'recall_number',
        'recall_reason',
        'description',
        'affected_regions',
        'severity',
        'status',
        'recall_date',
        'completion_date',
        'total_units',
        'units_returned',
        'units_destroyed',
        'initiated_by',
        'resolution_notes',
    ];

    protected $casts = [
        'recall_date' => 'date',
        'completion_date' => 'date',
    ];

    // Severity labels
    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'minor' => 'Minor',
            'major' => 'Major',
            'critical' => 'Critical',
            default => ucfirst($this->severity)
        };
    }

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'initiated' => 'Initiated',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    // Generate next recall number
    public static function getNextRecallNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return 'RCL-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Calculate return percentage
    public function getReturnPercentageAttribute(): float
    {
        if ($this->total_units <= 0) {
            return 0;
        }

        return round(($this->units_returned / $this->total_units) * 100, 2);
    }

    // Calculate remaining units
    public function getRemainingUnitsAttribute(): int
    {
        return $this->total_units - $this->units_returned - $this->units_destroyed;
    }

    // Complete recall
    public function complete(string $notes = ''): void
    {
        $this->status = 'completed';
        $this->completion_date = now();
        if ($notes) {
            $this->resolution_notes = $notes;
        }
        $this->save();
    }

    // Cancel recall
    public function cancel(string $notes = ''): void
    {
        $this->status = 'cancelled';
        if ($notes) {
            $this->resolution_notes = $notes;
        }
        $this->save();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['initiated', 'in_progress']);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CosmeticBatchRecord::class, 'batch_id');
    }
}
