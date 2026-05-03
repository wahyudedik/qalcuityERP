<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockVaccination extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'livestock_herd_id',
        'tenant_id',
        'user_id',
        'vaccine_name',
        'scheduled_date',
        'administered_date',
        'dose_age_days',
        'dose_method',
        'vaccinated_count',
        'cost',
        'administered_by',
        'batch_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date'    => 'date',
            'administered_date' => 'date',
            'cost'              => 'decimal:2',
        ];
    }

    public function herd(): BelongsTo
    {
        return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id');
    }
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user relationship - used by HealthController
     */
    public function administeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_date->isPast();
    }

    /**
     * Common vaccination schedules for poultry (broiler).
     */
    public const BROILER_SCHEDULE = [
        ['vaccine' => 'ND-IB (Tetes Mata)', 'day' => 4,  'method' => 'tetes mata'],
        ['vaccine' => 'Gumboro (IBD)',       'day' => 12, 'method' => 'air minum'],
        ['vaccine' => 'ND Booster',          'day' => 18, 'method' => 'air minum'],
        ['vaccine' => 'Gumboro Booster',     'day' => 21, 'method' => 'air minum'],
    ];

    public const LAYER_SCHEDULE = [
        ['vaccine' => 'Marek',               'day' => 1,  'method' => 'suntik'],
        ['vaccine' => 'ND-IB',               'day' => 4,  'method' => 'tetes mata'],
        ['vaccine' => 'Gumboro',             'day' => 12, 'method' => 'air minum'],
        ['vaccine' => 'ND Booster',          'day' => 21, 'method' => 'air minum'],
        ['vaccine' => 'Fowl Pox',            'day' => 28, 'method' => 'tusuk sayap'],
        ['vaccine' => 'ND-IB Booster',       'day' => 56, 'method' => 'suntik'],
        ['vaccine' => 'Coryza',              'day' => 70, 'method' => 'suntik'],
    ];

    /**
     * Auto-generate vaccination schedule for a herd.
     */
    public static function generateSchedule(LivestockHerd $herd): int
    {
        $schedule = match ($herd->animal_type) {
            'ayam_broiler' => self::BROILER_SCHEDULE,
            'ayam_layer'   => self::LAYER_SCHEDULE,
            default        => [],
        };

        if (empty($schedule) || !$herd->entry_date) return 0;

        $count = 0;
        foreach ($schedule as $vax) {
            $scheduledDate = $herd->entry_date->copy()->addDays($vax['day'] - $herd->entry_age_days);
            if ($scheduledDate->isPast() && $scheduledDate->diffInDays(now()) > 7) continue; // skip long past

            $exists = self::where('livestock_herd_id', $herd->id)
                ->where('vaccine_name', $vax['vaccine'])
                ->exists();
            if ($exists) continue;

            self::create([
                'livestock_herd_id' => $herd->id,
                'tenant_id'         => $herd->tenant_id,
                'vaccine_name'      => $vax['vaccine'],
                'scheduled_date'    => $scheduledDate,
                'dose_age_days'     => $vax['day'],
                'dose_method'       => $vax['method'],
                'status'            => 'scheduled',
            ]);
            $count++;
        }
        return $count;
    }
}
