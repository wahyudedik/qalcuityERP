<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'level',
        'type',
        'message',
        'stack_trace',
        'tenant_id',
        'user_id',
        'url',
        'ip_address',
        'user_agent',
        'context',
        'request_data',
        'exception_class',
        'file',
        'line',
        'method',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'notified',
        'notified_at',
        'occurrence_count',
        'first_occurrence',
    ];

    protected $casts = [
        'context' => 'array',
        'request_data' => 'array',
        'is_resolved' => 'boolean',
        'notified' => 'boolean',
        'resolved_at' => 'datetime',
        'notified_at' => 'datetime',
        'first_occurrence' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            // Track first occurrence
            if (empty($model->first_occurrence)) {
                $model->first_occurrence = now();
            }
        });
    }

    /**
     * Scope to get unresolved errors
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope to get critical errors
     */
    public function scopeCritical($query)
    {
        return $query->whereIn('level', ['emergency', 'alert', 'critical', 'error']);
    }

    /**
     * Scope to get recent errors
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope to get errors by tenant
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Get the tenant that owns the error
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user that triggered the error
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that resolved the error
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Mark error as resolved
     */
    public function resolve(?int $userId = null, ?string $notes = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Increment occurrence count for duplicate errors
     */
    public function incrementOccurrence(): void
    {
        $this->increment('occurrence_count');
        $this->touch();
    }

    /**
     * Get severity color for UI display
     */
    public function getSeverityColor(): string
    {
        return match ($this->level) {
            'emergency', 'alert' => 'bg-red-600',
            'critical' => 'bg-orange-600',
            'error' => 'bg-red-500',
            'warning' => 'bg-yellow-500',
            'notice' => 'bg-blue-500',
            'info' => 'bg-green-500',
            'debug' => 'bg-gray-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Get human-readable level name
     */
    public function getLevelName(): string
    {
        return ucfirst($this->level);
    }

    /**
     * Check if this is a repeated error
     */
    public function isRepeated(): bool
    {
        return $this->occurrence_count > 1;
    }

    /**
     * Get formatted context for display
     */
    public function getFormattedContext(): string
    {
        if (empty($this->context)) {
            return '{}';
        }

        return json_encode($this->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get short stack trace (first 10 lines)
     */
    public function getShortStackTrace(): string
    {
        if (empty($this->stack_trace)) {
            return '';
        }

        $lines = explode("\n", $this->stack_trace);
        return implode("\n", array_slice($lines, 0, 10));
    }
}
