<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveryQueue extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'operation_type',
        'operation_data',
        'failure_reason',
        'retry_count',
        'max_retries',
        'status',
        'last_error',
        'next_retry_at',
        'completed_at',
    ];

    protected $casts = [
        'operation_data' => 'array',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'last_error' => 'array',
        'next_retry_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(): void
    {
        $this->increment('retry_count');

        if ($this->retry_count >= $this->max_retries) {
            $this->update(['status' => 'failed']);
        } else {
            // Exponential backoff: 1min, 5min, 15min
            $delayMinutes = pow(5, $this->retry_count - 1);
            $this->update([
                'status' => 'retrying',
                'next_retry_at' => now()->addMinutes($delayMinutes),
            ]);
        }
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if ready for retry
     */
    public function isReadyForRetry(): bool
    {
        return $this->status === 'retrying'
            && $this->next_retry_at
            && now()->greaterThanOrEqualTo($this->next_retry_at);
    }
}