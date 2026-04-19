<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticsProvider extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'account_number',
        'api_key',
        'api_secret',
        'configuration',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}