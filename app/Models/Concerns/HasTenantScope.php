<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * HasTenantScope — Global scope that automatically filters queries by tenant_id.
 *
 * Usage: add `use HasTenantScope;` to any model that has a tenant_id column.
 *
 * NOTE: This is intentionally NOT applied globally to all models because:
 * 1. Super admin needs to query across tenants
 * 2. Some models are shared (SubscriptionPlan, Currency, etc.)
 * 3. Seeder/console commands run without auth context
 *
 * Apply only to models where cross-tenant data leakage would be catastrophic:
 * ChatSession, AiMemory, ApiToken, WebhookSubscription
 *
 * For all other models, controllers are responsible for filtering by tenant_id.
 * This trait provides a HELPER scope for convenience, not a mandatory global scope.
 */
trait HasTenantScope
{
    /**
     * Scope: filter by current authenticated user's tenant_id.
     * Usage: Model::forCurrentTenant()->get()
     */
    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenantId = auth()->user()?->tenant_id;
        if ($tenantId) {
            $query->where($this->getTable() . '.tenant_id', $tenantId);
        }
        return $query;
    }

    /**
     * Scope: filter by specific tenant_id.
     * Usage: Model::forTenant($tenantId)->get()
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Assert that this model belongs to the given tenant.
     * Throws 403 if not.
     */
    public function assertBelongsToTenant(int $tenantId): void
    {
        abort_if($this->tenant_id !== $tenantId, 403, 'Akses ditolak: data bukan milik tenant ini.');
    }
}
