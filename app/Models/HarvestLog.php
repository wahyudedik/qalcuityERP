<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HarvestLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'farm_plot_id', 'crop_cycle_id', 'tenant_id', 'user_id',
        'number', 'harvest_date', 'crop_name',
        'total_qty', 'unit', 'reject_qty', 'moisture_pct',
        'storage_location', 'labor_cost', 'transport_cost',
        'weather', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date'  => 'date',
            'total_qty'     => 'decimal:3',
            'reject_qty'    => 'decimal:3',
            'moisture_pct'  => 'decimal:2',
            'labor_cost'    => 'decimal:2',
            'transport_cost'=> 'decimal:2',
        ];
    }

    public function plot(): BelongsTo { return $this->belongsTo(FarmPlot::class, 'farm_plot_id'); }
    public function cropCycle(): BelongsTo { return $this->belongsTo(CropCycle::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function grades(): HasMany { return $this->hasMany(HarvestLogGrade::class); }
    public function workers(): HasMany { return $this->hasMany(HarvestLogWorker::class); }

    /** Net quantity (total - reject) */
    public function netQty(): float
    {
        return max(0, (float) $this->total_qty - (float) $this->reject_qty);
    }

    /** Reject percentage */
    public function rejectPercent(): float
    {
        return $this->total_qty > 0
            ? round(($this->reject_qty / $this->total_qty) * 100, 1)
            : 0;
    }

    /** Total cost (labor + transport) */
    public function totalCost(): float
    {
        return (float) $this->labor_cost + (float) $this->transport_cost;
    }

    /** Cost per net kg */
    public function costPerUnit(): ?float
    {
        $net = $this->netQty();
        return $net > 0 ? round($this->totalCost() / $net, 2) : null;
    }

    /** Estimated revenue from grade prices */
    public function estimatedRevenue(): float
    {
        return (float) $this->grades()->sum(\Illuminate\Support\Facades\DB::raw('quantity * price_per_unit'));
    }

    /** Generate harvest log number */
    public static function generateNumber(string $plotCode): string
    {
        return 'HRV-' . $plotCode . '-' . date('Ymd') . '-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
    }
}
