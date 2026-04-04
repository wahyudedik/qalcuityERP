<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueSnapshot extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'snapshot_date',
        'total_rooms',
        'occupied_rooms',
        'occupancy_rate',
        'adr',
        'revpar',
        'total_revenue',
        'total_reservations',
        'new_bookings_today',
        'cancellations_today',
        'breakdown_by_room_type',
        'breakdown_by_channel',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'total_rooms' => 'integer',
            'occupied_rooms' => 'integer',
            'occupancy_rate' => 'decimal:2',
            'adr' => 'decimal:2',
            'revpar' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'total_reservations' => 'integer',
            'new_bookings_today' => 'integer',
            'cancellations_today' => 'integer',
            'breakdown_by_room_type' => 'array',
            'breakdown_by_channel' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Calculate key metrics from raw data
     */
    public function calculateMetrics(): void
    {
        if ($this->total_rooms > 0) {
            $this->occupancy_rate = round(($this->occupied_rooms / $this->total_rooms) * 100, 2);
        }

        if ($this->occupied_rooms > 0) {
            $this->adr = round($this->total_revenue / $this->occupied_rooms, 2);
        }

        if ($this->total_rooms > 0) {
            $this->revpar = round($this->total_revenue / $this->total_rooms, 2);
        }
    }

    /**
     * Get trend compared to previous day
     */
    public function getTrend(): array
    {
        $previous = static::where('tenant_id', $this->tenant_id)
            ->where('snapshot_date', $this->snapshot_date->copy()->subDay())
            ->first();

        if (!$previous) {
            return ['direction' => 'neutral', 'change' => 0];
        }

        $change = $this->revpar - $previous->revpar;
        $percentage = $previous->revpar > 0 ? round(($change / $previous->revpar) * 100, 2) : 0;

        return [
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
            'change' => $change,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get available rooms
     */
    public function getAvailableRoomsAttribute(): int
    {
        return $this->total_rooms - $this->occupied_rooms;
    }

    /**
     * Scope for date range
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('snapshot_date', [$startDate, $endDate]);
    }

    /**
     * Get average metrics for a period
     */
    public static function getPeriodAverages(int $tenantId, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $stats = static::where('tenant_id', $tenantId)
            ->whereBetween('snapshot_date', [$startDate, $endDate])
            ->get();

        return [
            'avg_occupancy' => round($stats->avg('occupancy_rate') ?? 0, 2),
            'avg_adr' => round($stats->avg('adr') ?? 0, 2),
            'avg_revpar' => round($stats->avg('revpar') ?? 0, 2),
            'total_revenue' => round($stats->sum('total_revenue') ?? 0, 2),
            'total_bookings' => $stats->sum('new_bookings_today') ?? 0,
            'total_cancellations' => $stats->sum('cancellations_today') ?? 0,
        ];
    }
}
