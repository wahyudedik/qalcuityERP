<?php

namespace App\Http\Controllers;

use App\Events\SettingsUpdated;
use App\Services\ModuleCleanupService; // BUG-SET-002 FIX
use App\Services\ModuleRecommendationService;
use App\Services\PlanModuleMap;
use App\Services\SettingsCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModuleSettingsController extends Controller
{
    protected SettingsCacheService $cacheService;

    public function __construct(SettingsCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index()
    {
        $tenant = auth()->user()->tenant;
        $enabled = $tenant->enabledModules();
        $planSlug = $tenant->subscriptionPlan->slug ?? $tenant->plan ?? null;
        $allowedByPlan = PlanModuleMap::getAllowedModules($planSlug);

        return view('settings.modules', [
            'tenant'        => $tenant,
            'enabled'       => $enabled,
            'meta'          => ModuleRecommendationService::MODULE_META,
            'all'           => ModuleRecommendationService::ALL_MODULES,
            'planSlug'      => $planSlug,
            'allowedByPlan' => $allowedByPlan,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', 'in:' . implode(',', ModuleRecommendationService::ALL_MODULES)],
            // BUG-SET-002 FIX: Cleanup strategy for disabled modules
            'cleanup_strategy' => ['nullable', 'in:keep,archive,soft_delete'],
        ]);

        $tenant = auth()->user()->tenant;
        $oldModules = $tenant->enabledModules();
        $newModules = $request->input('modules', []);
        $cleanupStrategy = $request->input('cleanup_strategy', 'keep');

        // Plan-based module validation (skip for legacy tenants with null enabled_modules)
        $planSlug = $tenant->subscriptionPlan->slug ?? $tenant->plan ?? null;
        if ($tenant->enabled_modules !== null) {
            $disallowedModules = PlanModuleMap::getDisallowedModules($newModules, $planSlug);

            if (!empty($disallowedModules)) {
                $disallowedLabels = array_map(function ($key) {
                    return ModuleRecommendationService::MODULE_META[$key]['label'] ?? $key;
                }, $disallowedModules);

                $errorMessage = 'Modul berikut tidak tersedia untuk paket ' . strtoupper($planSlug ?? 'Anda') . ': '
                    . implode(', ', $disallowedLabels)
                    . '. Silakan upgrade paket untuk mengaktifkan modul ini.';

                if ($request->expectsJson()) {
                    return response()->json(['errors' => ['modules' => [$errorMessage]]], 422);
                }

                return back()
                    ->withInput()
                    ->with('error', $errorMessage)
                    ->with('upgrade_required', true);
            }
        }

        // BUG-SET-002 FIX: Detect disabled modules and cleanup
        $disabledModules = array_diff($oldModules, $newModules);
        $cleanupResults = [];

        if (!empty($disabledModules)) {
            $cleanupService = app(ModuleCleanupService::class);

            foreach ($disabledModules as $module) {
                // Analyze impact first
                $impact = $cleanupService->analyzeImpact($tenant->id, $module);

                // Log the cleanup action
                Log::info('BUG-SET-002: Module disabled', [
                    'tenant_id' => $tenant->id,
                    'module' => $module,
                    'strategy' => $cleanupStrategy,
                    'records_affected' => $impact['total_records'] ?? 0,
                ]);

                // Perform cleanup if there's data
                if (($impact['total_records'] ?? 0) > 0) {
                    $result = $cleanupService->cleanupModule($tenant->id, $module, $cleanupStrategy);
                    $cleanupResults[$module] = $result;
                }
            }
        }

        // Update enabled modules
        $tenant->update(['enabled_modules' => $newModules]);

        // BUG-SET-001 FIX: Dispatch event to clear module settings cache
        event(new SettingsUpdated(
            type: 'module',
            tenantId: $tenant->id,
            metadata: [
                'old_modules' => $oldModules,
                'new_modules' => $newModules,
                'disabled_modules' => $disabledModules,
                'cleanup_strategy' => $cleanupStrategy,
                'cleanup_results' => $cleanupResults,
            ]
        ));

        // Also clear specific tenant cache
        $this->cacheService->clearTenantCache($tenant->id);

        $message = 'Pengaturan modul berhasil disimpan.';
        if (!empty($disabledModules)) {
            $message .= ' Modul dinonaktifkan: ' . implode(', ', $disabledModules) . '.';
            if (!empty($cleanupResults)) {
                $message .= ' Data telah di-' . $cleanupStrategy . '.';
            }
        }

        return back()->with('success', $message);
    }

    /** AJAX: get AI recommendation for an industry */
    public function recommend(Request $request)
    {
        $industry = $request->input('industry', 'other');
        $svc = new ModuleRecommendationService();
        return response()->json($svc->recommend($industry));
    }

    // BUG-SET-002 FIX: Analyze impact before disabling module
    public function analyzeImpact(Request $request)
    {
        $request->validate([
            'module' => 'required|string|in:' . implode(',', ModuleRecommendationService::ALL_MODULES),
        ]);

        $tenant = auth()->user()->tenant;
        $cleanupService = app(ModuleCleanupService::class);
        $impact = $cleanupService->analyzeImpact($tenant->id, $request->module);

        return response()->json($impact);
    }

    // BUG-SET-002 FIX: Get cleanup summary for dashboard
    public function cleanupSummary()
    {
        $tenant = auth()->user()->tenant;
        $cleanupService = app(ModuleCleanupService::class);
        $summary = $cleanupService->getTenantCleanupSummary($tenant->id);

        return response()->json($summary);
    }

    // BUG-SET-002 FIX: Restore archived data when re-enabling module
    public function restoreData(Request $request)
    {
        $request->validate([
            'module' => 'required|string|in:' . implode(',', ModuleRecommendationService::ALL_MODULES),
        ]);

        $tenant = auth()->user()->tenant;
        $cleanupService = app(ModuleCleanupService::class);
        $result = $cleanupService->restoreArchivedData($tenant->id, $request->module);

        if ($result['success']) {
            return back()->with(
                'success',
                "Data modul {$request->module} berhasil dipulihkan ({$result['records_restored']} records)."
            );
        }

        return back()->with('error', 'Gagal memulihkan data: ' . implode(', ', $result['errors']));
    }
}
