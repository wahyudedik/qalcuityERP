<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'custom_role_id',
        'module',
        'action',
        'granted',
    ];

    protected $casts = [
        'granted' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function customRole(): BelongsTo
    {
        return $this->belongsTo(CustomRole::class);
    }
}
