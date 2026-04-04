<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NightAuditBatch extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'batch_number',
        'audit_date',
        'started_at',
        'completed_at',
        'auditor_id',
        'status',
        'notes',
        'summary_data',
        'total_rooms',
        'occupied_rooms',
        'occupancy_rate',
        'total_room_revenue',
        'total_fb_revenue',
        'total_other_revenue',
        'total_revenue',
        'adr',
        'revpar',
    ];

    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'summary_data' => 'array',
            'total_rooms' => 'integer',
            'occupied_rooms' => 'integer',
            'occupancy_rate' => 'decimal:2',
            'total_room_revenue' => 'decimal:2',
            'total_fb_revenue' => 'decimal:2',
            'total_other_revenue' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'adr' => 'decimal:2',
            'revpar' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function revenuePostings(): HasMany
    {
        return $this->hasMany(RevenuePosting::class, 'audit_batch_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(NightAuditLog::class, 'audit_batch_id');
    }

    /**
     * Generate unique batch number
     */
    public static function generateBatchNumber(\Carbon\Carbon $auditDate): string
    {
        return "NA-" . $auditDate->format('Ymd');
    }

    /**
     * Check if batch is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Calculate and save ADR (Average Daily Rate)
     */
    public function calculateADR(): void
    {
        if ($this->occupied_rooms > 0) {
            $this->adr = $this->total_room_revenue / $this->occupied_rooms;
        } else {
            $this->adr = 0;
        }

        $this->save();
    }

    /**
     * Calculate and save RevPAR (Revenue Per Available Room)
     */
    public function calculateRevPAR(): void
    {
        if ($this->total_rooms > 0) {
            $this->revpar = $this->total_room_revenue / $this->total_rooms;
        } else {
            $this->revpar = 0;
        }

        $this->save();
    }

    /**
     * Get occupancy rate percentage
     */
    public function getOccupancyRatePercentageAttribute(): float
    {
        if ($this->total_rooms > 0) {
            return ($this->occupied_rooms / $this->total_rooms) * 100;
        }
        return 0;
    }

    /**
     * Mark batch as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Recalculate metrics
        $this->calculateADR();
        $this->calculateRevPAR();
    }

    /**
     * Mark batch as failed
     */
    public function markAsFailed(string $reason = ''): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }
}
