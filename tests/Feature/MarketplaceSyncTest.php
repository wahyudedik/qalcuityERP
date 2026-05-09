<?php

namespace Tests\Feature;

use App\Jobs\SyncEcommerceOrders;
use App\Jobs\SyncMarketplacePrices;
use App\Jobs\SyncMarketplaceStock;
use App\Models\MarketplaceIntegration;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Test Marketplace Integration and Sync Functionality
 *
 * @group marketplace
 * @group feature
 */
class MarketplaceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Tenant $tenant;

    protected MarketplaceIntegration $marketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->marketplace = MarketplaceIntegration::factory()->create([
            'tenant_id' => $this->tenant->id,
            'platform' => 'shopee',
            'is_active' => true,
        ]);
    }

    /**
     * Test product SKU mapping creation
     */
    public function test_create_sku_mapping(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/marketplace/sku-mapping', [
                'product_id' => $product->id,
                'marketplace_id' => $this->marketplace->id,
                'external_sku' => 'SHOPEE-EXT-001',
                'external_product_id' => '12345',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'product_id',
                    'marketplace_id',
                    'external_sku',
                ],
            ]);

        $this->assertDatabaseHas('marketplace_sku_mappings', [
            'product_id' => $product->id,
            'external_sku' => 'SHOPEE-EXT-001',
        ]);
    }

    /**
     * Test stock sync from ERP to marketplace
     */
    public function test_stock_sync_to_marketplace(): void
    {
        Queue::fake();
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'ERP-SKU-001',
        ]);

        // Create warehouse stock
        $product->warehouses()->attach(1, ['quantity' => 100, 'available' => 80]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/marketplace/sync/stock', [
                'product_id' => $product->id,
                'marketplace_ids' => [$this->marketplace->id],
            ]);

        $response->assertStatus(200);

        // Verify sync job was queued
        Queue::assertPushed(SyncMarketplaceStock::class);
    }

    /**
     * Test bulk stock sync for multiple products
     */
    public function test_bulk_stock_sync(): void
    {
        Queue::fake();

        $products = Product::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/marketplace/sync/stock/bulk', [
                'product_ids' => $products->pluck('id')->toArray(),
                'marketplace_ids' => [$this->marketplace->id],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'queued_count',
                    'job_batch_id',
                ],
            ]);
    }

    /**
     * Test price sync to marketplace
     */
    public function test_price_sync_to_marketplace(): void
    {
        Queue::fake();
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price' => 150000,
            'compare_at_price' => 200000,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/marketplace/sync/price', [
                'product_id' => $product->id,
                'marketplace_ids' => [$this->marketplace->id],
            ]);

        $response->assertStatus(200);
        Queue::assertPushed(SyncMarketplacePrices::class);
    }

    /**
     * Test marketplace webhook handling for order creation
     */
    public function test_handle_marketplace_webhook_order(): void
    {
        Http::fake();

        $payload = [
            'event' => 'order.created',
            'data' => [
                'order_id' => 'MP-ORDER-123',
                'items' => [
                    [
                        'sku' => 'SHOPEE-EXT-001',
                        'quantity' => 2,
                        'price' => 100000,
                    ],
                ],
                'buyer' => [
                    'name' => 'Test Buyer',
                    'email' => 'buyer@example.com',
                ],
            ],
        ];

        $response = $this->postJson('/api/marketplace/webhooks/shopee', $payload);

        $response->assertStatus(200);

        // Verify order import job was queued
        Queue::assertPushed(SyncEcommerceOrders::class);
    }

    /**
     * Test sync status tracking
     */
    public function test_sync_status_tracking(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        // Simulate a sync
        MarketplaceSyncLog::create([
            'tenant_id' => $this->tenant->id,
            'marketplace_id' => $this->marketplace->id,
            'product_id' => $product->id,
            'sync_type' => 'stock',
            'status' => 'success',
            'request_data' => json_encode(['quantity' => 100]),
            'response_data' => json_encode(['success' => true]),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/marketplace/sync-status?product_id={$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'last_sync_at',
                    'status',
                    'sync_type',
                ],
            ]);
    }

    /**
     * Test failed sync retry mechanism
     */
    public function test_failed_sync_retry(): void
    {
        Queue::fake();

        // Simulate failed sync
        $failedSync = MarketplaceSyncLog::create([
            'tenant_id' => $this->tenant->id,
            'marketplace_id' => $this->marketplace->id,
            'product_id' => 1,
            'sync_type' => 'stock',
            'status' => 'failed',
            'error_message' => 'API timeout',
            'retry_count' => 0,
        ]);

        // Trigger retry
        $response = $this->actingAs($this->user)
            ->postJson('/api/marketplace/sync/retry', [
                'sync_log_id' => $failedSync->id,
            ]);

        $response->assertStatus(200);

        // Verify retry was queued
        $this->assertDatabaseHas('marketplace_sync_logs', [
            'id' => $failedSync->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test tenant isolation in marketplace data
     */
    public function test_tenant_isolation_in_marketplace_data(): void
    {
        $tenant2 = Tenant::factory()->create();
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        $marketplace2 = MarketplaceIntegration::factory()->create([
            'tenant_id' => $tenant2->id,
        ]);

        $product1 = Product::factory()->create(['tenant_id' => $this->tenant->id]);
        $product2 = Product::factory()->create(['tenant_id' => $tenant2->id]);

        // User from tenant 1 cannot access tenant 2's marketplace
        $response = $this->actingAs($user2)
            ->getJson("/api/marketplace/integrations/{$this->marketplace->id}");

        $response->assertStatus(403);

        // User from tenant 2 can only see their own products
        $response = $this->actingAs($user2)
            ->getJson('/api/marketplace/products');

        $response->assertStatus(200);
        // Should only contain tenant 2's products
    }
}
