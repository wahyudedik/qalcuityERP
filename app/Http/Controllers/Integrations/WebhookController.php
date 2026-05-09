<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\Integrations\ShopifyConnector;
use App\Services\Integrations\WooCommerceConnector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Shopify webhooks
     */
    public function handleShopify(Request $request)
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $shop = $request->header('X-Shopify-Shop-Domain');
        $topic = $request->header('X-Shopify-Topic');
        $payload = $request->getContent();

        // Verify HMAC signature
        if (! $this->verifyShopifySignature($payload, $hmac, $shop)) {
            Log::error('Shopify webhook signature verification failed', [
                'shop' => $shop,
                'topic' => $topic,
            ]);

            return response('Invalid signature', 401);
        }

        Log::info('Shopify webhook received', [
            'shop' => $shop,
            'topic' => $topic,
        ]);

        // Find integration
        $integration = Integration::where('slug', 'shopify')
            ->whereJsonContains('config', $shop)
            ->first();

        if (! $integration) {
            Log::error('Shopify integration not found for webhook', [
                'shop' => $shop,
            ]);

            return response('Integration not found', 404);
        }

        // Process webhook
        try {
            $connector = new ShopifyConnector($integration);
            $connector->handleWebhook($request->all());

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('Shopify webhook processing failed', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);

            return response('Processing failed', 500);
        }
    }

    /**
     * Handle WooCommerce webhooks
     */
    public function handleWooCommerce(Request $request)
    {
        $signature = $request->header('X-Wc-Webhook-Signature');
        $event = $request->header('X-Wc-Webhook-Event');
        $action = $request->header('X-Wc-Webhook-Action');
        $payload = $request->getContent();

        Log::info('WooCommerce webhook received', [
            'event' => $event,
            'action' => $action,
        ]);

        // Find WooCommerce integration
        $integration = Integration::where('slug', 'woocommerce')
            ->where('status', 'active')
            ->first();

        if (! $integration) {
            Log::error('WooCommerce integration not found for webhook');

            return response('Integration not found', 404);
        }

        // Verify signature
        $webhookSecret = $integration->getConfigValue('webhook_secret');

        if ($webhookSecret && ! $this->verifySignature($payload, $signature, $webhookSecret)) {
            Log::error('WooCommerce webhook signature verification failed');

            return response('Invalid signature', 401);
        }

        // Process webhook
        try {
            $connector = new WooCommerceConnector($integration);
            $connector->handleWebhook($request->all());

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('WooCommerce webhook processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response('Processing failed', 500);
        }
    }

    /**
     * Verify Shopify HMAC signature
     */
    protected function verifyShopifySignature(string $payload, ?string $hmac, ?string $shop): bool
    {
        if (! $hmac || ! $shop) {
            return false;
        }

        // Find integration to get webhook secret
        $integration = Integration::where('slug', 'shopify')
            ->whereJsonContains('config', $shop)
            ->first();

        if (! $integration) {
            return false;
        }

        $secret = $integration->getConfigValue('webhook_secret');

        if (! $secret) {
            return false;
        }

        $computedHmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($computedHmac, $hmac);
    }

    /**
     * Verify HMAC signature
     */
    protected function verifySignature(string $payload, ?string $signature, string $secret): bool
    {
        if (! $signature) {
            return false;
        }

        $computedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Test webhook endpoint
     */
    public function test(Request $request)
    {
        Log::info('Webhook test endpoint hit', [
            'payload' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is working',
            'received_at' => now()->toISOString(),
        ]);
    }
}
