<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'asset_id', 'type', 'description', 'scheduled_date',
        'completed_date', 'cost', 'vendor', 'status', 'notes',
    ];

    protected $casts = ['scheduled_date' => 'date', 'completed_date' => 'date', 'cost' => 'float'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
