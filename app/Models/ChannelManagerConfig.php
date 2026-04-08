<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelManagerConfig extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'channel',
        'api_key',
        'api_secret',
        'property_id',
        'is_active',
        'last_synced_at',
        'sync_settings',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'sync_settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
