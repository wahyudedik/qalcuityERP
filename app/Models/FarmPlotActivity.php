<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmPlotActivity extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'farm_plot_id', 'tenant_id', 'user_id', 'activity_type', 'date',
        'description', 'input_product', 'input_quantity', 'input_unit',
        'cost', 'harvest_qty', 'harvest_unit', 'harvest_grade', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'input_quantity' => 'decimal:3',
            'cost' => 'decimal:2',
            'harvest_qty' => 'decimal:3',
        ];
    }

    public const ACTIVITY_TYPES = [
        'planting' => '🌱 Penanaman',
        'fertilizing' => '🧪 Pemupukan',
        'spraying' => '💧 Penyemprotan',
        'watering' => '🚿 Pengairan',
        'weeding' => '🌿 Penyiangan',
        'pruning' => '✂️ Pemangkasan',
        'harvesting' => '🌾 Panen',
        'soil_prep' => '🚜 Olah Tanah',
        'other' => '📝 Lainnya',
    ];

    public function plot(): BelongsTo
    {
        return $this->belongsTo(FarmPlot::class, 'farm_plot_id');
    }

    public function cropCycle(): BelongsTo
    {
        return $this->belongsTo(CropCycle::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLabel(): string
    {
        return self::ACTIVITY_TYPES[$this->activity_type] ?? $this->activity_type;
    }
}
