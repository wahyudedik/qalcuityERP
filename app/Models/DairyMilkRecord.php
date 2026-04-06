<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DairyMilkRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'livestock_herd_id',
        'animal_id',
        'record_date',
        'milking_session',
        'milk_volume_liters',
        'fat_percentage',
        'protein_percentage',
        'lactose_percentage',
        'somatic_cell_count',
        'quality_grade',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'record_date' => 'date',
        'milk_volume_liters' => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'protein_percentage' => 'decimal:2',
        'lactose_percentage' => 'decimal:2',
        'somatic_cell_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function herd(): BelongsTo
    {
        return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getQualityGradeLabelAttribute(): string
    {
        return match ($this->quality_grade) {
            'A' => 'Grade A (Premium)',
            'B' => 'Grade B (Standard)',
            'C' => 'Grade C (Below Standard)',
            default => 'Not Graded'
        };
    }

    public function getSessionLabelAttribute(): string
    {
        return match ($this->milking_session) {
            'morning' => 'Morning (05:00-08:00)',
            'afternoon' => 'Afternoon (13:00-16:00)',
            'evening' => 'Evening (18:00-21:00)',
            default => ucfirst($this->milking_session)
        };
    }

    /**
     * Check if milk quality is good based on SCC and fat content
     */
    public function isGoodQuality(): bool
    {
        // Good quality: SCC < 400,000 cells/ml and fat > 3.5%
        $sccGood = !$this->somatic_cell_count || $this->somatic_cell_count < 400000;
        $fatGood = !$this->fat_percentage || $this->fat_percentage >= 3.5;

        return $sccGood && $fatGood;
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('record_date', $date);
    }

    public function scopeByHerd($query, $herdId)
    {
        return $query->where('livestock_herd_id', $herdId);
    }

    public function scopeHighQuality($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('somatic_cell_count')
                ->orWhere('somatic_cell_count', '<', 400000);
        })
            ->where(function ($q) {
                $q->whereNull('fat_percentage')
                    ->orWhere('fat_percentage', '>=', 3.5);
            });
    }
}
