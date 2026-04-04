<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintJob extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'job_type',
        'reference_id',
        'reference_number',
        'printer_type',
        'printer_destination',
        'print_data',
        'status',
        'error_message',
        'retry_count',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'print_data' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Mark job as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark job as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }

    /**
     * Cancel job
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Check if job can be retried
     */
    public function canRetry(int $maxRetries = 3): bool
    {
        return $this->status === 'failed' && $this->retry_count < $maxRetries;
    }

    /**
     * Retry the job
     */
    public function retry(): void
    {
        $this->update([
            'status' => 'pending',
            'error_message' => null,
        ]);
    }

    /**
     * Scope: Get pending jobs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get failed jobs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
