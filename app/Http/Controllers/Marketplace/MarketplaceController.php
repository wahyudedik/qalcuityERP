<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\DeveloperAccount;
use App\Services\Marketplace\ApiMonetizationService;
use App\Services\Marketplace\AppMarketplaceService;
use App\Services\Marketplace\DeveloperService;
use App\Services\Marketplace\ModuleBuilderService;
use App\Services\Marketplace\ThemeService;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    protected $appService;

    protected $developerService;

    protected $moduleService;

    protected $themeService;

    protected $apiService;

    public function __construct()
    {
        $this->appService = new AppMarketplaceService;
        $this->developerService = new DeveloperService;
        $this->moduleService = new ModuleBuilderService;
        $this->themeService = new ThemeService;
        $this->apiService = new ApiMonetizationService;
    }

    // ==========================================
    // APP MARKETPLACE ENDPOINTS
    // ==========================================

    /**
     * Browse marketplace apps
     */
    public function listApps(Request $request)
    {
        $filters = $request->only(['category', 'search', 'min_rating', 'price_type', 'sort_by', 'sort_order', 'per_page']);
        $apps = $this->appService->listApps($filters);

        return response()->json([
            'success' => true,
            'data' => $apps,
        ]);
    }

    /**
     * Get app details
     */
    public function showApp(string $slug)
    {
        $app = $this->appService->getAppBySlug($slug);

        if (! $app) {
            return response()->json(['success' => false, 'message' => 'App not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $app,
        ]);
    }

    /**
     * Install app to tenant
     */
    public function installApp(Request $request, int $appId)
    {
        $result = $this->appService->installApp(
            $appId,
            auth()->user()->tenant_id,
            auth()->id()
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Uninstall app
     */
    public function uninstallApp(int $installationId)
    {
        $success = $this->appService->uninstallApp($installationId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'App uninstalled' : 'Failed to uninstall app',
        ]);
    }

    /**
     * Configure installed app
     */
    public function configureApp(Request $request, int $installationId)
    {
        $success = $this->appService->configureApp($installationId, $request->configuration);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Configuration updated' : 'Failed to update configuration',
        ]);
    }

    /**
     * Submit app review
     */
    public function submitReview(Request $request, int $appId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
            'pros' => 'nullable|array',
            'cons' => 'nullable|array',
        ]);

        $result = $this->appService->submitReview(
            $appId,
            auth()->id(),
            auth()->user()->tenant_id,
            $validated['rating'],
            $validated['review'] ?? null,
            $validated['pros'] ?? [],
            $validated['cons'] ?? []
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get tenant's installed apps
     */
    public function getTenantApps()
    {
        $apps = $this->appService->getTenantApps(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'data' => $apps,
        ]);
    }

    // ==========================================
    // DEVELOPER PORTAL ENDPOINTS
    // ==========================================

    /**
     * Register as developer
     */
    public function registerDeveloper(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string',
            'bio' => 'nullable|string',
            'website' => 'nullable|url',
            'github_profile' => 'nullable|url',
            'skills' => 'nullable|array',
        ]);

        $developer = $this->developerService->registerDeveloper(auth()->id(), $validated);

        return response()->json([
            'success' => true,
            'data' => $developer,
        ]);
    }

    /**
     * Submit new app
     */
    public function submitApp(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'version' => 'nullable|string',
            'category' => 'required|string',
            'screenshots' => 'nullable|array',
            'icon_url' => 'nullable|url',
            'price' => 'nullable|numeric|min:0',
            'pricing_model' => 'nullable|string|in:one_time,subscription,freemium',
            'subscription_price' => 'nullable|numeric',
            'subscription_period' => 'nullable|string|in:monthly,yearly',
            'features' => 'nullable|array',
            'requirements' => 'nullable|array',
            'documentation_url' => 'nullable|url',
            'support_url' => 'nullable|url',
            'repository_url' => 'nullable|url',
        ]);

        $developer = DeveloperAccount::where('user_id', auth()->id())->firstOrFail();
        $app = $this->developerService->submitApp($developer->id, $validated);

        return response()->json([
            'success' => true,
            'data' => $app,
        ], 201);
    }

    /**
     * Update app
     */
    public function updateApp(Request $request, int $appId)
    {
        $success = $this->developerService->updateApp($appId, $request->all());

        return response()->json([
            'success' => $success,
            'message' => $success ? 'App updated' : 'Failed to update app',
        ]);
    }

    /**
     * Submit app for review
     */
    public function submitForReview(int $appId)
    {
        $success = $this->developerService->submitForReview($appId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'App submitted for review' : 'Failed to submit',
        ]);
    }

    /**
     * Approve app (admin)
     */
    public function approveApp(int $appId)
    {
        $success = $this->developerService->approveApp($appId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'App approved' : 'Failed to approve',
        ]);
    }

    /**
     * Reject app (admin)
     */
    public function rejectApp(Request $request, int $appId)
    {
        $validated = $request->validate(['reason' => 'required|string']);
        $success = $this->developerService->rejectApp($appId, $validated['reason']);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'App rejected' : 'Failed to reject',
        ]);
    }

    /**
     * Get developer's apps
     */
    public function getDeveloperApps()
    {
        $developer = DeveloperAccount::where('user_id', auth()->id())->firstOrFail();
        $apps = $this->developerService->getDeveloperApps($developer->id);

        return response()->json([
            'success' => true,
            'data' => $apps,
        ]);
    }

    /**
     * Get earnings summary
     */
    public function getEarningsSummary(Request $request)
    {
        $developer = DeveloperAccount::where('user_id', auth()->id())->firstOrFail();
        $summary = $this->developerService->getEarningsSummary($developer->id, $request->period);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Request payout
     */
    public function requestPayout(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payout_method' => 'required|string|in:bank_transfer,paypal,wire_transfer',
            'payout_details' => 'required|array',
        ]);

        $payout = $this->developerService->requestPayout(
            auth()->id(),
            $validated['amount'],
            $validated['payout_method'],
            $validated['payout_details']
        );

        return response()->json([
            'success' => true,
            'data' => $payout,
        ], 201);
    }

    /**
     * Process payout (admin)
     */
    public function processPayout(Request $request, int $payoutId)
    {
        $validated = $request->validate(['reference_number' => 'required|string']);
        $success = $this->developerService->processPayout($payoutId, $validated['reference_number']);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Payout processed' : 'Failed to process payout',
        ]);
    }

    /**
     * Get developer dashboard
     */
    public function getDashboard()
    {
        $data = $this->developerService->getDashboardData(auth()->id());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // ==========================================
    // CUSTOM MODULE BUILDER ENDPOINTS
    // ==========================================

    /**
     * Create custom module
     */
    public function createModule(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'version' => 'nullable|string',
            'schema' => 'required|array',
            'ui_config' => 'nullable|array',
            'permissions' => 'nullable|array',
        ]);

        $module = $this->moduleService->createModule(
            auth()->user()->tenant_id,
            auth()->id(),
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $module,
        ], 201);
    }

    /**
     * Update module schema
     */
    public function updateSchema(Request $request, int $moduleId)
    {
        $validated = $request->validate(['schema' => 'required|array']);
        $success = $this->moduleService->updateSchema($moduleId, $validated['schema']);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Schema updated' : 'Failed to update schema',
        ]);
    }

    /**
     * Add record to module
     */
    public function addRecord(Request $request, int $moduleId)
    {
        $record = $this->moduleService->addRecord(
            $moduleId,
            auth()->user()->tenant_id,
            auth()->id(),
            $request->data
        );

        return response()->json([
            'success' => true,
            'data' => $record,
        ], 201);
    }

    /**
     * Update module record
     */
    public function updateRecord(Request $request, int $recordId)
    {
        $success = $this->moduleService->updateRecord($recordId, auth()->id(), $request->data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Record updated' : 'Failed to update record',
        ]);
    }

    /**
     * Delete module record
     */
    public function deleteRecord(int $recordId)
    {
        $success = $this->moduleService->deleteRecord($recordId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Record deleted' : 'Failed to delete record',
        ]);
    }

    /**
     * Get module records
     */
    public function getRecords(Request $request, int $moduleId)
    {
        $records = $this->moduleService->getRecords($moduleId, $request->filters ?? []);

        return response()->json([
            'success' => true,
            'data' => $records,
        ]);
    }

    /**
     * Export module
     */
    public function exportModule(int $moduleId)
    {
        $export = $this->moduleService->exportModule($moduleId);

        return response()->json([
            'success' => true,
            'data' => $export,
        ]);
    }

    /**
     * Get tenant's modules
     */
    public function getTenantModules()
    {
        $modules = $this->moduleService->getTenantModules(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    // ==========================================
    // THEME MARKETPLACE ENDPOINTS
    // ==========================================

    /**
     * Browse themes
     */
    public function listThemes(Request $request)
    {
        $themes = $this->themeService->listThemes($request->only(['search', 'sort_by', 'per_page']));

        return response()->json([
            'success' => true,
            'data' => $themes,
        ]);
    }

    /**
     * Install theme
     */
    public function installTheme(Request $request, int $themeId)
    {
        $installation = $this->themeService->installTheme($themeId, auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'data' => $installation,
        ]);
    }

    /**
     * Customize theme
     */
    public function customizeTheme(Request $request, int $installationId)
    {
        $success = $this->themeService->customizeTheme($installationId, $request->customizations);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Theme customized' : 'Failed to customize theme',
        ]);
    }

    /**
     * Get active theme
     */
    public function getActiveTheme()
    {
        $theme = $this->themeService->getActiveTheme(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'data' => $theme,
        ]);
    }

    // ==========================================
    // API MONETIZATION ENDPOINTS
    // ==========================================

    /**
     * Generate API key
     */
    public function generateApiKey(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'permissions' => 'nullable|array',
            'rate_limit' => 'nullable|integer|min:100',
        ]);

        $apiKey = $this->apiService->generateApiKey(
            auth()->user()->tenant_id,
            auth()->id(),
            $validated['name'],
            $validated['permissions'] ?? [],
            $validated['rate_limit'] ?? 1000
        );

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $apiKey->key,
                'name' => $apiKey->name,
                'rate_limit' => $apiKey->rate_limit,
                'created_at' => $apiKey->created_at,
            ],
        ], 201);
    }

    /**
     * List API keys
     */
    public function listApiKeys()
    {
        $keys = ApiKey::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key' => substr($key->key, 0, 8).'...',
                    'rate_limit' => $key->rate_limit,
                    'requests_used' => $key->requests_used,
                    'is_active' => $key->is_active,
                    'last_used_at' => $key->last_used_at,
                    'created_at' => $key->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $keys,
        ]);
    }

    /**
     * Revoke API key
     */
    public function revokeApiKey(int $keyId)
    {
        $key = ApiKey::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($keyId);

        $key->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'API key revoked',
        ]);
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(Request $request)
    {
        $stats = $this->apiService->getUsageStats(
            auth()->user()->tenant_id,
            $request->period
        );

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Subscribe to API plan
     */
    public function subscribeToPlan(Request $request)
    {
        $validated = $request->validate([
            'plan_name' => 'required|string',
            'rate_limit' => 'required|integer',
            'price' => 'required|numeric',
            'billing_period' => 'nullable|string|in:monthly,yearly',
            'features' => 'nullable|array',
        ]);

        $subscription = $this->apiService->createSubscription(
            auth()->user()->tenant_id,
            $validated['plan_name'],
            $validated['rate_limit'],
            $validated['price'],
            $validated['billing_period'] ?? 'monthly',
            $validated['features'] ?? []
        );

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ], 201);
    }

    /**
     * Upgrade subscription plan
     */
    public function upgradePlan(Request $request, int $subscriptionId)
    {
        $validated = $request->validate([
            'plan_name' => 'required|string',
            'rate_limit' => 'required|integer',
            'price' => 'required|numeric',
        ]);

        $success = $this->apiService->upgradePlan(
            $subscriptionId,
            $validated['plan_name'],
            $validated['rate_limit'],
            $validated['price']
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Plan upgraded' : 'Failed to upgrade plan',
        ]);
    }

    /**
     * Get current subscription
     */
    public function getSubscription()
    {
        $subscription = $this->apiService->getSubscription(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ]);
    }
}
