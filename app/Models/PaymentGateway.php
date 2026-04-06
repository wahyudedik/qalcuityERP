<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'provider',
        'environment',
        'api_key',
        'secret_key',
        'merchant_id',
        'configuration',
        'is_active',
        'is_default',
        'webhook_url',
        'last_tested_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'gateway_provider', 'provider');
    }

    public function getProviderNameAttribute(): string
    {
        return match ($this->provider) {
            'midtrans' => 'Midtrans',
            'xendit' => 'Xendit',
            'duitku' => 'Duitku',
            default => ucfirst($this->provider)
        };
    }
}
