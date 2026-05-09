<?php

namespace App\Http\Controllers\Api\Telecom;

use App\Models\NetworkAlert;
use App\Models\NetworkDevice;
use App\Models\TelecomSubscription;
use App\Services\Telecom\UsageTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WebhookController extends TelecomApiController
{
    protected UsageTrackingService $usageService;

    public function __construct()
    {
        $this->usageService = new UsageTrackingService;
    }

    /**
     * Receive usage data from router.
     *
     * POST /api/telecom/webhook/router-usage
     */
    public function routerUsage(Request $request)
    {
        try {
            // Verify webhook signature if configured
            $signature = $request->header('X-Webhook-Signature');
            $secret = config('services.telecom.webhook_secret');

            if ($secret && $signature !== hash_hmac('sha256', $request->getContent(), $secret)) {
                return $this->error('Invalid webhook signature', 401);
            }

            $validated = $request->validate([
                'device_id' => 'required|integer',
                'subscription_id' => 'required|integer',
                'bytes_in' => 'required|integer|min:0',
                'bytes_out' => 'required|integer|min:0',
                'timestamp' => 'nullable|date',
                'metadata' => 'nullable|array',
            ]);

            // Find subscription
            $subscription = TelecomSubscription::findOrFail($validated['subscription_id']);

            // Record usage
            $metadata = $validated['metadata'] ?? [];
            $metadata['timestamp'] = $validated['timestamp'] ?? now();

            $usageRecord = $this->usageService->recordUsage(
                $subscription,
                $validated['bytes_in'],
                $validated['bytes_out'],
                $metadata
            );

            Log::info('Webhook usage received', [
                'subscription_id' => $subscription->id,
                'bytes_total' => $usageRecord->bytes_total,
                'device_id' => $validated['device_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usage data received',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Webhook usage processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Processing failed',
            ], 500);
        }
    }

    /**
     * Receive device alert from router.
     *
     * POST /api/telecom/webhook/device-alert
     */
    public function deviceAlert(Request $request)
    {
        try {
            // Verify webhook signature
            $signature = $request->header('X-Webhook-Signature');
            $secret = config('services.telecom.webhook_secret');

            if ($secret && $signature !== hash_hmac('sha256', $request->getContent(), $secret)) {
                return $this->error('Invalid webhook signature', 401);
            }

            $validated = $request->validate([
                'device_id' => 'required|integer',
                'alert_type' => 'required|string',
                'severity' => 'required|string|in:low,medium,high,critical',
                'title' => 'required|string',
                'message' => 'required|string',
                'timestamp' => 'nullable|date',
                'details' => 'nullable|array',
            ]);

            // Find device
            $device = NetworkDevice::findOrFail($validated['device_id']);

            // Create alert
            $alert = NetworkAlert::create([
                'tenant_id' => $device->tenant_id,
                'device_id' => $device->id,
                'alert_type' => $validated['alert_type'],
                'severity' => $validated['severity'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'triggered_at' => $validated['timestamp'] ?? now(),
                'additional_data' => $validated['details'] ?? [],
                'status' => 'new',
            ]);

            // Update device status if it's a connectivity alert
            if (in_array($validated['alert_type'], ['device_offline', 'connection_lost'])) {
                $device->update(['status' => 'offline']);
            } elseif ($validated['alert_type'] === 'device_online') {
                $device->update(['status' => 'online', 'last_seen_at' => now()]);
            }

            Log::info('Webhook alert received', [
                'device_id' => $device->id,
                'alert_type' => $validated['alert_type'],
                'severity' => $validated['severity'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert received',
                'alert_id' => $alert->id,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Webhook alert processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Processing failed',
            ], 500);
        }
    }
}
