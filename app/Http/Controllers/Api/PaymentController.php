<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\SalesOrder;
use App\Models\TenantPaymentGateway;
use App\Services\PaymentGatewayService;
use App\Services\WebhookHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct()
    {
        // Will be initialized per request with tenant_id
    }

    /**
     * Generate QRIS payment for order
     */
    public function generateQris(Request $request, SalesOrder $order)
    {
        // Authorization check
        if ($order->tenant_id !== $this->tenantId()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'provider' => 'nullable|in:midtrans,xendit,duitku,tripay',
        ]);

        $service = new PaymentGatewayService($this->tenantId());
        $result = $service->generateQrisPayment($order, $validated['provider'] ?? null);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request)
    {
        $validated = $request->validate([
            'transaction_number' => 'required|string',
        ]);

        $service = new PaymentGatewayService($this->tenantId());
        $result = $service->checkPaymentStatus($validated['transaction_number']);

        return response()->json($result);
    }

    /**
     * Get payment transaction details
     */
    public function getTransaction(string $transactionNumber)
    {
        $transaction = PaymentTransaction::where('tenant_id', $this->tenantId())
            ->where('transaction_number', $transactionNumber)
            ->with('salesOrder')
            ->first();

        if (! $transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Get payment history
     */
    public function getHistory(Request $request)
    {
        $status = $request->input('status');
        $limit = $request->input('limit', 50);

        $query = PaymentTransaction::where('tenant_id', $this->tenantId())
            ->with('salesOrder')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $transactions = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Webhook handler for payment gateways
     */
    public function webhook(Request $request, string $provider)
    {
        try {
            $payload = $request->all();

            // Extract signature based on provider
            // Midtrans: signature_key in payload
            // Xendit: x-callback-token header
            // Duitku: signature in payload
            $signature = match ($provider) {
                'midtrans' => $payload['signature_key'] ?? $request->header('X-Signature'),
                'xendit' => $request->header('x-callback-token') ?? $request->header('X-Callback-Token'),
                'duitku' => $payload['signature'] ?? null,
                default => $request->header('X-Signature') ?? $request->header('Signature'),
            };

            // Extract tenant ID from payload or URL
            $tenantId = $request->input('tenant_id')
                ?? $request->route('tenant')
                ?? $this->extractTenantFromPayload($provider, $payload);

            if (! $tenantId) {
                return response()->json(['error' => 'Tenant ID not found'], 400);
            }

            // Use dedicated webhook handler service
            $webhookService = new WebhookHandlerService($tenantId);

            $result = match ($provider) {
                'midtrans' => $webhookService->handleMidtrans($payload, $signature),
                'xendit' => $webhookService->handleXendit($payload, $signature),
                'duitku' => $webhookService->handleDuitku($payload, $signature),
                default => ['success' => false, 'error' => "Unsupported provider: {$provider}"],
            };

            if ($result['success']) {
                return response()->json(['status' => 'success']);
            }

            return response()->json(['error' => $result['error']], 400);

        } catch (\Exception $e) {
            \Log::error("Payment webhook error: {$e->getMessage()}");

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get tenant payment gateway settings
     */
    public function getGatewaySettings()
    {
        $gateways = TenantPaymentGateway::where('tenant_id', $this->tenantId())->get();

        // Hide sensitive credentials
        $gateways->each(function ($gateway) {
            unset($gateway->credentials);
            unset($gateway->webhook_secret);
        });

        return response()->json([
            'success' => true,
            'data' => $gateways,
        ]);
    }

    /**
     * Save/update payment gateway settings
     */
    public function saveGatewaySettings(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:midtrans,xendit,duitku,tripay',
            'environment' => 'required|in:sandbox,production',
            'credentials' => 'required|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'webhook_secret' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $gateway = TenantPaymentGateway::updateOrCreate(
                [
                    'tenant_id' => $this->tenantId(),
                    'provider' => $validated['provider'],
                ],
                [
                    'environment' => $validated['environment'],
                    'is_active' => $validated['is_active'] ?? false,
                    'webhook_secret' => $validated['webhook_secret'] ?? null,
                ]
            );

            // Set encrypted credentials
            $gateway->setCredentials($validated['credentials']);
            $gateway->save();

            // Set as default if requested
            if ($validated['is_default'] ?? false) {
                $gateway->setAsDefault();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway settings saved',
        ]);
    }

    /**
     * Test/verify payment gateway credentials
     */
    public function testGateway(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:midtrans,xendit,duitku,tripay',
        ]);

        $service = new PaymentGatewayService($this->tenantId());
        $result = $service->verifyGateway($validated['provider']);

        return response()->json($result);
    }

    /**
     * Activate/deactivate payment gateway
     */
    public function toggleGateway(TenantPaymentGateway $gateway)
    {
        if ($gateway->tenant_id !== $this->tenantId()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $gateway->update([
            'is_active' => ! $gateway->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $gateway->is_active,
        ]);
    }

    /**
     * Delete payment gateway configuration
     */
    public function deleteGateway(TenantPaymentGateway $gateway)
    {
        if ($gateway->tenant_id !== $this->tenantId()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $gateway->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway removed',
        ]);
    }

    /**
     * Extract tenant ID from webhook payload (provider-specific)
     */
    private function extractTenantFromPayload(string $provider, array $payload): ?int
    {
        // Try to find tenant_id in metadata
        if (isset($payload['metadata']['tenant_id'])) {
            return (int) $payload['metadata']['tenant_id'];
        }

        // For Midtrans/Xendit, we might need to lookup by order_id
        if (isset($payload['order_id']) || isset($payload['external_id'])) {
            $orderId = $payload['order_id'] ?? $payload['external_id'];

            $transaction = PaymentTransaction::where('transaction_number', $orderId)->first();

            if ($transaction) {
                return $transaction->tenant_id;
            }
        }

        return null;
    }
}
