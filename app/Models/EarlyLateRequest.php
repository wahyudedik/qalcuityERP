<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EarlyLateRequest extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'guest_id',
        'request_type',
        'requested_time',
        'standard_time',
        'extra_hours',
        'extra_charge',
        'status',
        'reason',
        'notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_time' => 'datetime',
            'reviewed_at' => 'datetime',
            'extra_hours' => 'integer',
            'extra_charge' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Check if request is for early check-in
     */
    public function isEarlyCheckin(): bool
    {
        return $this->request_type === 'early_checkin';
    }

    /**
     * Check if request is for late check-out
     */
    public function isLateCheckout(): bool
    {
        return $this->request_type === 'late_checkout';
    }

    /**
     * Approve the request
     */
    public function approve(?int $userId = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $userId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Reject the request
     */
    public function reject(string $reason, ?int $userId = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reason' => $reason,
            'reviewed_by' => $userId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }
}
