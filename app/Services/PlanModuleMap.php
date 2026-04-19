<?php

namespace App\Services;

class PlanModuleMap
{
    /**
     * Mapping of plan slugs to their allowed module keys.
     * 
     * TASK 8.1: Audit complete — all 34 modules from ModuleRecommendationService::ALL_MODULES
     * are now properly registered across all subscription plans.
     */
    const PLAN_MODULES = [
        'starter' => [
            'pos', 'inventory', 'sales', 'invoicing', 'reports',
        ],
        'trial' => [
            'pos', 'inventory', 'sales', 'invoicing', 'reports',
        ],
        'business' => [
            'pos', 'inventory', 'purchasing', 'sales', 'invoicing',
            'crm', 'accounting', 'budget', 'helpdesk', 'commission',
            'consignment', 'subscription_billing', 'reimbursement', 'reports',
            'loyalty', 'bank_reconciliation',
        ],
        'professional' => [
            'pos', 'inventory', 'purchasing', 'sales', 'invoicing',
            'hrm', 'payroll', 'crm', 'accounting', 'budget',
            'production', 'manufacturing', 'fleet', 'contracts', 'ecommerce',
            'projects', 'assets', 'commission', 'helpdesk', 'project_billing',
            'loyalty', 'bank_reconciliation', 'reports', 'landed_cost',
            'consignment', 'subscription_billing', 'reimbursement', 'wms',
            'agriculture', 'livestock',
        ],
        'enterprise' => [
            // All modules available in enterprise plan
            'pos', 'inventory', 'purchasing', 'sales', 'invoicing',
            'hrm', 'payroll', 'crm', 'accounting', 'budget',
            'production', 'manufacturing', 'fleet', 'contracts', 'ecommerce',
            'projects', 'assets', 'commission', 'helpdesk', 'project_billing',
            'loyalty', 'bank_reconciliation', 'reports', 'landed_cost',
            'consignment', 'subscription_billing', 'reimbursement', 'wms',
            'agriculture', 'livestock', 'hotel', 'fnb', 'spa', 'telecom',
        ],
    ];

    /**
     * Get the list of allowed module keys for a given plan slug.
     * Returns ALL_MODULES for unknown/null plan slugs (backward compatibility).
     */
    public static function getAllowedModules(?string $planSlug = null): array
    {
        if ($planSlug === null || !isset(self::PLAN_MODULES[$planSlug])) {
            return ModuleRecommendationService::ALL_MODULES;
        }

        return self::PLAN_MODULES[$planSlug];
    }

    /**
     * Check whether a specific module key is allowed for a given plan slug.
     */
    public static function isModuleAllowedForPlan(string $moduleKey, ?string $planSlug = null): bool
    {
        return in_array($moduleKey, self::getAllowedModules($planSlug), true);
    }

    /**
     * Filter an array of module keys, returning only those allowed for the given plan.
     */
    public static function filterAllowedModules(array $modules, ?string $planSlug = null): array
    {
        $allowed = self::getAllowedModules($planSlug);

        return array_values(array_filter($modules, fn($module) => in_array($module, $allowed, true)));
    }

    /**
     * Return the subset of modules that are NOT allowed for the given plan.
     */
    public static function getDisallowedModules(array $modules, ?string $planSlug = null): array
    {
        $allowed = self::getAllowedModules($planSlug);

        return array_values(array_filter($modules, fn($module) => !in_array($module, $allowed, true)));
    }
}
