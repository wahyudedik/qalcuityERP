<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope — Global Eloquent Scope untuk otomatis filter query berdasarkan tenant_id.
 *
 * Resolves tenant_id dari:
 * 1. auth()->user()->tenant_id (prioritas utama)
 * 2. Header X-Tenant-ID (untuk API request)
 * 3. session('tenant_id') (fallback)
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = $this->resolveTenantId();

        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }

    /**
     * Resolve tenant ID dari berbagai sumber.
     */
    protected function resolveTenantId(): ?int
    {
        // 1. Dari user yang sedang login
        if (auth()->check()) {
            $user = auth()->user();

            // Super admin bypass — bisa akses semua tenant
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return null;
            }

            if ($user->tenant_id) {
                return (int) $user->tenant_id;
            }
        }

        // 2. Dari header X-Tenant-ID (API request)
        if (app()->runningInConsole()) {
            return null;
        }

        try {
            $request = request();

            if ($request && $request->hasHeader('X-Tenant-ID')) {
                $tenantId = (int) $request->header('X-Tenant-ID');
                if ($tenantId > 0) {
                    return $tenantId;
                }
            }

            // 3. Dari session
            if ($request && $request->hasSession()) {
                $tenantId = session('tenant_id');
                if ($tenantId) {
                    return (int) $tenantId;
                }
            }
        } catch (\Throwable) {
            // Ignore jika tidak ada request context (CLI, testing, dll)
        }

        return null;
    }
}
