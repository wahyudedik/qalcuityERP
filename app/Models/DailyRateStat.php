<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyRateStat extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'stat_date',
        'adr',
        'revpar',
        'total_room_revenue',
        'total_available_rooms',
        'rooms_sold',
        'average_rate_sold',
        'rate_breakdown',
    ];

    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'adr' => 'decimal:2',
            'revpar' => 'decimal:2',
            'total_room_revenue' => 'decimal:2',
            'total_available_rooms' => 'decimal:2',
            'rooms_sold' => 'integer',
            'average_rate_sold' => 'decimal:2',
            'rate_breakdown' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Calculate ADR (Average Daily Rate)
     * Formula: Total Room Revenue / Rooms Sold
     */
    public function calculateADR(): void
    {
        if ($this->rooms_sold > 0) {
            $this->adr = $this->total_room_revenue / $this->rooms_sold;
        } else {
            $this->adr = 0;
        }

        $this->save();
    }

    /**
     * Calculate RevPAR (Revenue Per Available Room)
     * Formula: Total Room Revenue / Total Available Rooms
     */
    public function calculateRevPAR(): void
    {
        if ($this->total_available_rooms > 0) {
            $this->revpar = $this->total_room_revenue / $this->total_available_rooms;
        } else {
            $this->revpar = 0;
        }

        $this->save();
    }

    /**
     * Calculate both ADR and RevPAR
     */
    public function calculateMetrics(): void
    {
        $this->calculateADR();
        $this->calculateRevPAR();
    }

    /**
     * Get or create stat for date
     */
    public static function getOrCreateForDate(int $tenantId, Carbon $date): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'stat_date' => $date],
            ['total_room_revenue' => 0, 'rooms_sold' => 0]
        );
    }
}
