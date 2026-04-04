<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentCallback;
use App\Services\WebhookHandlerService;
use Illuminate\Http\Request;

class WebhookTestController extends Controller
{
    /**
     * Test Midtrans webhook with sample payload
     */
    public function testMidtrans(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Sample Midtrans settlement payload
        $payload = [
            'transaction_time' => now()->toIso8601String(),
            'transaction_status' => 'settlement',
            'transaction_id' => 'test-' . uniqid(),
            'status_message' => 'Success, transaction is found',
            'status_code' => '200',
            'signature_key' => hash('sha512', 'test'),
            'payment_type' => 'gopay',
            'order_id' => $request->input('order_id', 'PAY-TEST-' . date('YmdHis')),
            'merchant_id' => 'G123456789',
            'gross_amount' => $request->input('amount', 150000),
            'fraud_status' => 'accept',
            'currency' => 'IDR',
        ];

        // Generate signature for testing
        $secret = $request->input('webhook_secret', 'test-secret');
        $hashInput = $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $secret;
        $signature = hash('sha512', $hashInput);

        $service = new WebhookHandlerService($tenantId);
        $result = $service->handleMidtrans($payload, $signature);

        return response()->json([
            'success' => true,
            'message' => 'Test webhook sent',
            'payload' => $payload,
            'result' => $result,
        ]);
    }

    /**
     * Test Xendit webhook with sample payload
     */
    public function testXendit(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Sample Xendit paid payload
        $payload = [
            'id' => 'test-' . uniqid(),
            'external_id' => $request->input('order_id', 'PAY-TEST-' . date('YmdHis')),
            'user_id' => 'test-user-id',
            'is_high' => false,
            'payment_method' => 'QRIS',
            'status' => 'PAID',
            'paid_amount' => $request->input('amount', 150000),
            'paid_at' => now()->toIso8601String(),
            'created' => now()->subMinutes(5)->toIso8601String(),
            'updated' => now()->toIso8601String(),
            'currency' => 'IDR',
        ];

        // Generate HMAC signature for testing
        $secret = $request->input('webhook_secret', 'test-secret');
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $service = new WebhookHandlerService($tenantId);
        $result = $service->handleXendit($payload, $signature);

        return response()->json([
            'success' => true,
            'message' => 'Test webhook sent',
            'payload' => $payload,
            'result' => $result,
        ]);
    }

    /**
     * Get webhook callback history
     */
    public function getWebhookHistory(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $limit = $request->input('limit', 50);
        $provider = $request->input('provider');
        $processed = $request->input('processed');

        $query = PaymentCallback::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc');

        if ($provider) {
            $query->where('provider', $provider);
        }

        if ($processed !== null) {
            $query->where('processed', filter_var($processed, FILTER_VALIDATE_BOOLEAN));
        }

        $callbacks = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $callbacks,
        ]);
    }

    /**
     * Retry failed webhook callbacks
     */
    public function retryFailedWebhooks(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $limit = $request->input('limit', 10);

        $service = new WebhookHandlerService($tenantId);
        $result = $service->retryFailedCallbacks($limit);

        return response()->json($result);
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats()
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_callbacks' => PaymentCallback::where('tenant_id', $tenantId)->count(),
            'processed' => PaymentCallback::where('tenant_id', $tenantId)->where('processed', true)->count(),
            'pending' => PaymentCallback::where('tenant_id', $tenantId)->where('processed', false)->count(),
            'failed' => PaymentCallback::where('tenant_id', $tenantId)
                ->where('processed', false)
                ->whereNotNull('error_message')
                ->count(),
            'by_provider' => PaymentCallback::where('tenant_id', $tenantId)
                ->selectRaw('provider, COUNT(*) as count, SUM(CASE WHEN processed = 1 THEN 1 ELSE 0 END) as processed_count')
                ->groupBy('provider')
                ->get(),
            'recent_failures' => PaymentCallback::where('tenant_id', $tenantId)
                ->where('processed', false)
                ->whereNotNull('error_message')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id', 'provider', 'error_message', 'created_at']),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
