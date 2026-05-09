<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SharedReport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'report_id',
        'tenant_id',
        'created_by',
        'name',
        'type',
        'report_data',
        'config',
        'recipients',
        'access_level',
        'expires_at',
        'last_accessed_at',
        'access_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'report_data' => 'array',
            'config' => 'array',
            'recipients' => 'array',
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'access_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->report_id)) {
                $model->report_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the tenant that owns the shared report
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created the shared report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the report is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    /**
     * Check if the report is accessible
     */
    public function isAccessible(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    /**
     * Record access to the report
     */
    public function recordAccess(): void
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Get share URL
     */
    public function getShareUrlAttribute(): string
    {
        return route('reports.shared.view', ['id' => $this->report_id]);
    }

    /**
     * Scope a query to only include active reports
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include non-expired reports
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
