<?php

namespace App\Http\Controllers;

use App\Services\OfflineConflictResolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OfflineSyncController extends Controller
{
    protected $conflictResolutionService;

    public function __construct(OfflineConflictResolutionService $conflictResolutionService)
    {
        $this->conflictResolutionService = $conflictResolutionService;
    }

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
            Log::error('OfflineSyncController::getStatus failed: '.$e->getMessage());

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
            'mutations.*.offline_timestamp' => 'nullable|string',
            'mutations.*.local_id' => 'nullable|string',
        ]);

        try {
            $user = $request->user();
            $tenantId = $user->tenant_id;
            $mutations = $request->input('mutations');

            $results = [];
            $synced = 0;
            $failed = 0;
            $conflicts = 0;

            foreach ($mutations as $index => $mutation) {
                try {
                    // BUG-OFF-001 FIX: Check for conflicts before applying
                    $conflictCheck = $this->conflictResolutionService->checkAndResolveConflict($mutation);

                    if ($conflictCheck['has_conflict'] && ! $conflictCheck['apply']) {
                        // BUG-OFF-001 FIX: Conflict detected, don't apply
                        $results[] = [
                            'index' => $index,
                            'success' => false,
                            'conflict' => true,
                            'conflict_id' => $conflictCheck['conflict_id'] ?? null,
                            'strategy' => $conflictCheck['strategy'],
                            'error' => 'Conflict detected - manual resolution required',
                        ];

                        $conflicts++;

                        continue;
                    }

                    // Route to appropriate handler based on module
                    $result = $this->processMutation($mutation, $user, $tenantId);

                    $results[] = [
                        'index' => $index,
                        'success' => true,
                        'data' => $result,
                        'conflict_warning' => $conflictCheck['warning'] ?? null,
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

            Log::info('BUG-OFF-001: Bulk sync completed', [
                'total' => count($mutations),
                'synced' => $synced,
                'failed' => $failed,
                'conflicts' => $conflicts,
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
            ]);

            return response()->json([
                'success' => true,
                'synced' => $synced,
                'failed' => $failed,
                'conflicts' => $conflicts,
                'results' => $results,
            ]);

        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::bulkSync failed: '.$e->getMessage());

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
            $posController = app(PosController::class);

            // Create request object
            $request = Request::create('/pos/checkout', 'POST', $body);
            $request->setUserResolver(fn () => $user);

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
            Log::error('OfflineSyncController::clearFailed failed: '.$e->getMessage());

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

            if (! $data) {
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
            Log::error('OfflineSyncController::getCache failed: '.$e->getMessage());

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
            Log::error('OfflineSyncController::updateCache failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cache',
            ], 500);
        }
    }

    /**
     * Get pending conflicts
     * GET /api/offline/conflicts
     */
    public function getConflicts(Request $request)
    {
        try {
            // Support both Sanctum and web session auth
            $user = $request->user() ?: auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $conflicts = $this->conflictResolutionService->getPendingConflicts();
            $statistics = $this->conflictResolutionService->getStatistics();

            return response()->json([
                'success' => true,
                'conflicts' => $conflicts,
                'statistics' => $statistics,
            ]);
        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::getConflicts failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get conflicts',
            ], 500);
        }
    }

    /**
     * Resolve conflict
     * POST /api/offline/conflicts/{id}/resolve
     */
    public function resolveConflict(Request $request, int $id)
    {
        $request->validate([
            'strategy' => 'required|string|in:local_wins,server_wins,merge,skip',
        ]);

        try {
            $result = $this->conflictResolutionService->autoResolveConflict(
                $id,
                $request->input('strategy')
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conflict resolved',
                    'result' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);

        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::resolveConflict failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve conflict',
            ], 500);
        }
    }

    /**
     * Auto-resolve all pending conflicts
     * POST /api/offline/conflicts/auto-resolve
     */
    public function autoResolveAll(Request $request)
    {
        try {
            $result = $this->conflictResolutionService->bulkAutoResolve();

            return response()->json([
                'success' => true,
                'message' => 'Bulk resolution completed',
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('OfflineSyncController::autoResolveAll failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-resolve conflicts',
            ], 500);
        }
    }
}
