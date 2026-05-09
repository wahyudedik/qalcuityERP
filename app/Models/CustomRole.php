<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomRole extends Model
{
    use AuditsChanges, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Jumlah user yang menggunakan custom role ini.
     */
    public function userCount(): int
    {
        return User::where('tenant_id', $this->tenant_id)
            ->where('role', 'custom:' . $this->id)
            ->count();
    }

    /**
     * Cek apakah role ini memiliki permission tertentu.
     */
    public function hasPermission(string $module, string $action): bool
    {
        return $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->where('granted', true)
            ->exists();
    }
}
