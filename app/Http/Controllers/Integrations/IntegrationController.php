<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationSyncLog;
use App\Jobs\Integrations\SyncProductsJob;
use App\Jobs\Integrations\SyncOrdersJob;
use App\Jobs\Integrations\SyncInventoryJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class IntegrationController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display integrations marketplace
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');

        $integrations = Integration::where('tenant_id', Auth::user()->tenant_id)
            ->when($type !== 'all', fn($q) => $q->where('type', $type))
            ->with(['syncLogs' => fn($q) => $q->latest()->limit(5)])
            ->latest()
            ->paginate(20);

        $availableIntegrations = [
            'e-commerce' => [
                ['slug' => 'shopify', 'name' => 'Shopify', 'description' => 'Sync products, orders, and inventory with Shopify', 'logo' => 'shopify.png'],
                ['slug' => 'woocommerce', 'name' => 'WooCommerce', 'description' => 'Connect your WooCommerce store', 'logo' => 'woocommerce.png'],
                ['slug' => 'tokopedia', 'name' => 'Tokopedia', 'description' => 'Integration with Tokopedia marketplace', 'logo' => 'tokopedia.png'],
                ['slug' => 'shopee', 'name' => 'Shopee', 'description' => 'Integration with Shopee marketplace', 'logo' => 'shopee.png'],
                ['slug' => 'lazada', 'name' => 'Lazada', 'description' => 'Integration with Lazada marketplace', 'logo' => 'lazada.png'],
            ],
            'payment' => [
                ['slug' => 'midtrans', 'name' => 'Midtrans', 'description' => 'Payment gateway for Indonesia (VA, Cards, E-Wallets)', 'logo' => 'midtrans.png'],
                ['slug' => 'xendit', 'name' => 'Xendit', 'description' => 'Payment gateway for Southeast Asia', 'logo' => 'xendit.png'],
            ],
            'logistics' => [
                ['slug' => 'rajaongkir', 'name' => 'RajaOngkir', 'description' => 'Shipping rate calculator (JNE, TIKI, POS, etc.)', 'logo' => 'rajaongkir.png'],
            ],
            'automation' => [
                ['slug' => 'zapier', 'name' => 'Zapier / Make.com', 'description' => 'Connect to 5000+ apps via webhooks', 'logo' => 'zapier.png'],
            ],
        ];

        return view('integrations.index', compact('integrations', 'availableIntegrations', 'type'));
    }

    /**
     * Show integration details
     */
    public function show(Integration $integration)
    {
        $this->authorize('view', $integration);

        $integration->load(['syncLogs' => fn($q) => $q->latest()->limit(50), 'webhooks']);

        $stats = [
            'total_syncs' => $integration->syncLogs()->count(),
            'successful_syncs' => $integration->syncLogs()->successful()->count(),
            'failed_syncs' => $integration->syncLogs()->failed()->count(),
            'last_sync' => $integration->last_sync_at,
            'next_sync' => $integration->next_sync_at,
        ];

        return view('integrations.show', compact('integration', 'stats'));
    }

    /**
     * Create new integration
     */
    public function create()
    {
        return view('integrations.create');
    }

    /**
     * Store new integration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:integrations,slug',
            'type' => 'required|in:e-commerce,payment,logistics,crm,accounting',
            'config' => 'nullable|array',
            'sync_frequency' => 'required|in:realtime,hourly,daily,weekly',
        ]);

        $integration = Integration::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'type' => $validated['type'],
            'status' => 'inactive',
            'sync_frequency' => $validated['sync_frequency'],
            'config' => $validated['config'] ?? [],
        ]);

        return redirect()->route('integrations.setup', $integration)
            ->with('success', 'Integration created. Please complete the setup.');
    }

    /**
     * Show integration setup page
     */
    public function setup(Integration $integration)
    {
        $this->authorize('update', $integration);

        $connectorClass = $integration->getConnectorClass();
        $connector = new $connectorClass($integration);

        return view('integrations.setup', compact('integration', 'connector'));
    }

    /**
     * Update integration configuration
     */
    public function update(Request $request, Integration $integration)
    {
        $this->authorize('update', $integration);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sync_frequency' => 'sometimes|required|in:realtime,hourly,daily,weekly',
            'config' => 'nullable|array',
            'shop_domain' => 'nullable|string',
            'consumer_key' => 'nullable|string',
            'consumer_secret' => 'nullable|string',
        ]);

        // Update basic fields
        if (isset($validated['name'])) {
            $integration->update(['name' => $validated['name']]);
        }

        if (isset($validated['sync_frequency'])) {
            $integration->update(['sync_frequency' => $validated['sync_frequency']]);
        }

        // Update config values
        if (isset($validated['config'])) {
            foreach ($validated['config'] as $key => $value) {
                $integration->setConfigValue($key, $value, $this->isSensitiveKey($key));
            }
        }

        // Handle specific integration configs
        if (isset($validated['shop_domain'])) {
            $integration->setConfigValue('shop_domain', $validated['shop_domain']);
        }

        if (isset($validated['consumer_key'])) {
            $integration->setConfigValue('consumer_key', $validated['consumer_key'], true);
        }

        if (isset($validated['consumer_secret'])) {
            $integration->setConfigValue('consumer_secret', $validated['consumer_secret'], true);
        }

        return redirect()->route('integrations.show', $integration)
            ->with('success', 'Integration updated successfully.');
    }

    /**
     * Test integration connection
     */
    public function testConnection(Integration $integration)
    {
        $this->authorize('update', $integration);

        try {
            $connectorClass = $integration->getConnectorClass();
            $connector = new $connectorClass($integration);

            $result = $connector->testConnection();

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger manual sync
     */
    public function sync(Integration $integration, Request $request)
    {
        $this->authorize('update', $integration);

        $syncType = $request->input('type', 'products');

        if (!$integration->isConnected()) {
            return response()->json([
                'success' => false,
                'error' => 'Integration not connected',
            ], 400);
        }

        try {
            match ($syncType) {
                'products' => SyncProductsJob::dispatch($integration),
                'orders' => SyncOrdersJob::dispatch($integration),
                'inventory' => SyncInventoryJob::dispatch($integration),
                'all' => [
                    SyncProductsJob::dispatch($integration),
                    SyncOrdersJob::dispatch($integration),
                    SyncInventoryJob::dispatch($integration),
                ],
                default => throw new \InvalidArgumentException("Invalid sync type: {$syncType}"),
            };

            return response()->json([
                'success' => true,
                'message' => "Sync job queued: {$syncType}",
                'sync_type' => $syncType,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch sync job', [
                'integration' => $integration->slug,
                'sync_type' => $syncType,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync history
     */
    public function syncHistory(Integration $integration)
    {
        $this->authorize('view', $integration);

        $syncLogs = IntegrationSyncLog::where('integration_id', $integration->id)
            ->latest()
            ->paginate(50);

        return view('integrations.sync-history', compact('integration', 'syncLogs'));
    }

    /**
     * Get sync statistics
     */
    public function syncStats(Integration $integration)
    {
        $this->authorize('view', $integration);

        $stats = [
            'total_syncs' => $integration->syncLogs()->count(),
            'successful' => $integration->syncLogs()->successful()->count(),
            'failed' => $integration->syncLogs()->failed()->count(),
            'partial' => $integration->syncLogs()->partial()->count(),
            'success_rate' => $integration->syncLogs()->count() > 0
                ? round(($integration->syncLogs()->successful()->count() / $integration->syncLogs()->count()) * 100, 2)
                : 0,
            'today_syncs' => $integration->syncLogs()->today()->count(),
            'average_duration' => $integration->syncLogs()->whereNotNull('duration_seconds')
                ->avg('duration_seconds') ?? 0,
        ];

        return response()->json($stats);
    }

    /**
     * Activate integration
     */
    public function activate(Integration $integration)
    {
        $this->authorize('update', $integration);

        try {
            $connectorClass = $integration->getConnectorClass();
            $connector = new $connectorClass($integration);

            if ($connector->authenticate()) {
                $integration->markAsActive();

                return redirect()->route('integrations.show', $integration)
                    ->with('success', 'Integration activated successfully.');
            }

            return redirect()->back()
                ->withErrors(['error' => 'Authentication failed. Please check your credentials.']);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Deactivate integration
     */
    public function deactivate(Integration $integration)
    {
        $this->authorize('update', $integration);

        $integration->update(['status' => 'inactive']);

        return redirect()->route('integrations.show', $integration)
            ->with('success', 'Integration deactivated.');
    }

    /**
     * Delete integration
     */
    public function destroy(Integration $integration)
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        return redirect()->route('integrations.index')
            ->with('success', 'Integration deleted successfully.');
    }

    /**
     * Register webhooks for integration
     */
    public function registerWebhooks(Integration $integration)
    {
        $this->authorize('update', $integration);

        try {
            $connectorClass = $integration->getConnectorClass();
            $connector = new $connectorClass($integration);

            $result = $connector->registerWebhooks();

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if config key is sensitive (should be encrypted)
     */
    protected function isSensitiveKey(string $key): bool
    {
        $sensitiveKeys = [
            'api_key',
            'api_secret',
            'secret_key',
            'consumer_secret',
            'webhook_secret',
            'password',
            'token',
            'access_token',
        ];

        foreach ($sensitiveKeys as $sensitive) {
            if (stripos($key, $sensitive) !== false) {
                return true;
            }
        }

        return false;
    }
}
