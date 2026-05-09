<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsToTenant — Trait untuk model yang memiliki kolom tenant_id.
 *
 * Fungsi:
 * 1. Mendaftarkan TenantScope sebagai global scope otomatis
 * 2. Auto-fill tenant_id saat creating model baru
 *
 * Cara pakai:
 * ```php
 * use App\Models\Concerns\BelongsToTenant;
 *
 * class Product extends Model
 * {
 *     use BelongsToTenant;
 * }
 * ```
 *
 * CATATAN: Trait ini adalah alias/wrapper untuk App\Traits\BelongsToTenant
 * yang sudah ada. Keduanya bisa digunakan secara bergantian.
 * Gunakan App\Models\Concerns\BelongsToTenant untuk model baru.
 */
trait BelongsToTenant
{
    /**
     * Boot the trait — daftarkan TenantScope dan creating event.
     */
    protected static function bootBelongsToTenant(): void
    {
        // 1. GLOBAL SCOPE: Daftarkan TenantScope
        static::addGlobalScope(new TenantScope);

        // 2. CREATING: Auto-fill tenant_id dari user yang login
        static::creating(function ($model) {
            if (! $model->tenant_id && auth()->check()) {
                $user = auth()->user();
                if ($user->tenant_id) {
                    $model->tenant_id = $user->tenant_id;
                }
            }
        });
    }

    /**
     * Scope untuk bypass tenant filter (akses semua tenant).
     * Gunakan dengan hati-hati — hanya untuk admin/superadmin.
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope untuk akses data tenant tertentu.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class)
            ->where($this->getTable().'.tenant_id', $tenantId);
    }

    /**
     * Relasi ke model Tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
