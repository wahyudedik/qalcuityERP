<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * BelongsToTenant - Global scope untuk otomatis filter & set tenant_id
 *
 * Trait ini memberikan 2 fungsi utama:
 * 1. Otomatis menambahkan `->where('tenant_id', auth()->user()->tenant_id)` ke SEMUA query
 * 2. Otomatis set `tenant_id` saat create model baru
 *
 * Cara pakai:
 * ```php
 * class Product extends Model
 * {
 *     use BelongsToTenant;
 * }
 * ```
 *
 * Setelah itu, query otomatis ter-filter:
 * ```php
 * Product::all(); // Otomatis hanya product dari tenant user yang login
 * Product::where('is_active', true)->get(); // Tetap filtered by tenant_id
 * ```
 *
 * SuperAdmin bypass: User dengan role 'super_admin' TIDAK akan di-filter,
 * sehingga bisa akses data dari semua tenant.
 */
trait BelongsToTenant
{
    /**
     * Boot the trait - add global scope dan creating callback.
     */
    protected static function bootBelongsToTenant(): void
    {
        // 1. GLOBAL SCOPE: Filter semua query berdasarkan tenant_id user yang login
        static::addGlobalScope('tenant', function (Builder $query) {
            $user = Auth::user();

            // Skip jika:
            // - Tidak ada user yang login (guest, CLI, job)
            // - User adalah super_admin (bisa akses semua tenant)
            // - User tidak punya tenant_id (affiliate, dll)
            if (! $user || $user->isSuperAdmin() || ! $user->tenant_id) {
                return;
            }

            // Filter query berdasarkan tenant_id user
            $query->where('tenant_id', $user->tenant_id);
        });

        // 2. CREATING: Otomatis set tenant_id saat create model baru
        static::creating(function ($model) {
            $user = Auth::user();

            // Skip jika:
            // - Tidak ada user yang login (CLI, job, seeder)
            // - Model sudah punya tenant_id yang di-set manual
            // - User tidak punya tenant_id
            if (! $user || $model->tenant_id || ! $user->tenant_id) {
                return;
            }

            // Set tenant_id dari user yang login
            $model->tenant_id = $user->tenant_id;
        });
    }

    /**
     * Scope untuk mengakses SEMUA data (bypass tenant filter).
     *
     * Gunakan dengan HATI-HATI - hanya untuk keperluan admin/superadmin.
     *
     * Contoh:
     * ```php
     * Product::withoutTenantScope()->get(); // Ambil semua product dari semua tenant
     * ```
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope untuk akses data tenant tertentu.
     *
     * Berguna untuk SuperAdmin yang ingin lihat data tenant spesifik.
     *
     * Contoh:
     * ```php
     * Product::forTenant(5)->get(); // Product dari tenant ID 5
     * ```
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }

    /**
     * Helper method untuk check apakah model punya relasi tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
