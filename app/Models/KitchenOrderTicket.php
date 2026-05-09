<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kitchen Order Ticket - Untuk Kitchen Display System
 */
class KitchenOrderTicket extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'fb_order_id',
        'ticket_number',
        'station', // grill, fry, salad, dessert, bar
        'status', // pending, preparing, ready, served, cancelled
        'priority', // normal, rush, vip
        'estimated_time',
        'started_at',
        'completed_at',
        'chef_notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_time' => 'integer', // minutes
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(FbOrder::class, 'fb_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(KitchenOrderItem::class, 'ticket_id');
    }

    /**
     * Generate ticket number
     */
    public static function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;

        return "KOT-{$date}-".str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Start preparing
     */
    public function startPreparing(): void
    {
        $this->update([
            'status' => 'preparing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as ready
     */
    public function markReady(): void
    {
        $this->update([
            'status' => 'ready',
            'completed_at' => now(),
        ]);
    }

    /**
     * Calculate elapsed time in minutes
     */
    public function getElapsedTime(): int
    {
        if (! $this->started_at) {
            return 0;
        }

        $endTime = $this->completed_at ?? now();

        return $this->started_at->diffInMinutes($endTime);
    }

    /**
     * Check if order is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->started_at || ! $this->estimated_time) {
            return false;
        }

        return $this->getElapsedTime() > $this->estimated_time;
    }
}
