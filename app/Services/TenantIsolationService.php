<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * TenantIsolationService — Helper to enforce and verify tenant isolation.
 *
 * Use this in controllers to:
 * 1. Safely find a model by ID while enforcing tenant ownership
 * 2. Assert ownership before mutating a record
 * 3. Log isolation violations for security monitoring
 */
class TenantIsolationService
{
    /**
     * Find a model by ID, scoped to tenant. Returns 404 if not found, 403 if wrong tenant.
     * This is the SAFE alternative to Model::findOrFail($id) + abort_if check.
     *
     * Usage:
     *   $order = $this->isolation->findForTenant(SalesOrder::class, $id, $tenantId);
     */
    public function findForTenant(string $modelClass, int $id, int $tenantId): Model
    {
        /** @var Model $model */
        $model = $modelClass::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        return $model;
    }

    /**
     * Assert that a model belongs to the given tenant.
     * Logs a security warning if it doesn't.
     */
    public function assertOwnership(Model $model, int $tenantId, string $context = ''): void
    {
        if (!isset($model->tenant_id)) return; // model doesn't have tenant_id

        if ($model->tenant_id !== $tenantId) {
            Log::warning('TenantIsolation: ownership violation', [
                'model'     => get_class($model),
                'model_id'  => $model->getKey(),
                'model_tenant' => $model->tenant_id,
                'user_tenant'  => $tenantId,
                'context'      => $context,
                'user_id'      => auth()->id(),
                'ip'           => request()->ip(),
            ]);

            abort(403, 'Akses ditolak: data bukan milik tenant ini.');
        }
    }

    /**
     * Filter a query to only return records for the given tenant.
     * Convenience wrapper for ->where('tenant_id', $tenantId).
     */
    public function scopeQuery(\Illuminate\Database\Eloquent\Builder $query, int $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
