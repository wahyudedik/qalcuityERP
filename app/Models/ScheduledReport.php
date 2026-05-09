<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'description',
        'metrics',
        'frequency',
        'recipients',
        'format',
        'filters',
        'is_active',
        'last_run_at',
        'next_run',
        'last_status',
    ];

    protected $casts = [
        'metrics' => 'array',
        'recipients' => 'array',
        'filters' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->where('next_run', '<=', now());
    }

    /**
     * Mark as executed
     */
    public function markAsExecuted()
    {
        $this->update([
            'last_run_at' => now(),
            'last_status' => 'success',
            'next_run' => $this->calculateNextRun(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error)
    {
        $this->update([
            'last_run_at' => now(),
            'last_status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /**
     * Calculate next run date based on frequency
     */
    public function calculateNextRun()
    {
        return match ($this->frequency) {
            'daily' => now()->addDay()->startOfDay(),
            'weekly' => now()->addWeek()->startOfDay(),
            'monthly' => now()->addMonth()->startOfDay(),
            default => now()->addDay()->startOfDay(),
        };
    }
}
