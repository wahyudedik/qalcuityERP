<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use AuditsChanges;
    protected $fillable = [
        'tenant_id',
        'asset_code',
        'name',
        'category',
        'brand',
        'model',
        'serial_number',
        'location',
        'purchase_date',
        'purchase_price',
        'current_value',
        'salvage_value',
        'useful_life_years',
        'depreciation_method',
        'status',
        'notes',
    ];

    protected $casts = ['purchase_date' => 'date', 'purchase_price' => 'float', 'current_value' => 'float', 'salvage_value' => 'float'];

    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class);
    }
    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function monthlyDepreciation(): float
    {
        if ($this->depreciation_method === 'straight_line') {
            return ($this->purchase_price - $this->salvage_value) / max(1, $this->useful_life_years * 12);
        }
        // Declining balance: 2/useful_life per year / 12
        $annualRate = 2 / max(1, $this->useful_life_years);
        return $this->current_value * $annualRate / 12;
    }
}
