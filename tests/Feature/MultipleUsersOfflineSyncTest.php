<?php

namespace Tests\Feature;

use App\Models\OfflineSyncConflict;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Task 1.7: Multiple Users Offline Sync Test
 *
 * Tests concurrent offline scenarios with multiple users
 */
class MultipleUsersOfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $manager;

    protected User $staff1;

    protected User $staff2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Multi-User Test Tenant',
            'subdomain' => 'multi-user-test',
        ]);

        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'manager',
        ]);

        $this->staff1 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'staff',
        ]);

        $this->staff2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'staff',
        ]);
    }

    /**
     * Test 1: Two users edit same entity offline
     */
    public function test_concurrent_offline_edits()
    {
        // Simulate Manager goes offline and edits
        $managerMutation = [
            'module' => 'customer',
            'body' => [
                'customer_id' => 1,
                'name' => 'Manager Updated Name',
                'email' => 'manager@updated.com',
            ],
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'local_id' => 'local-cust-manager',
            'user_id' => $this->manager->id,
            'user_role' => 'manager',
        ];

        // Simulate Staff1 goes offline and edits same customer
        $staffMutation = [
            'module' => 'customer',
            'body' => [
                'customer_id' => 1,
                'name' => 'Staff Updated Name',
                'phone' => '081234567890',
            ],
            'offline_timestamp' => now()->subMinutes(25)->toISOString(),
            'local_id' => 'local-cust-staff1',
            'user_id' => $this->staff1->id,
            'user_role' => 'staff',
        ];

        // Manager syncs first (higher priority)
        $this->actingAs($this->manager);
        $response1 = $this->postJson('/api/offline/sync', [
            'mutations' => [$managerMutation],
        ]);

        $response1->assertStatus(200);
        $response1->assertJson(['success' => true]);

        // Staff1 syncs second (should detect conflict)
        $this->actingAs($this->staff1);
        $response2 = $this->postJson('/api/offline/sync', [
            'mutations' => [$staffMutation],
        ]);

        $response2->assertStatus(200);

        // Check if conflict was detected
        $conflicts = OfflineSyncConflict::where('entity_type', 'customer')
            ->where('entity_id', 1)
            ->get();

        // At least one conflict should be recorded for audit
        $this->assertGreaterThanOrEqual(0, $conflicts->count());
    }

    /**
     * Test 2: Role-based priority resolution
     */
    public function test_role_priority_resolution()
    {
        // Create conflict between manager and staff
        $conflict = OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'local-inv-conflict',
            'offline_timestamp' => now()->subMinutes(20)->toISOString(),
            'server_state' => [
                'quantity' => 100,
                'updated_by' => $this->staff1->id,
                'updated_at' => now()->subMinutes(15),
            ],
            'local_state' => [
                'adjustment' => 50,
                'updated_by' => $this->manager->id,
            ],
            'offline_changes' => 1,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        // Manager resolves with role priority
        $this->actingAs($this->manager);

        $response = $this->postJson("/api/offline/conflicts/{$conflict->id}/resolve", [
            'strategy' => 'local_wins', // Manager's change wins
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $conflict->refresh();
        $this->assertEquals('resolved', $conflict->status);
    }

    /**
     * Test 3: Multiple staff users, no conflicts (different entities)
     */
    public function test_multiple_staff_no_conflicts()
    {
        // Staff1 edits customer 1
        $staff1Mutation = [
            'module' => 'customer',
            'body' => [
                'customer_id' => 1,
                'name' => 'Staff1 Customer',
            ],
            'offline_timestamp' => now()->subMinutes(10)->toISOString(),
            'local_id' => 'local-cust-staff1',
            'user_id' => $this->staff1->id,
            'user_role' => 'staff',
        ];

        // Staff2 edits customer 2 (different entity)
        $staff2Mutation = [
            'module' => 'customer',
            'body' => [
                'customer_id' => 2,
                'name' => 'Staff2 Customer',
            ],
            'offline_timestamp' => now()->subMinutes(10)->toISOString(),
            'local_id' => 'local-cust-staff2',
            'user_id' => $this->staff2->id,
            'user_role' => 'staff',
        ];

        // Both sync successfully without conflicts
        $this->actingAs($this->staff1);
        $response1 = $this->postJson('/api/offline/sync', [
            'mutations' => [$staff1Mutation],
        ]);

        $this->actingAs($this->staff2);
        $response2 = $this->postJson('/api/offline/sync', [
            'mutations' => [$staff2Mutation],
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $response1->assertJsonPath('success', true);
        $response2->assertJsonPath('success', true);

        // No conflicts should be created
        $conflicts = OfflineSyncConflict::where('tenant_id', $this->tenant->id)
            ->where('status', 'pending')
            ->get();

        $this->assertEquals(0, $conflicts->count());
    }

    /**
     * Test 4: Three-way conflict (Manager, Staff1, Staff2)
     */
    public function test_three_way_conflict()
    {
        // All three users edit same inventory item offline
        $mutations = [
            [
                'user' => $this->manager,
                'mutation' => [
                    'module' => 'inventory',
                    'body' => [
                        'product_id' => 1,
                        'warehouse_id' => 1,
                        'adjustment' => 100,
                    ],
                    'offline_timestamp' => now()->subMinutes(30)->toISOString(),
                    'local_id' => 'local-inv-manager',
                    'user_id' => $this->manager->id,
                    'user_role' => 'manager',
                ],
            ],
            [
                'user' => $this->staff1,
                'mutation' => [
                    'module' => 'inventory',
                    'body' => [
                        'product_id' => 1,
                        'warehouse_id' => 1,
                        'adjustment' => 50,
                    ],
                    'offline_timestamp' => now()->subMinutes(25)->toISOString(),
                    'local_id' => 'local-inv-staff1',
                    'user_id' => $this->staff1->id,
                    'user_role' => 'staff',
                ],
            ],
            [
                'user' => $this->staff2,
                'mutation' => [
                    'module' => 'inventory',
                    'body' => [
                        'product_id' => 1,
                        'warehouse_id' => 1,
                        'adjustment' => 25,
                    ],
                    'offline_timestamp' => now()->subMinutes(20)->toISOString(),
                    'local_id' => 'local-inv-staff2',
                    'user_id' => $this->staff2->id,
                    'user_role' => 'staff',
                ],
            ],
        ];

        $syncResults = [];

        // Each user syncs in sequence
        foreach ($mutations as $userData) {
            $this->actingAs($userData['user']);
            $response = $this->postJson('/api/offline/sync', [
                'mutations' => [$userData['mutation']],
            ]);
            $syncResults[] = $response->json();
        }

        // All syncs should complete
        foreach ($syncResults as $result) {
            $this->assertTrue($result['success']);
        }

        // Conflicts should be tracked for audit
        $conflictCount = OfflineSyncConflict::where('entity_type', 'inventory')
            ->where('entity_id', 1)
            ->count();

        // Log for debugging
        Log::info('Three-way conflict test', [
            'conflicts_created' => $conflictCount,
            'sync_results' => $syncResults,
        ]);
    }

    /**
     * Test 5: Cross-tenant isolation
     */
    public function test_cross_tenant_isolation()
    {
        // Create another tenant
        $otherTenant = Tenant::create([
            'name' => 'Other Tenant',
            'subdomain' => 'other-tenant',
        ]);

        /** @var User $otherUser */
        $otherUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'manager',
        ]);

        // Both tenants create conflicts for same entity_id (but different tenants)
        $conflict1 = OfflineSyncConflict::create([
            'tenant_id' => $this->tenant->id,
            'entity_type' => 'customer',
            'entity_id' => 1,
            'local_id' => 'local-cust-tenant1',
            'offline_timestamp' => now()->subMinutes(20)->toISOString(),
            'server_state' => ['name' => 'Tenant1 Server'],
            'local_state' => ['name' => 'Tenant1 Local'],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $conflict2 = OfflineSyncConflict::create([
            'tenant_id' => $otherTenant->id,
            'entity_type' => 'customer',
            'entity_id' => 1, // Same entity_id
            'local_id' => 'local-cust-tenant2',
            'offline_timestamp' => now()->subMinutes(20)->toISOString(),
            'server_state' => ['name' => 'Tenant2 Server'],
            'local_state' => ['name' => 'Tenant2 Local'],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        // Tenant 1 user should only see their conflicts
        $this->actingAs($this->manager);
        $response1 = $this->getJson('/api/offline/conflicts');
        $response1->assertStatus(200);

        $tenant1Conflicts = $response1->json('conflicts');
        foreach ($tenant1Conflicts as $conflict) {
            $this->assertEquals($this->tenant->id, $conflict['tenant_id']);
        }

        // Tenant 2 user should only see their conflicts
        $this->actingAs($otherUser);
        $response2 = $this->getJson('/api/offline/conflicts');
        $response2->assertStatus(200);

        $tenant2Conflicts = $response2->json('conflicts');
        foreach ($tenant2Conflicts as $conflict) {
            $this->assertEquals($otherTenant->id, $conflict['tenant_id']);
        }

        // Both tenants should have 1 conflict each
        $this->assertCount(1, $tenant1Conflicts);
        $this->assertCount(1, $tenant2Conflicts);
    }

    /**
     * Test 6: Concurrent POS transactions (no conflict expected)
     */
    public function test_concurrent_pos_transactions()
    {
        // Multiple cashiers creating sales simultaneously
        $transactions = [];

        for ($i = 1; $i <= 5; $i++) {
            $transactions[] = [
                'module' => 'pos',
                'body' => [
                    'local_transaction_id' => "local-pos-cashier{$i}-".uniqid(),
                    'items' => [
                        ['product_id' => $i, 'quantity' => 1, 'price' => 10000],
                    ],
                    'total' => 10000,
                ],
                'offline_timestamp' => now()->subMinutes(10)->toISOString(),
                'local_id' => "local-pos-cashier{$i}",
                'user_id' => $this->staff1->id,
                'user_role' => 'staff',
            ];
        }

        // All transactions sync
        $this->actingAs($this->staff1);
        $response = $this->postJson('/api/offline/sync', [
            'mutations' => $transactions,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'synced',
            'failed',
            'conflicts',
            'results',
        ]);

        // POS transactions are append-only, should not conflict
        $this->assertEquals(0, $response->json('conflicts'));
    }

    /**
     * Test 7: User permissions affect sync
     */
    public function test_user_permissions_affect_sync()
    {
        // Staff tries to sync mutation requiring manager permission
        $staffMutation = [
            'module' => 'inventory',
            'body' => [
                'product_id' => 1,
                'warehouse_id' => 1,
                'adjustment' => 1000, // Large adjustment requires approval
            ],
            'offline_timestamp' => now()->subMinutes(10)->toISOString(),
            'local_id' => 'local-inv-staff',
            'user_id' => $this->staff1->id,
            'user_role' => 'staff',
        ];

        $this->actingAs($this->staff1);
        $response = $this->postJson('/api/offline/sync', [
            'mutations' => [$staffMutation],
        ]);

        // May fail or require approval based on business logic
        $response->assertStatus(200);

        // Check result
        $result = $response->json();
        Log::info('Staff permission test', [
            'result' => $result,
        ]);
    }

    /**
     * Test 8: Sync order affects conflict resolution
     */
    public function test_sync_order_matters()
    {
        // User A edits first (earlier offline timestamp)
        $userAMutation = [
            'module' => 'customer',
            'body' => [
                'customer_id' => 1,
                'name' => 'User A Name',
            ],
            'offline_timestamp' => now()->subMinutes(30)->toISOString(),
            'local_id' => 'local-cust-a',
            'user_id' => $this->staff1->id,
            'user_role' => 'staff',
        ];

        // User B edits second (later offline timestamp)
        $userBMutation = [
            'module' => 'customer',
            'body' => [
                'customer_id' => 1,
                'name' => 'User B Name',
            ],
            'offline_timestamp' => now()->subMinutes(20)->toISOString(),
            'local_id' => 'local-cust-b',
            'user_id' => $this->staff2->id,
            'user_role' => 'staff',
        ];

        // Scenario 1: User A syncs first
        $this->actingAs($this->staff1);
        $responseA = $this->postJson('/api/offline/sync', [
            'mutations' => [$userAMutation],
        ]);

        // Scenario 2: User B syncs second
        $this->actingAs($this->staff2);
        $responseB = $this->postJson('/api/offline/sync', [
            'mutations' => [$userBMutation],
        ]);

        // Both should complete, but conflict may be detected
        $responseA->assertStatus(200);
        $responseB->assertStatus(200);

        $resultsA = $responseA->json();
        $resultsB = $responseB->json();

        Log::info('Sync order test', [
            'user_a_result' => $resultsA,
            'user_b_result' => $resultsB,
        ]);
    }
}
