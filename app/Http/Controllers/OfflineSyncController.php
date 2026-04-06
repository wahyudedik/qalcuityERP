<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OfflineSyncController extends Controller
{
    /**
     * Get sync status and statistics
     * GET /api/offline/status
     */
    public function getStatus(Request $request)
    {
        try {
            $user = $request->user();
            $tenantId = $user->tenant_id;

            // Get queue statistics (from database or cache)
            $stats = [
                'is_online' => true, // Always true from server perspective
                'pending_mutations' => 0,
                'failed_mutations' => 0,
                'last_sync_at' => null,
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::getStatus failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status',
            ], 500);
        }
    }

    /**
     * Bulk sync endpoint for offline mutations
     * POST /api/offline/sync
     */
    public function bulkSync(Request $request)
    {
        $request->validate([
            'mutations' => 'required|array|min:1|max:50',
            'mutations.*.url' => 'required|string',
            'mutations.*.method' => 'required|string|in:POST,PUT,PATCH,DELETE',
            'mutations.*.body' => 'nullable|array',
            'mutations.*.module' => 'required|string',
        ]);

        try {
            $user = $request->user();
            $tenantId = $user->tenant_id;
            $mutations = $request->input('mutations');

            $results = [];
            $synced = 0;
            $failed = 0;

            foreach ($mutations as $index => $mutation) {
                try {
                    // Route to appropriate handler based on module
                    $result = $this->processMutation($mutation, $user, $tenantId);

                    $results[] = [
                        'index' => $index,
                        'success' => true,
                        'data' => $result,
                    ];

                    $synced++;
                } catch (\Throwable $e) {
                    Log::warning('Offline mutation failed', [
                        'index' => $index,
                        'module' => $mutation['module'],
                        'error' => $e->getMessage(),
                    ]);

                    $results[] = [
                        'index' => $index,
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];

                    $failed++;
                }
            }

            Log::info('Bulk sync completed', [
                'total' => count($mutations),
                'synced' => $synced,
                'failed' => $failed,
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
            ]);

            return response()->json([
                'success' => true,
                'synced' => $synced,
                'failed' => $failed,
                'results' => $results,
            ]);

        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::bulkSync failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk sync failed',
                'error' => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Process single mutation
     */
    protected function processMutation(array $mutation, $user, int $tenantId): array
    {
        $module = $mutation['module'];
        $method = $mutation['method'];
        $body = $mutation['body'] ?? [];

        // Add tenant isolation
        $body['tenant_id'] = $tenantId;

        switch ($module) {
            case 'pos':
                return $this->handlePOSMutation($body, $user, $tenantId);

            case 'inventory':
                return $this->handleInventoryMutation($body, $user, $tenantId);

            case 'sales':
                return $this->handleSalesMutation($body, $user, $tenantId);

            default:
                throw new \Exception("Unsupported module: {$module}");
        }
    }

    /**
     * Handle POS mutations
     */
    protected function handlePOSMutation(array $body, $user, int $tenantId): array
    {
        // Check if this is a checkout operation
        if (isset($body['items'])) {
            // Call existing POS checkout logic
            $posController = app(\App\Http\Controllers\PosController::class);

            // Create request object
            $request = Request::create('/pos/checkout', 'POST', $body);
            $request->setUserResolver(fn() => $user);

            // Execute checkout
            $response = $posController->checkout($request);

            return json_decode($response->getContent(), true);
        }

        throw new \Exception('Invalid POS mutation');
    }

    /**
     * Handle inventory mutations
     */
    protected function handleInventoryMutation(array $body, $user, int $tenantId): array
    {
        // Implement inventory operations
        // Stock adjustments, transfers, etc.

        throw new \Exception('Inventory mutations not yet implemented');
    }

    /**
     * Handle sales mutations
     */
    protected function handleSalesMutation(array $body, $user, int $tenantId): array
    {
        // Implement sales operations
        // Invoice creation, payment recording, etc.

        throw new \Exception('Sales mutations not yet implemented');
    }

    /**
     * Clear failed mutations
     * DELETE /api/offline/failed
     */
    public function clearFailed(Request $request)
    {
        try {
            // In production, this would clean up database records
            // For now, just acknowledge

            return response()->json([
                'success' => true,
                'message' => 'Failed mutations cleared',
            ]);
        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::clearFailed failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear mutations',
            ], 500);
        }
    }

    /**
     * Get cached data for offline access
     * GET /api/offline/cache/{key}
     */
    public function getCache(Request $request, string $key)
    {
        try {
            $user = $request->user();
            $tenantId = $user->tenant_id;

            // Return cached data based on key
            $cacheKey = "offline_cache:{$tenantId}:{$key}";
            $data = cache()->get($cacheKey);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cache not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'cached_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::getCache failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache',
            ], 500);
        }
    }

    /**
     * Update cache for offline access
     * POST /api/offline/cache/{key}
     */
    public function updateCache(Request $request, string $key)
    {
        try {
            $user = $request->user();
            $tenantId = $user->tenant_id;

            $data = $request->input('data');
            $ttl = $request->input('ttl', 3600); // Default 1 hour

            $cacheKey = "offline_cache:{$tenantId}:{$key}";
            cache()->put($cacheKey, $data, $ttl);

            return response()->json([
                'success' => true,
                'message' => 'Cache updated',
            ]);
        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::updateCache failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cache',
            ], 500);
        }
    }
}
