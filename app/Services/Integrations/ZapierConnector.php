<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Zapier/Make.com Universal Webhook Connector
 *
 * Sends data to Zapier/Make.com webhooks
 * Enables integration with 5000+ apps via Zapier
 */
class ZapierConnector extends BaseConnector
{
    protected WebhookDeliveryService $webhookService;

    public function __construct(Integration $integration)
    {
        parent::__construct($integration);
        $this->webhookService = app(WebhookDeliveryService::class);
    }

    public function authenticate(): bool
    {
        try {
            $webhookUrl = $this->integration->getConfigValue('webhook_url');

            if (! $webhookUrl) {
                return false;
            }

            // Test webhook
            $response = Http::post($webhookUrl, [
                'event' => 'test.connection',
                'message' => 'Testing Zapier integration',
                'timestamp' => now()->toISOString(),
            ]);

            if ($response->successful()) {
                $this->integration->markAsActive();

                return true;
            }

            return false;
        } catch (Throwable $e) {
            Log::error('Zapier authentication failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Send event to Zapier webhook
     */
    public function sendEvent(string $event, array $data): array
    {
        try {
            $webhookUrl = $this->integration->getConfigValue('webhook_url');

            if (! $webhookUrl) {
                return ['success' => false, 'error' => 'Webhook URL not configured'];
            }

            $payload = [
                'event' => $event,
                'tenant_id' => $this->integration->tenant_id,
                'timestamp' => now()->toISOString(),
                'data' => $data,
            ];

            // Use webhook delivery service for retry logic
            $subscription = $this->integration->webhooks()->firstOrCreate(
                ['endpoint_url' => $webhookUrl],
                [
                    'secret_key' => $this->integration->getConfigValue('secret_key') ?? bin2hex(random_bytes(32)),
                    'events' => [$event],
                    'is_active' => true,
                ]
            );

            $delivery = $this->webhookService->deliver($subscription, $event, $payload);

            return [
                'success' => $delivery->status === 'delivered',
                'delivery_id' => $delivery->id,
                'status' => $delivery->status,
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'sendEvent');
        }
    }

    /**
     * Send invoice created event
     */
    public function sendInvoiceCreated($invoice): array
    {
        return $this->sendEvent('invoice.created', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer_id' => $invoice->customer_id,
            'total_amount' => $invoice->total_amount,
            'due_date' => $invoice->due_date?->toISOString(),
            'status' => $invoice->status,
        ]);
    }

    /**
     * Send order created event
     */
    public function sendOrderCreated($order): array
    {
        return $this->sendEvent('order.created', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $order->customer_id,
            'total_amount' => $order->total_amount,
            'status' => $order->status,
        ]);
    }

    /**
     * Send payment received event
     */
    public function sendPaymentReceived($payment): array
    {
        return $this->sendEvent('payment.received', [
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id ?? null,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method ?? null,
            'transaction_id' => $payment->transaction_id ?? null,
        ]);
    }

    /**
     * Send low stock alert
     */
    public function sendLowStockAlert($product, int $currentStock): array
    {
        return $this->sendEvent('inventory.low_stock', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'current_stock' => $currentStock,
            'reorder_level' => $product->reorder_level ?? 0,
        ]);
    }

    /**
     * Send customer created event
     */
    public function sendCustomerCreated($customer): array
    {
        return $this->sendEvent('customer.created', [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'type' => $customer->type ?? 'individual',
        ]);
    }

    // E-commerce methods (not applicable)
    public function syncProducts(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }

    public function syncOrders(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }

    public function syncInventory(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }

    public function registerWebhooks(): array
    {
        return ['success' => true, 'registered' => []];
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('Zapier webhook received', ['event' => $payload['event'] ?? 'unknown']);
    }
}
