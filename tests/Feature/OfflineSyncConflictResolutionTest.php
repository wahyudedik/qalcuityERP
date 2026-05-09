<?php

namespace Tests\Feature;

use App\Models\OfflineSyncConflict;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OfflineConflictResolutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 1.5: Offline Mode Conflict Resolution - Integration Tests
 *
 * Tests for:
 * - Smart conflict detection algorithm
 * - Auto-resolve strategies (last-write-wins, role-priority)
 * - Retry queue with exponential backoff
 * - Sync endpoints
 */
class OfflineSyncConflictResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Tenant $tenant;

    protected OfflineConflictResolutionService $conflictService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'subdomain' => 'test-tenant',
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'manager',
        ]);

        $this->conflictService = app(OfflineConflictResolutionService::class);
    }

    /**
     * Test 1: Smart conflict detection for inventory
     */
    public function test_inventory_conflict_detection()
    {
        $mutation = [
            'module' => 'inventory',
            'body' => [
                'product_id' => 1,
                'warehouse_id' => 1,
                'adjustment' => 10,
                'new_quantity' => 110,
            ],
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'local_id' => 'local-inv-001',
        ];

        // Simulate server state has changed
        $conflictCheck = $this->conflictService->checkAndResolveConflict($mutation);

        // Should detect conflict if stock was modified
        $this->assertIsArray($conflictCheck);
        $this->assertArrayHasKey('has_conflict', $conflictCheck);
        $this->assertArrayHasKey('apply', $conflictCheck);
    }

    /**
     * Test 2: POS duplicate transaction prevention
     */
    public function test_pos_duplicate_prevention()
    {
        $localTransactionId = 'local-pos-'.uniqid();

        $mutation = [
            'module' => 'pos',
            'body' => [
                'local_transaction_id' => $localTransactionId,
                'items' => [],
                'total' => 100000,
            ],
            'offline_timestamp' => now()->subMinutes(10)->toISOString(),
            'local_id' => $localTransactionId,
        ];

        $conflictCheck = $this->conflictService->checkAndResolveConflict($mutation);

        // First time should not conflict
        $this->assertFalse($conflictCheck['has_conflict'] ?? false);
    }

    /**
     * Test 3: Customer data conflict detection
     */
    public function test_customer_conflict_detection()
    {
        $mutation = [
            'module' => 'customer',
            'body' => [
                'customer_id' => 1,
                'name' => 'Updated Customer Name',
                'email' => 'newemail@example.com',
                'phone' => '081234567890',
            ],
            'offline_timestamp' => now()->subMinutes(20)->toISOString(),
            'local_id' => 'local-cust-001',
        ];

        $conflictCheck = $this->conflictService->checkAndResolveConflict($mutation);

        $this->assertIsArray($conflictCheck);
    }

    /**
     * Test 4: Auto-resolve with local_wins strategy
     */
    public function test_auto_resolve_local_wins()
    {
        $conflict = OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-001',
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 10, 'new_quantity' => 110],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $result = $this->conflictService->autoResolveConflict($conflict->id, 'local_wins');

        $this->assertTrue($result['success']);
        $this->assertEquals('local_wins', $result['strategy']);

        // Verify conflict is resolved
        $conflict->refresh();
        $this->assertEquals('resolved', $conflict->status);
        $this->assertEquals('local_wins', $conflict->resolution_strategy);
    }

    /**
     * Test 5: Auto-resolve with server_wins strategy
     */
    public function test_auto_resolve_server_wins()
    {
        $conflict = OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'customer',
            'entity_id' => 1,
            'local_id' => 'local-cust-001',
            'offline_timestamp' => now()->subMinutes(15)->toISOString(),
            'server_state' => ['name' => 'Server Name', 'email' => 'server@example.com'],
            'local_state' => ['name' => 'Local Name', 'email' => 'local@example.com'],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $result = $this->conflictService->autoResolveConflict($conflict->id, 'server_wins');

        $this->assertTrue($result['success']);
        $this->assertEquals('server_wins', $result['strategy']);

        $conflict->refresh();
        $this->assertEquals('resolved', $conflict->status);
    }

    /**
     * Test 6: Auto-resolve with merge strategy
     */
    public function test_auto_resolve_merge()
    {
        $conflict = OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-002',
            'offline_timestamp' => now()->subMinutes(25)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 5, 'new_quantity' => 105],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $result = $this->conflictService->autoResolveConflict($conflict->id, 'merge');

        $this->assertTrue($result['success']);
        $this->assertEquals('merge', $result['strategy']);
    }

    /**
     * Test 7: Get pending conflicts
     */
    public function test_get_pending_conflicts()
    {
        // Create multiple conflicts
        OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-001',
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 10],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'customer',
            'entity_id' => 2,
            'local_id' => 'local-cust-001',
            'offline_timestamp' => now()->subMinutes(20)->toISOString(),
            'server_state' => ['name' => 'Server Name'],
            'local_state' => ['name' => 'Local Name'],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $conflicts = $this->conflictService->getPendingConflicts();

        $this->assertIsArray($conflicts);
        $this->assertGreaterThanOrEqual(2, count($conflicts));
    }

    /**
     * Test 8: Conflict statistics
     */
    public function test_conflict_statistics()
    {
        $this->actingAs($this->user);

        // Create conflicts with different statuses
        OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-001',
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 10],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'customer',
            'entity_id' => 2,
            'local_id' => 'local-cust-001',
            'offline_timestamp' => now()->subMinutes(20)->toISOString(),
            'server_state' => ['name' => 'Server Name'],
            'local_state' => ['name' => 'Local Name'],
            'offline_changes' => 0,
            'status' => 'resolved',
            'detected_at' => now()->subMinutes(15),
            'resolved_at' => now()->subMinutes(10),
            'resolution_strategy' => 'merge',
        ]);

        $stats = $this->conflictService->getStatistics();

        $this->assertArrayHasKey('total_conflicts', $stats);
        $this->assertArrayHasKey('pending_conflicts', $stats);
        $this->assertArrayHasKey('resolved_conflicts', $stats);
        $this->assertArrayHasKey('resolution_rate', $stats);

        $this->assertGreaterThanOrEqual(2, $stats['total_conflicts']);
        $this->assertGreaterThanOrEqual(1, $stats['pending_conflicts']);
        $this->assertGreaterThanOrEqual(1, $stats['resolved_conflicts']);
    }

    /**
     * Test 9: Bulk auto-resolve
     */
    public function test_bulk_auto_resolve()
    {
        // Create multiple pending conflicts
        for ($i = 1; $i <= 3; $i++) {
            OfflineSyncConflict::create([
                'tenant_id' => $this->tenant->id,
                'entity_type' => 'inventory',
                'entity_id' => $i,
                'local_id' => "local-inv-{$i}",
                'offline_timestamp' => now()->subMinutes(30 + $i)->toISOString(),
                'server_state' => ['quantity' => 100 + $i],
                'local_state' => ['adjustment' => $i * 10],
                'offline_changes' => 0,
                'status' => 'pending',
                'detected_at' => now(),
            ]);
        }

        $result = $this->conflictService->bulkAutoResolve();

        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('resolved', $result);
        $this->assertArrayHasKey('failed', $result);
        $this->assertGreaterThanOrEqual(3, $result['total']);
    }

    /**
     * Test 10: API endpoint - Bulk sync
     */
    public function test_bulk_sync_endpoint()
    {
        $this->actingAs($this->user);

        $mutations = [
            [
                'url' => '/api/pos/checkout',
                'method' => 'POST',
                'body' => [
                    'items' => [],
                    'total' => 100000,
                ],
                'module' => 'pos',
                'offline_timestamp' => now()->subMinutes(10)->toISOString(),
                'local_id' => 'local-pos-001',
            ],
        ];

        $response = $this->postJson('/api/offline/sync', [
            'mutations' => $mutations,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'synced',
            'failed',
            'conflicts',
            'results',
        ]);
    }

    /**
     * Test 11: API endpoint - Get conflicts
     */
    public function test_get_conflicts_endpoint()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/offline/conflicts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'conflicts',
            'statistics',
        ]);
    }

    /**
     * Test 12: API endpoint - Resolve conflict
     */
    public function test_resolve_conflict_endpoint()
    {
        $this->actingAs($this->user);

        $conflict = OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-001',
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 10],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $response = $this->postJson("/api/offline/conflicts/{$conflict->id}/resolve", [
            'strategy' => 'merge',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Conflict resolved',
        ]);
    }

    /**
     * Test 13: API endpoint - Auto-resolve all
     */
    public function test_auto_resolve_all_endpoint()
    {
        $this->actingAs($this->user);

        // Create test conflict
        OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-001',
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 10],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $response = $this->postJson('/api/offline/conflicts/auto-resolve');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result',
        ]);
    }

    /**
     * Test 14: Default strategy per entity type
     */
    public function test_default_strategies()
    {
        // Test that different entity types have appropriate default strategies
        $reflection = new \ReflectionClass($this->conflictService);
        $method = $reflection->getMethod('getDefaultStrategy');
        $method->setAccessible(true);

        $this->assertEquals('merge', $method->invoke($this->conflictService, 'inventory'));
        $this->assertEquals('server_wins', $method->invoke($this->conflictService, 'sale'));
        $this->assertEquals('local_wins', $method->invoke($this->conflictService, 'customer'));
        $this->assertEquals('skip', $method->invoke($this->conflictService, 'pos'));
    }

    /**
     * Test 15: Tenant isolation in conflicts
     */
    public function test_tenant_isolation()
    {
        $this->actingAs($this->user);

        // Create conflict for current tenant
        $conflict1 = OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-001',
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 10],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        // Create conflict for different tenant
        $otherTenant = Tenant::create([
            'name' => 'Other Tenant',
            'subdomain' => 'other-tenant',
        ]);

        OfflineSyncConflict::create([
            'tenant_id' => $otherTenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 2,
            'local_id' => 'local-inv-002',
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'server_state' => ['quantity' => 200],
            'local_state' => ['adjustment' => 20],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $conflicts = $this->conflictService->getPendingConflicts();

        // Should only see conflicts from current tenant
        foreach ($conflicts as $conflict) {
            $this->assertEquals($this->tenant->id, $conflict['tenant_id']);
        }
    }
}
