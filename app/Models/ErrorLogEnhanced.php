<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLogEnhanced extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'error_type',
        'error_code',
        'error_message',
        'stack_trace',
        'file',
        'line',
        'context',
        'suggested_solutions',
        'severity',
        'resolved',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'context' => 'array',
        'suggested_solutions' => 'array',
        'line' => 'integer',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsResolved(string $notes = ''): void
    {
        $this->update([
            'resolved' => true,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'info' => 'blue',
            'warning' => 'yellow',
            'error' => 'red',
            'critical' => 'purple',
            default => 'gray'
        };
    }
}
