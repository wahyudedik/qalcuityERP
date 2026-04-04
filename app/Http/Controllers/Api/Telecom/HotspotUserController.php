<?php

namespace App\Http\Controllers\Api\Telecom;

use App\Models\NetworkDevice;
use App\Models\HotspotUser;
use App\Services\Telecom\HotspotManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HotspotUserController extends TelecomApiController
{
    protected HotspotManagementService $hotspotService;

    public function __construct()
    {
        $this->hotspotService = new HotspotManagementService();
    }

    /**
     * Create a new hotspot user.
     * 
     * POST /api/telecom/hotspot/users
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|exists:network_devices,id',
                'username' => 'nullable|string|max:255',
                'password' => 'nullable|string|max:255',
                'download_speed_kbps' => 'nullable|integer|min:1',
                'upload_speed_kbps' => 'nullable|integer|min:1',
                'quota_bytes' => 'nullable|integer|min:0',
                'expires_at' => 'nullable|date|after:now',
                'comment' => 'nullable|string',
                'subscription_id' => 'nullable|exists:telecom_subscriptions,id',
            ]);

            // Check device ownership
            $device = NetworkDevice::findOrFail($validated['device_id']);

            if ($device->tenant_id !== auth()->user()->tenant_id) {
                return $this->error('Unauthorized', 403);
            }

            $result = $this->hotspotService->createUser($device, [
                'username' => $validated['username'] ?? null,
                'password' => $validated['password'] ?? null,
                'download_speed_kbps' => $validated['download_speed_kbps'] ?? null,
                'upload_speed_kbps' => $validated['upload_speed_kbps'] ?? null,
                'quota_bytes' => $validated['quota_bytes'] ?? 0,
                'expires_at' => $validated['expires_at'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'subscription_id' => $validated['subscription_id'] ?? null,
            ]);

            if (!$result['success']) {
                return $this->error($result['error'], 400);
            }

            $this->logApiRequest($request, 'POST /api/telecom/hotspot/users', [
                'username' => $result['user']->username
            ]);

            return $this->success([
                'user' => $result['user'],
            ], 'Hotspot user created successfully', 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error("Failed to create hotspot user", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error('Failed to create hotspot user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user statistics.
     * 
     * GET /api/telecom/hotspot/users/{id}/stats
     */
    public function stats(HotspotUser $user)
    {
        // Check tenant ownership
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            return $this->error('Unauthorized', 403);
        }

        try {
            $stats = $this->hotspotService->getUserStats($user);

            $this->logApiRequest(request(), "GET /api/telecom/hotspot/users/{$user->id}/stats");

            return $this->success($stats);

        } catch (\Exception $e) {
            Log::error("Failed to get user stats", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return $this->error('Failed to get user stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Suspend a user.
     * 
     * POST /api/telecom/hotspot/users/{id}/suspend
     */
    public function suspend(HotspotUser $user)
    {
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            return $this->error('Unauthorized', 403);
        }

        try {
            $success = $this->hotspotService->suspendUser($user);

            if (!$success) {
                return $this->error('Failed to suspend user', 500);
            }

            $this->logApiRequest(request(), "POST /api/telecom/hotspot/users/{$user->id}/suspend");

            return $this->success(['user' => $user->fresh()], 'User suspended successfully');

        } catch (\Exception $e) {
            Log::error("Failed to suspend user", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return $this->error('Failed to suspend user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reactivate a suspended user.
     * 
     * POST /api/telecom/hotspot/users/{id}/reactivate
     */
    public function reactivate(HotspotUser $user)
    {
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            return $this->error('Unauthorized', 403);
        }

        try {
            $success = $this->hotspotService->reactivateUser($user);

            if (!$success) {
                return $this->error('Failed to reactivate user', 500);
            }

            $this->logApiRequest(request(), "POST /api/telecom/hotspot/users/{$user->id}/reactivate");

            return $this->success(['user' => $user->fresh()], 'User reactivated successfully');

        } catch (\Exception $e) {
            Log::error("Failed to reactivate user", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return $this->error('Failed to reactivate user: ' . $e->getMessage(), 500);
        }
    }
}
