<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingIntegration extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'provider',
        'company_id',
        'api_key',
        'api_secret',
        'access_token',
        'refresh_token',
        'configuration',
        'is_active',
        'auto_sync_invoices',
        'auto_sync_payments',
        'auto_sync_expenses',
        'last_sync_at',
        'token_expires_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'auto_sync_invoices' => 'boolean',
        'auto_sync_payments' => 'boolean',
        'auto_sync_expenses' => 'boolean',
        'last_sync_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(AccountingSyncLog::class, 'integration_id');
    }
}
