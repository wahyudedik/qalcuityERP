<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk menyimpan routing rules AI per use case.
 *
 * Mendukung dua jenis rule:
 * - Global rule: tenant_id = NULL, berlaku untuk semua tenant
 * - Tenant-specific rule: tenant_id = X, override khusus untuk tenant X
 *
 * Prioritas resolusi: tenant-specific rule > global rule > config default
 *
 * Requirements: 1.2, 1.7, 5.3
 */
class AiUseCaseRoute extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'use_case',
        'provider',
        'model',
        'min_plan',
        'fallback_chain',
        'is_active',
        'description',
    ];

    protected $casts = [
        'fallback_chain' => 'array',
        'is_active'      => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope untuk mengakses global rules (tenant_id IS NULL) maupun
     * tenant-specific rules tanpa dibatasi oleh global scope BelongsToTenant.
     *
     * Diperlukan karena BelongsToTenant secara default memfilter berdasarkan
     * tenant user yang sedang login, sedangkan global rules memiliki tenant_id = NULL.
     *
     * Gunakan ini setiap kali perlu membaca routing rules dari luar konteks
     * satu tenant (misalnya di UseCaseRouter).
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Filter hanya routing rules yang aktif (is_active = true).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter berdasarkan use case tertentu.
     */
    public function scopeForUseCase(Builder $query, string $useCase): Builder
    {
        return $query->where('use_case', $useCase);
    }

    /**
     * Filter hanya global rules (tenant_id IS NULL).
     *
     * Otomatis melewati global scope BelongsToTenant agar record dengan
     * tenant_id = NULL dapat diambil.
     */
    public function scopeGlobalRules(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant')->whereNull('tenant_id');
    }

    /**
     * Filter hanya tenant-specific rules untuk tenant tertentu.
     *
     * Otomatis melewati global scope BelongsToTenant agar dapat digunakan
     * dari konteks SuperAdmin atau UseCaseRouter.
     */
    public function scopeTenantRules(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    /**
     * Relasi ke Tenant (nullable — global rules tidak memiliki tenant).
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
