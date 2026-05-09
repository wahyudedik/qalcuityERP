<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMarketplaceWebhook;
use App\Models\EcommerceChannel;
use App\Models\EcommerceWebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketplaceWebhookController extends Controller
{
    /**
     * Handle Shopee webhook
     * Shopee signs with HMAC-SHA256 using partner key
     */
    public function handleShopee(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('Authorization') ?? $request->header('X-Shopee-Hmac-Sha256', '');
        $shopId = $payload['shop_id'] ?? null;

        // Find channel by shop_id
        $channel = $shopId ? EcommerceChannel::where('platform', 'shopee')
            ->where('shop_id', $shopId)
            ->where('webhook_enabled', true)
            ->first() : null;

        // Verify signature
        $isValid = false;
        if ($channel && $channel->webhook_secret) {
            $rawBody = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $rawBody, $channel->webhook_secret);
            $isValid = hash_equals($expectedSignature, $signature);
        }

        $log = EcommerceWebhookLog::create([
            'tenant_id' => $channel?->tenant_id,
            'channel_id' => $channel?->id,
            'platform' => 'shopee',
            'event_type' => $payload['event'] ?? $payload['type'] ?? 'unknown',
            'payload' => $payload,
            'signature' => $signature,
            'is_valid' => $isValid,
        ]);

        if ($isValid && $channel) {
            ProcessMarketplaceWebhook::dispatch($log->id);
        } else {
            Log::warning('Invalid Shopee webhook received', ['shop_id' => $shopId, 'valid' => $isValid]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle Tokopedia webhook
     */
    public function handleTokopedia(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Toko-Hmac-Sha256', '');
        $shopId = (string) ($payload['shop_id'] ?? $payload['fs_id'] ?? null);

        $channel = $shopId ? EcommerceChannel::where('platform', 'tokopedia')
            ->where('shop_id', $shopId)
            ->where('webhook_enabled', true)
            ->first() : null;

        $isValid = false;
        if ($channel && $channel->webhook_secret) {
            $rawBody = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $rawBody, $channel->webhook_secret);
            $isValid = hash_equals($expectedSignature, $signature);
        }

        $log = EcommerceWebhookLog::create([
            'tenant_id' => $channel?->tenant_id,
            'channel_id' => $channel?->id,
            'platform' => 'tokopedia',
            'event_type' => $payload['type'] ?? $payload['action'] ?? 'unknown',
            'payload' => $payload,
            'signature' => $signature,
            'is_valid' => $isValid,
        ]);

        if ($isValid && $channel) {
            ProcessMarketplaceWebhook::dispatch($log->id);
        } else {
            Log::warning('Invalid Tokopedia webhook received', ['shop_id' => $shopId]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle Lazada webhook
     */
    public function handleLazada(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('Authorization', '');
        $sellerId = (string) ($payload['seller_id'] ?? null);

        $channel = $sellerId ? EcommerceChannel::where('platform', 'lazada')
            ->where('shop_id', $sellerId)
            ->where('webhook_enabled', true)
            ->first() : null;

        // Lazada uses app_secret for signature
        $isValid = false;
        if ($channel && $channel->webhook_secret) {
            $rawBody = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $rawBody, $channel->webhook_secret);
            $isValid = hash_equals($expectedSignature, $signature);
        }

        $log = EcommerceWebhookLog::create([
            'tenant_id' => $channel?->tenant_id,
            'channel_id' => $channel?->id,
            'platform' => 'lazada',
            'event_type' => $payload['message_type'] ?? $payload['type'] ?? 'unknown',
            'payload' => $payload,
            'signature' => $signature,
            'is_valid' => $isValid,
        ]);

        if ($isValid && $channel) {
            ProcessMarketplaceWebhook::dispatch($log->id);
        } else {
            Log::warning('Invalid Lazada webhook received', ['seller_id' => $sellerId]);
        }

        return response()->json(['status' => 'ok']);
    }
}
