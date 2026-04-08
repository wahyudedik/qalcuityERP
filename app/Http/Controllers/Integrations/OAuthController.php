<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\Integrations\ShopifyConnector;
use App\Services\Integrations\WooCommerceConnector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class OAuthController extends Controller
{
    /**
     * Start OAuth flow for integration
     */
    public function startOAuth(Request $request, string $provider)
    {
        $validated = $request->validate([
            'integration_id' => 'required|exists:integrations,id',
            'shop_domain' => 'required_if:provider,shopify|nullable|string',
            'store_url' => 'required_if:provider,woocommerce|nullable|string',
        ]);

        $integration = Integration::where('id', $validated['integration_id'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->firstOrFail();

        return match ($provider) {
            'shopify' => $this->startShopifyOAuth($integration, $validated['shop_domain']),
            'woocommerce' => $this->startWooCommerceOAuth($integration, $validated['store_url']),
            default => abort(404, "Provider {$provider} not found"),
        };
    }

    /**
     * Start Shopify OAuth flow
     */
    protected function startShopifyOAuth(Integration $integration, string $shopDomain)
    {
        // Update integration with shop domain
        $integration->setConfigValue('shop_domain', $shopDomain);
        $integration->update([
            'name' => "Shopify - {$shopDomain}",
        ]);

        // Create connector
        $connector = new ShopifyConnector($integration);

        // Get OAuth URL
        $authUrl = $connector->getAuthUrl(
            config('services.shopify.client_id'),
            route('integrations.oauth.callback', ['provider' => 'shopify']),
            'read_products,write_products,read_orders,write_orders,read_inventory,write_inventory'
        );

        return Redirect::away($authUrl);
    }

    /**
     * Start WooCommerce OAuth flow
     */
    protected function startWooCommerceOAuth(Integration $integration, string $storeUrl)
    {
        // Update integration with store URL
        $integration->setConfigValue('store_url', $storeUrl);
        $integration->update([
            'name' => "WooCommerce - {$storeUrl}",
        ]);

        // Redirect to WooCommerce setup page (manual API key entry)
        return redirect()->route('integrations.setup', $integration)
            ->with('info', 'Please enter your Consumer Key and Consumer Secret from WooCommerce.');
    }

    /**
     * Handle OAuth callback
     */
    public function handleCallback(Request $request, string $provider)
    {
        // Verify state
        if ($request->state !== session('oauth_state')) {
            Log::error('OAuth state mismatch', ['provider' => $provider]);
            return redirect()->route('integrations.index')
                ->withErrors(['error' => 'Invalid OAuth state. Please try again.']);
        }

        return match ($provider) {
            'shopify' => $this->handleShopifyCallback($request),
            default => abort(404, "Provider {$provider} not found"),
        };
    }

    /**
     * Handle Shopify OAuth callback
     */
    protected function handleShopifyCallback(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'shop' => 'required|string',
            'state' => 'required|string',
        ]);

        // Find integration by shop domain
        $integration = Integration::where('tenant_id', Auth::user()->tenant_id)
            ->where('slug', 'shopify')
            ->whereJsonContains('config', $validated['shop'])
            ->first();

        if (!$integration) {
            Log::error('Shopify integration not found', [
                'shop' => $validated['shop'],
            ]);

            return redirect()->route('integrations.index')
                ->withErrors(['error' => 'Integration not found. Please create it first.']);
        }

        // Create connector
        $connector = new ShopifyConnector($integration);

        // Complete OAuth
        $success = $connector->completeAuth(
            $validated['code'],
            config('services.shopify.client_id'),
            config('services.shopify.client_secret')
        );

        if ($success) {
            Log::info('Shopify OAuth completed successfully', [
                'integration_id' => $integration->id,
                'shop' => $validated['shop'],
            ]);

            // Register webhooks
            try {
                $connector->registerWebhooks();
            } catch (\Throwable $e) {
                Log::warning('Failed to register Shopify webhooks', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Dispatch initial sync
            \App\Jobs\Integrations\SyncProductsJob::dispatch($integration);

            return redirect()->route('integrations.show', $integration)
                ->with('success', 'Shopify connected successfully! Initial product sync started.');
        }

        Log::error('Shopify OAuth failed', [
            'shop' => $validated['shop'],
        ]);

        return redirect()->route('integrations.setup', $integration)
            ->withErrors(['error' => 'Failed to authenticate with Shopify. Please try again.']);
    }

    /**
     * Complete WooCommerce setup with API keys
     */
    public function completeWooCommerceSetup(Request $request, Integration $integration)
    {
        $validated = $request->validate([
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'webhook_secret' => 'nullable|string',
        ]);

        // Save credentials
        $integration->setConfigValue('consumer_key', $validated['consumer_key'], true);
        $integration->setConfigValue('consumer_secret', $validated['consumer_secret'], true);

        if (isset($validated['webhook_secret'])) {
            $integration->setConfigValue('webhook_secret', $validated['webhook_secret'], true);
        }

        // Test connection
        $connector = new WooCommerceConnector($integration);
        $result = $connector->testConnection();

        if ($result['success']) {
            $integration->markAsActive();

            // Register webhooks
            try {
                $connector->registerWebhooks();
            } catch (\Throwable $e) {
                Log::warning('Failed to register WooCommerce webhooks', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Dispatch initial sync
            \App\Jobs\Integrations\SyncProductsJob::dispatch($integration);

            return redirect()->route('integrations.show', $integration)
                ->with('success', 'WooCommerce connected successfully! Initial product sync started.');
        }

        return redirect()->back()
            ->withErrors(['error' => 'Connection test failed: ' . ($result['error'] ?? 'Unknown error')]);
    }

    /**
     * Disconnect integration
     */
    public function disconnect(Integration $integration)
    {
        $integration->update([
            'status' => 'inactive',
            'oauth_tokens' => null,
        ]);

        Log::info('Integration disconnected', [
            'integration_id' => $integration->id,
            'slug' => $integration->slug,
        ]);

        return redirect()->route('integrations.show', $integration)
            ->with('success', 'Integration disconnected.');
    }

    /**
     * Refresh OAuth token (if applicable)
     */
    public function refreshToken(Integration $integration)
    {
        try {
            $connectorClass = $integration->getConnectorClass();
            $connector = new $connectorClass($integration);

            // Shopify tokens don't expire, but other providers might
            $success = $connector->authenticate();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Token refreshed successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Token refresh failed',
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
