<?php

namespace App\Http\Middleware;

use App\Services\PlanModuleMap;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePlanAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        // 1. Not authenticated → redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Super-admin has full access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $request->user()->tenant;

        // 3. No tenant → should not happen, but handle gracefully
        if (!$tenant) {
            abort(403, 'Tenant tidak ditemukan');
        }

        // 4. Null enabled_modules → backward compat for legacy tenants
        if ($tenant->enabled_modules === null) {
            return $next($request);
        }

        // 5. Resolve plan slug
        $planSlug = $tenant->subscriptionPlan->slug ?? $tenant->plan ?? null;

        // 6. Check if module is allowed for plan
        if (!PlanModuleMap::isModuleAllowedForPlan($moduleKey, $planSlug)) {
            // TASK 8.4: Redirect to informative upgrade page
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Modul {$moduleKey} memerlukan upgrade paket",
                    'module' => $moduleKey,
                    'current_plan' => $planSlug,
                ], 403);
            }

            // Get module metadata for upgrade page
            $moduleMeta = \App\Services\ModuleRecommendationService::MODULE_META[$moduleKey] ?? null;
            $moduleName = $moduleMeta['label'] ?? ucfirst(str_replace('_', ' ', $moduleKey));
            $moduleDescription = $moduleMeta['desc'] ?? null;

            // Determine which plans include this module
            $availablePlans = $this->getPlansWithModule($moduleKey);

            return response()->view('subscription.upgrade-required', [
                'moduleKey' => $moduleKey,
                'moduleName' => $moduleName,
                'moduleDescription' => $moduleDescription,
                'currentPlan' => $planSlug,
                'availablePlans' => $availablePlans,
            ], 403);
        }

        // 7. Check if tenant has enabled this module
        if (!$tenant->isModuleEnabled($moduleKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Modul {$moduleKey} belum diaktifkan untuk tenant ini",
                    'module' => $moduleKey,
                ], 403);
            }

            return redirect()->route('settings.modules')->with('error', "Modul {$moduleKey} belum diaktifkan. Silakan aktifkan di pengaturan modul.");
        }

        // 8. Allowed → proceed
        return $next($request);
    }

    /**
     * Get subscription plans that include the specified module.
     * Returns array of plan info for display on upgrade page.
     */
    private function getPlansWithModule(string $moduleKey): array
    {
        $plans = [];

        // Check each plan
        foreach (PlanModuleMap::PLAN_MODULES as $planSlug => $modules) {
            if (in_array($moduleKey, $modules, true)) {
                $plans[] = $this->getPlanInfo($planSlug);
            }
        }

        return $plans;
    }

    /**
     * Get plan information for display.
     */
    private function getPlanInfo(string $planSlug): array
    {
        $info = [
            'business' => [
                'name' => 'Business',
                'price' => 'Rp 299.000',
                'features' => [
                    '10 pengguna',
                    'Modul inti lengkap',
                    'CRM & Helpdesk',
                    'Komisi & Loyalitas',
                    'Support prioritas',
                ],
            ],
            'professional' => [
                'name' => 'Professional',
                'price' => 'Rp 599.000',
                'features' => [
                    '25 pengguna',
                    'Semua modul Business',
                    'HRM & Payroll',
                    'Manufacturing & WMS',
                    'Project Management',
                    'Agriculture & Livestock',
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 'Rp 999.000',
                'features' => [
                    'Unlimited pengguna',
                    'Semua modul tersedia',
                    'Hotel, F&B, Spa',
                    'Telecom/ISP',
                    'Multi-company',
                    'Dedicated support',
                ],
            ],
        ];

        return $info[$planSlug] ?? [
            'name' => ucfirst($planSlug),
            'price' => 'Hubungi kami',
            'features' => ['Akses modul premium'],
        ];
    }
}
