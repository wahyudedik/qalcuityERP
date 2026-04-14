<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictiveMaintenance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'prediction_type',
        'probability',
        'predicted_date',
        'severity',
        'contributing_factors',
        'recommendations',
        'status',
        'scheduled_date',
        'scheduled_by_user_id',
        'notes',
    ];

    protected $casts = [
        'probability' => 'decimal:4',
        'predicted_date' => 'date',
        'contributing_factors' => 'array',
        'recommendations' => 'array',
        'scheduled_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
    public function scheduledBy()
    {
        return $this->belongsTo(User::class, 'scheduled_by_user_id');
    }
}