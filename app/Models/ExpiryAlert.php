<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpiryAlert extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'alert_type',
        'alert_date',
        'expiry_date',
        'days_until_expiry',
        'alert_threshold',
        'severity',
        'is_read',
        'is_actioned',
        'action_taken',
        'actioned_at',
    ];

    protected $casts = [
        'alert_date' => 'date',
        'expiry_date' => 'date',
        'is_read' => 'boolean',
        'is_actioned' => 'boolean',
        'actioned_at' => 'datetime',
    ];

    // Severity labels
    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'info' => 'Information',
            'warning' => 'Warning',
            'critical' => 'Critical',
            'expired' => 'Expired',
            default => ucfirst($this->severity)
        };
    }

    // Alert type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->alert_type) {
            'pao_expiry' => 'Period After Opening (PAO)',
            'best_before' => 'Best Before Date',
            'near_expiry' => 'Near Expiry',
            'expired' => 'Expired',
            default => ucfirst(str_replace('_', ' ', $this->alert_type))
        };
    }

    // Mark as read
    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->save();
    }

    // Mark as actioned
    public function markAsActioned(string $action): void
    {
        $this->is_actioned = true;
        $this->action_taken = $action;
        $this->actioned_at = now();
        $this->save();
    }

    // Check if expired
    public function isExpired(): bool
    {
        return $this->days_until_expiry < 0;
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnactioned($query)
    {
        return $query->where('is_actioned', false);
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('severity', ['critical', 'expired']);
    }

    public function scopeExpired($query)
    {
        return $query->where('days_until_expiry', '<', 0);
    }

    public function scopeNearExpiry($query, $days = 90)
    {
        return $query->whereBetween('days_until_expiry', [0, $days]);
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
