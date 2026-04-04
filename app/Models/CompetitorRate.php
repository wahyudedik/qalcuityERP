<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitorRate extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'competitor_name',
        'source',
        'rate_date',
        'rate',
        'room_type',
        'amenities',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'rate_date' => 'date',
            'rate' => 'decimal:2',
            'amenities' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get average competitor rate for a date range
     */
    public static function getAverageRate(int $tenantId, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate, ?string $competitor = null): float
    {
        $query = static::where('tenant_id', $tenantId)
            ->whereBetween('rate_date', [$startDate, $endDate]);

        if ($competitor) {
            $query->where('competitor_name', $competitor);
        }

        return $query->avg('rate') ?? 0;
    }

    /**
     * Get all unique competitors
     */
    public static function getCompetitors(int $tenantId): array
    {
        return static::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('competitor_name')
            ->toArray();
    }
}
