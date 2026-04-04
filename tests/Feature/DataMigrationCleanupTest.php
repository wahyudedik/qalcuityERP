<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Services\DataArchivalService;
use App\Services\OrphanedDataCleanupService;
use App\Services\TenantDataMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataMigrationCleanupTest extends TestCase
{
    use RefreshDatabase;

    private $tenant1;
    private $tenant2;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant1 = $this->createTenant();
        $this->tenant2 = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant1);
    }

    /**
     * Test data archival service
     */
    public function test_archival_service_identifies_old_records(): void
    {
        // Create old activity logs
        $oldDate = now()->subDays(400); // Older than 365 day retention

        DB::table('activity_logs')->insert([
            [
                'tenant_id' => $this->tenant1->id,
                'user_id' => $this->user->id,
                'action' => 'test_action',
                'description' => 'Old test log',
                'created_at' => $oldDate,
                'updated_at' => $oldDate,
            ]
        ]);

        $archivalService = app(DataArchivalService::class);
        $stats = $archivalService->getStatistics($this->tenant1->id);

        $this->assertArrayHasKey('activity_logs', $stats);
        $this->assertGreaterThan(0, $stats['activity_logs']['ready_for_archival']);
    }

    /**
     * Test archival dry run mode
     */
    public function test_archival_dry_run_does_not_delete(): void
    {
        $oldDate = now()->subDays(400);

        DB::table('activity_logs')->insert([
            [
                'tenant_id' => $this->tenant1->id,
                'user_id' => $this->user->id,
                'action' => 'test_action',
                'description' => 'Old test log',
                'created_at' => $oldDate,
                'updated_at' => $oldDate,
            ]
        ]);

        $archivalService = app(DataArchivalService::class);
        $result = $archivalService->archiveType('activity_logs', $this->tenant1->id, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['archived_count'] ?? 0);
        $this->assertGreaterThan(0, $result['would_archive_count'] ?? 0);

        // Verify record still exists
        $this->assertDatabaseHas('activity_logs', [
            'tenant_id' => $this->tenant1->id,
            'description' => 'Old test log',
        ]);
    }

    /**
     * Test orphan detection service
     */
    public function test_orphan_detection_finds_broken_references(): void
    {
        // Create an invoice item pointing to non-existent invoice
        DB::table('invoice_items')->insert([
            'tenant_id' => $this->tenant1->id,
            'invoice_id' => 99999, // Non-existent
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cleanupService = app(OrphanedDataCleanupService::class);
        $scanResult = $cleanupService->scanType('invoice_items_without_invoice', $this->tenant1->id);

        $this->assertTrue($scanResult['success']);
        $this->assertEquals(1, $scanResult['orphan_count']);
    }

    /**
     * Test orphan cleanup in dry run mode
     */
    public function test_orphan_cleanup_dry_run_preserves_data(): void
    {
        // Create orphaned invoice item
        DB::table('invoice_items')->insert([
            'tenant_id' => $this->tenant1->id,
            'invoice_id' => 99999,
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cleanupService = app(OrphanedDataCleanupService::class);
        $result = $cleanupService->cleanupType('invoice_items_without_invoice', $this->tenant1->id, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['deleted_count'] ?? 0);
        $this->assertGreaterThan(0, $result['would_delete_count'] ?? 0);

        // Verify orphan still exists
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => 99999,
        ]);
    }

    /**
     * Test actual orphan cleanup (live mode)
     */
    public function test_orphan_cleanup_deletes_orphaned_records(): void
    {
        // Create orphaned invoice item
        DB::table('invoice_items')->insert([
            'tenant_id' => $this->tenant1->id,
            'invoice_id' => 99999,
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cleanupService = app(OrphanedDataCleanupService::class);
        $result = $cleanupService->cleanupType('invoice_items_without_invoice', $this->tenant1->id, false);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['deleted_count'] ?? 0);

        // Verify orphan is deleted
        $this->assertDatabaseMissing('invoice_items', [
            'invoice_id' => 99999,
        ]);
    }

    /**
     * Test tenant merge validation
     */
    public function test_tenant_merge_validates_different_tenants(): void
    {
        $migrationService = app(TenantDataMigrationService::class);

        $result = $migrationService->mergeTenants(
            sourceTenantId: $this->tenant1->id,
            targetTenantId: $this->tenant1->id, // Same tenant - should fail
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('different', $result['error'] ?? '');
    }

    /**
     * Test tenant data transfer
     */
    public function test_tenant_data_transfer_moves_records(): void
    {
        // Create customer in tenant1
        $customer = Customer::create([
            'tenant_id' => $this->tenant1->id,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '123456789',
        ]);

        $migrationService = app(TenantDataMigrationService::class);
        $result = $migrationService->transferData(
            sourceTenantId: $this->tenant1->id,
            targetTenantId: $this->tenant2->id,
            dataTypes: ['customers']
        );

        $this->assertArrayHasKey('customers', $result);
        $this->assertEquals(1, $result['customers']['transferred'] ?? 0);

        // Verify customer moved to tenant2
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'tenant_id' => $this->tenant2->id,
        ]);

        // Verify no longer in tenant1
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
            'tenant_id' => $this->tenant1->id,
        ]);
    }

    /**
     * Test detailed orphan report generation
     */
    public function test_orphan_report_generation(): void
    {
        // Create multiple orphans
        DB::table('invoice_items')->insert([
            'tenant_id' => $this->tenant1->id,
            'invoice_id' => 99999,
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cleanupService = app(OrphanedDataCleanupService::class);
        $report = $cleanupService->getDetailedReport($this->tenant1->id);

        $this->assertArrayHasKey('total_orphans', $report);
        $this->assertGreaterThan(0, $report['total_orphans']);
        $this->assertArrayHasKey('breakdown', $report);
        $this->assertArrayHasKey('generated_at', $report);
    }

    /**
     * Test that valid records are not archived
     */
    public function test_recent_records_are_not_archived(): void
    {
        // Create recent activity log (within retention period)
        $recentDate = now()->subDays(100); // Less than 365 days

        DB::table('activity_logs')->insert([
            [
                'tenant_id' => $this->tenant1->id,
                'user_id' => $this->user->id,
                'action' => 'test_action',
                'description' => 'Recent test log',
                'created_at' => $recentDate,
                'updated_at' => $recentDate,
            ]
        ]);

        $archivalService = app(DataArchivalService::class);
        $stats = $archivalService->getStatistics($this->tenant1->id);

        // Recent record should not be ready for archival
        $this->assertEquals(0, $stats['activity_logs']['ready_for_archival']);
    }

    /**
     * Test multi-tenant archival isolation
     */
    public function test_archival_respects_tenant_boundaries(): void
    {
        $oldDate = now()->subDays(400);

        // Create old logs in both tenants
        DB::table('activity_logs')->insert([
            [
                'tenant_id' => $this->tenant1->id,
                'user_id' => $this->user->id,
                'action' => 'test_action_1',
                'description' => 'Tenant 1 old log',
                'created_at' => $oldDate,
                'updated_at' => $oldDate,
            ],
            [
                'tenant_id' => $this->tenant2->id,
                'user_id' => $this->user->id,
                'action' => 'test_action_2',
                'description' => 'Tenant 2 old log',
                'created_at' => $oldDate,
                'updated_at' => $oldDate,
            ]
        ]);

        $archivalService = app(DataArchivalService::class);

        // Archive only tenant1
        $result = $archivalService->archiveType('activity_logs', $this->tenant1->id, true);

        $this->assertEquals(1, $result['would_archive_count'] ?? 0);

        // Tenant 2 records should remain untouched
        $stats2 = $archivalService->getStatistics($this->tenant2->id);
        $this->assertEquals(1, $stats2['activity_logs']['ready_for_archival']);
    }

    /**
     * Test orphan cleanup does not affect valid records
     */
    public function test_orphan_cleanup_preserves_valid_records(): void
    {
        // Create valid invoice
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant1->id,
            'customer_id' => Customer::create([
                'tenant_id' => $this->tenant1->id,
                'name' => 'Test Customer',
                'email' => 'test@example.com',
            ])->id,
            'number' => 'INV-TEST-001',
            'total_amount' => 1000,
            'status' => 'unpaid',
        ]);

        // Create valid invoice item
        DB::table('invoice_items')->insert([
            'tenant_id' => $this->tenant1->id,
            'invoice_id' => $invoice->id, // Valid reference
            'product_id' => 1,
            'quantity' => 1,
            'price' => 1000,
            'subtotal' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cleanupService = app(OrphanedDataCleanupService::class);
        $scanResult = $cleanupService->scanType('invoice_items_without_invoice', $this->tenant1->id);

        // Valid items should not be detected as orphans
        $this->assertEquals(0, $scanResult['orphan_count']);
    }
}
