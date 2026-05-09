<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationEscalation extends Model
{
    use BelongsToTenant;

    protected $table = 'notification_escalations';

    protected $fillable = [
        'tenant_id',
        'notification_id',
        'from_user_id',
        'to_user_id',
        'escalation_level',
        'reason',
        'minutes_until_escalation',
        'escalated_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'escalation_level' => 'integer',
            'minutes_until_escalation' => 'integer',
            'escalated_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(ErpNotification::class, 'notification_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if escalation has been read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark escalation as read.
     */
    public function markRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Scope for unread escalations.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for pending escalations (not yet escalated).
     */
    public function scopePending($query)
    {
        return $query->where('escalated_at', '>', now());
    }

    /**
     * Scope for overdue escalations (should have been escalated).
     */
    public function scopeOverdue($query)
    {
        return $query->where('escalated_at', '<=', now())
            ->whereNull('read_at');
    }

    /**
     * Get escalation level label.
     */
    public function getLevelLabel(): string
    {
        return match ($this->escalation_level) {
            1 => 'Level 1 - Manager',
            2 => 'Level 2 - Admin',
            3 => 'Level 3 - Super Admin',
            default => "Level {$this->escalation_level}",
        };
    }

    /**
     * Get minutes since escalation.
     */
    public function minutesSinceEscalation(): int
    {
        return (int) $this->escalated_at->diffInMinutes(now());
    }

    /**
     * Check if this escalation is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->escalated_at->lte(now()) && ! $this->isRead();
    }
}
