<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteManagementLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'livestock_herd_id',
        'collection_date',
        'waste_type',
        'quantity_kg',
        'volume_liters',
        'disposal_method',
        'storage_location',
        'processing_date',
        'processed_quantity_kg',
        'end_product',
        'revenue_amount',
        'environmental_impact',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'collection_date' => 'date',
        'processing_date' => 'date',
        'quantity_kg' => 'decimal:2',
        'volume_liters' => 'decimal:2',
        'processed_quantity_kg' => 'decimal:2',
        'revenue_amount' => 'decimal:2',
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

    public function getWasteTypeLabelAttribute(): string
    {
        return match ($this->waste_type) {
            'manure_solid' => 'Solid Manure',
            'manure_liquid' => 'Liquid Manure/Slurry',
            'urine' => 'Urine',
            'bedding' => 'Used Bedding Material',
            'mortality' => 'Mortality (Dead Animals)',
            'other' => 'Other Waste',
            default => ucfirst(str_replace('_', ' ', $this->waste_type))
        };
    }

    public function getDisposalMethodLabelAttribute(): string
    {
        return match ($this->disposal_method) {
            'composting' => 'Composting',
            'biogas' => 'Biogas Production',
            'field_application' => 'Field Application (Fertilizer)',
            'sale' => 'Sale to Third Party',
            'disposal' => 'Disposal/Landfill',
            'storage' => 'Storage for Later Use',
            default => ucfirst(str_replace('_', ' ', $this->disposal_method))
        };
    }

    /**
     * Check if waste was converted to revenue-generating product
     */
    public function isRevenueGenerating(): bool
    {
        return $this->disposal_method === 'sale' && $this->revenue_amount > 0;
    }

    /**
     * Calculate processing efficiency percentage
     */
    public function getProcessingEfficiencyAttribute(): ?float
    {
        if (!$this->quantity_kg || !$this->processed_quantity_kg) {
            return null;
        }

        return round(($this->processed_quantity_kg / $this->quantity_kg) * 100, 2);
    }

    /**
     * Environmental friendliness score (1-10)
     */
    public function getEnvironmentalScoreAttribute(): int
    {
        return match ($this->disposal_method) {
            'biogas' => 10, // Best - renewable energy
            'composting' => 9, // Excellent - organic fertilizer
            'field_application' => 8, // Good - natural fertilizer
            'sale' => 7, // Depends on buyer's use
            'storage' => 5, // Neutral
            'disposal' => 2, // Poor - landfill
            default => 5
        };
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('collection_date', $date);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('waste_type', $type);
    }

    public function scopeEcoFriendly($query)
    {
        return $query->whereIn('disposal_method', ['composting', 'biogas', 'field_application']);
    }

    public function scopeRevenueGenerating($query)
    {
        return $query->where('disposal_method', 'sale')
            ->where('revenue_amount', '>', 0);
    }
}
