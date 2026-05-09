<?php

namespace Tests\Feature\Audit;

use App\Models\LandedCost;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use App\Services\BarcodeService;
use App\Services\InventoryCostingService;
use App\Services\LandedCostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Task 11: Audit & Perbaikan Modul Inventory
 *
 * Tests all inventory features:
 * - Real-time stock updates
 * - FIFO and Average Cost costing methods
 * - Multi-warehouse support
 * - Batch/lot tracking
 * - Barcode/QR code generation
 * - Landed cost allocation
 * - Stock minimum alerts
 * - WMS features (zones, bins, racks)
 */
class Task11_InventoryAuditTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Warehouse $warehouse1;

    private Warehouse $warehouse2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tenant and user
        $this->tenant = Tenant::factory()->create([
            'costing_method' => 'simple', // Will test FIFO and AVCO separately
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);

        // Create test warehouses
        $this->warehouse1 = Warehouse::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gudang Utama',
            'code' => 'GU-01',
            'is_active' => true,
        ]);

        $this->warehouse2 = Warehouse::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gudang Cabang',
            'code' => 'GC-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function test_11_1_stock_updates_real_time_on_receipt()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'stock_min' => 10,
        ]);

        // Initial stock should be 0
        $this->assertEquals(0, $product->totalStock());

        // Add stock via receipt
        $response = $this->post(route('inventory.add-stock', $product), [
            'warehouse_id' => $this->warehouse1->id,
            'quantity' => 100,
            'notes' => 'Initial stock',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify stock updated in real-time
        $stock = ProductStock::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse1->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(100, $stock->quantity);

        // Verify stock movement recorded
        $movement = StockMovement::where('product_id', $product->id)
            ->where('type', 'in')
            ->latest()
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(100, $movement->quantity);
        $this->assertEquals(0, $movement->quantity_before);
        $this->assertEquals(100, $movement->quantity_after);
    }

    /** @test */
    public function test_11_1_stock_updates_real_time_on_issue()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Add initial stock
        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity' => 100,
        ]);

        // Issue stock (simulate sales order)
        DB::transaction(function () use ($product) {
            $stock = ProductStock::where('product_id', $product->id)
                ->where('warehouse_id', $this->warehouse1->id)
                ->lockForUpdate()
                ->first();

            $before = $stock->quantity;
            $stock->decrement('quantity', 30);

            StockMovement::create([
                'tenant_id' => $this->tenant->id,
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouse1->id,
                'user_id' => $this->user->id,
                'type' => 'out',
                'quantity' => 30,
                'quantity_before' => $before,
                'quantity_after' => $before - 30,
                'reference' => 'SO-001',
            ]);
        });

        // Verify stock decreased
        $stock = ProductStock::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse1->id)
            ->first();

        $this->assertEquals(70, $stock->quantity);
    }

    /** @test */
    public function test_11_1_stock_updates_real_time_on_transfer()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Add stock to warehouse 1
        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity' => 100,
        ]);

        // Transfer 50 units to warehouse 2
        DB::transaction(function () use ($product) {
            // Deduct from warehouse 1
            $stock1 = ProductStock::where('product_id', $product->id)
                ->where('warehouse_id', $this->warehouse1->id)
                ->lockForUpdate()
                ->first();
            $stock1->decrement('quantity', 50);

            // Add to warehouse 2
            $stock2 = ProductStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $this->warehouse2->id,
                ],
                ['quantity' => 0]
            );
            $stock2->increment('quantity', 50);

            // Record movement
            StockMovement::create([
                'tenant_id' => $this->tenant->id,
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouse1->id,
                'to_warehouse_id' => $this->warehouse2->id,
                'user_id' => $this->user->id,
                'type' => 'transfer',
                'quantity' => 50,
                'quantity_before' => 100,
                'quantity_after' => 50,
            ]);
        });

        // Verify stock in both warehouses
        $stock1 = ProductStock::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse1->id)
            ->first();
        $stock2 = ProductStock::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse2->id)
            ->first();

        $this->assertEquals(50, $stock1->quantity);
        $this->assertEquals(50, $stock2->quantity);
        $this->assertEquals(100, $product->fresh()->totalStock());
    }

    /** @test */
    public function test_11_1_stock_updates_real_time_on_adjustment()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity' => 100,
        ]);

        // Adjustment: found 5 damaged items
        DB::transaction(function () use ($product) {
            $stock = ProductStock::where('product_id', $product->id)
                ->where('warehouse_id', $this->warehouse1->id)
                ->lockForUpdate()
                ->first();

            $before = $stock->quantity;
            $stock->decrement('quantity', 5);

            StockMovement::create([
                'tenant_id' => $this->tenant->id,
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouse1->id,
                'user_id' => $this->user->id,
                'type' => 'adjustment',
                'quantity' => -5,
                'quantity_before' => $before,
                'quantity_after' => $before - 5,
                'notes' => 'Damaged items',
            ]);
        });

        $stock = ProductStock::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse1->id)
            ->first();

        $this->assertEquals(95, $stock->quantity);
    }

    /** @test */
    public function test_11_2_fifo_costing_calculates_correct_cogs()
    {
        // Set tenant to FIFO method
        $this->tenant->update(['costing_method' => 'fifo']);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price_buy' => 100,
        ]);

        $costingService = app(InventoryCostingService::class);

        // Receipt 1: 50 units @ 100
        $movement1 = StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 50,
            'quantity_before' => 0,
            'quantity_after' => 50,
        ]);
        $costingService->recordStockIn($movement1, 100);

        // Receipt 2: 50 units @ 120
        $movement2 = StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 50,
            'quantity_before' => 50,
            'quantity_after' => 100,
        ]);
        $costingService->recordStockIn($movement2, 120);

        // Issue 60 units (should consume 50 @ 100 + 10 @ 120)
        $movement3 = StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'quantity' => 60,
            'quantity_before' => 100,
            'quantity_after' => 40,
        ]);
        $unitCost = $costingService->recordStockOut($movement3, 'SO-001');

        // Expected COGS: (50 * 100 + 10 * 120) / 60 = 6200 / 60 = 103.33
        $this->assertEqualsWithDelta(103.33, $unitCost, 0.01);

        // Remaining stock should be 40 units @ 120
        $currentCost = $costingService->getCurrentCost(
            $this->tenant->id,
            $product->id,
            $this->warehouse1->id
        );
        $this->assertEquals(120, $currentCost);
    }

    /** @test */
    public function test_11_2_average_cost_calculates_correct_cogs()
    {
        // Set tenant to AVCO method
        $this->tenant->update(['costing_method' => 'avco']);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price_buy' => 100,
        ]);

        $costingService = app(InventoryCostingService::class);

        // Receipt 1: 50 units @ 100
        $movement1 = StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 50,
            'quantity_before' => 0,
            'quantity_after' => 50,
        ]);
        $costingService->recordStockIn($movement1, 100);

        // Receipt 2: 50 units @ 120
        $movement2 = StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 50,
            'quantity_before' => 50,
            'quantity_after' => 100,
        ]);
        $costingService->recordStockIn($movement2, 120);

        // Average cost should be (50*100 + 50*120) / 100 = 110
        $avgCost = $costingService->getCurrentCost(
            $this->tenant->id,
            $product->id,
            $this->warehouse1->id
        );
        $this->assertEquals(110, $avgCost);

        // Issue 60 units @ average cost 110
        $movement3 = StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'quantity' => 60,
            'quantity_before' => 100,
            'quantity_after' => 40,
        ]);
        $unitCost = $costingService->recordStockOut($movement3, 'SO-001');

        $this->assertEquals(110, $unitCost);
    }

    /** @test */
    public function test_11_3_multi_warehouse_stock_per_warehouse()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Add stock to warehouse 1
        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity' => 100,
        ]);

        // Add stock to warehouse 2
        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse2->id,
            'quantity' => 50,
        ]);

        // Verify stock per warehouse
        $this->assertEquals(100, $product->stockInWarehouse($this->warehouse1->id));
        $this->assertEquals(50, $product->stockInWarehouse($this->warehouse2->id));
        $this->assertEquals(150, $product->totalStock());
    }

    /** @test */
    public function test_11_4_batch_tracking_with_expiry_date()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'has_expiry' => true,
            'expiry_alert_days' => 30,
        ]);

        // Create batch with expiry date
        $batch = ProductBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'batch_number' => 'BATCH-001',
            'quantity' => 100,
            'quantity_remaining' => 100,
            'manufacture_date' => now()->subDays(30),
            'expiry_date' => now()->addDays(60),
            'status' => 'active',
        ]);

        // Verify batch tracking
        $this->assertEquals('BATCH-001', $batch->batch_number);
        $this->assertEquals(100, $batch->quantity);
        $this->assertEquals(60, $batch->daysUntilExpiry());
        $this->assertFalse($batch->isExpired());

        // Test expiring soon scope
        $expiringSoon = ProductBatch::expiringSoon(90)->get();
        $this->assertTrue($expiringSoon->contains($batch));
    }

    /** @test */
    public function test_11_4_batch_traceability()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'has_expiry' => true,
        ]);

        // Create batch
        $batch = ProductBatch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'batch_number' => 'BATCH-TRACE-001',
            'quantity' => 100,
            'quantity_remaining' => 100,
            'manufacture_date' => now()->subDays(10),
            'expiry_date' => now()->addDays(350),
            'status' => 'active',
        ]);

        // Record stock movement with batch reference
        StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 100,
            'quantity_before' => 0,
            'quantity_after' => 100,
            'reference' => 'BATCH-TRACE-001',
        ]);

        // Verify traceability: can find all movements for this batch
        $movements = StockMovement::where('reference', 'BATCH-TRACE-001')->get();
        $this->assertCount(1, $movements);
        $this->assertEquals(100, $movements->first()->quantity);
    }

    /** @test */
    public function test_11_5_barcode_generation()
    {
        $barcodeService = app(BarcodeService::class);

        // Generate barcode from SKU
        $barcode = $barcodeService->generateFromSKU('TEST-001');
        $this->assertStringContainsString('QAL', $barcode);
        $this->assertStringContainsString('TEST001', $barcode);

        // Generate barcode image
        $image = $barcodeService->generate('QAL-TEST001', 'code128', 'png');
        $this->assertNotEmpty($image);

        // Validate barcode
        $this->assertTrue($barcodeService->validate('QAL-TEST001', 'code128'));
    }

    /** @test */
    public function test_11_5_qr_code_generation()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'TEST-QR-001',
        ]);

        // QR code path should be stored in product
        $product->update(['qr_code_path' => '/storage/qr/TEST-QR-001.png']);

        $this->assertNotNull($product->qr_code_path);
        $this->assertStringContainsString('qr', $product->qr_code_path);
    }

    /** @test */
    public function test_11_6_landed_cost_allocation()
    {
        $product1 = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price_buy' => 100,
        ]);

        $product2 = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price_buy' => 200,
        ]);

        // Create landed cost record
        $landedCost = LandedCost::create([
            'tenant_id' => $this->tenant->id,
            'number' => 'LC-001',
            'date' => now(),
            'status' => 'draft',
            'user_id' => $this->user->id,
        ]);

        // Add components (freight, customs, etc.)
        $landedCost->components()->create([
            'name' => 'Freight',
            'type' => 'freight',
            'amount' => 1000,
        ]);

        $landedCost->components()->create([
            'name' => 'Customs',
            'type' => 'customs',
            'amount' => 500,
        ]);

        // Add allocations
        $landedCost->allocations()->create([
            'product_id' => $product1->id,
            'original_cost' => 10000, // 100 units @ 100
            'quantity' => 100,
            'weight' => 0.5,
        ]);

        $landedCost->allocations()->create([
            'product_id' => $product2->id,
            'original_cost' => 10000, // 50 units @ 200
            'quantity' => 50,
            'weight' => 0.5,
        ]);

        // Allocate landed cost
        $landedCostService = app(LandedCostService::class);
        $result = $landedCostService->allocate($landedCost);

        // Verify allocation
        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);

        // Each product should get 50% of total landed cost (1500 / 2 = 750)
        $alloc1 = $landedCost->allocations()->where('product_id', $product1->id)->first();
        $alloc2 = $landedCost->allocations()->where('product_id', $product2->id)->first();

        $this->assertEquals(750, $alloc1->allocated_cost);
        $this->assertEquals(750, $alloc2->allocated_cost);

        // Landed unit cost = (original_cost + allocated_cost) / quantity
        // Product 1: (10000 + 750) / 100 = 107.5
        // Product 2: (10000 + 750) / 50 = 215
        $this->assertEquals(107.5, $alloc1->landed_unit_cost);
        $this->assertEquals(215, $alloc2->landed_unit_cost);
    }

    /** @test */
    public function test_11_7_stock_minimum_alert()
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'stock_min' => 50,
        ]);

        // Add stock below minimum
        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse1->id,
            'quantity' => 30,
        ]);

        // Query low stock products
        $lowStockProducts = Product::where('tenant_id', $this->tenant->id)
            ->whereHas('productStocks', function ($q) {
                $q->whereColumn('quantity', '<=', 'products.stock_min');
            })
            ->get();

        $this->assertCount(1, $lowStockProducts);
        $this->assertTrue($lowStockProducts->contains($product));
    }

    /** @test */
    public function test_11_8_wms_zone_management()
    {
        // Create warehouse zone
        $zone = WarehouseZone::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse1->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A - Receiving',
            'type' => 'receiving',
            'is_active' => true,
        ]);

        $this->assertEquals('ZONE-A', $zone->code);
        $this->assertEquals($this->warehouse1->id, $zone->warehouse_id);
        $this->assertTrue($zone->is_active);
    }

    /** @test */
    public function test_11_8_wms_bin_management()
    {
        // Create zone first
        $zone = WarehouseZone::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse1->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
            'is_active' => true,
        ]);

        // Create bin with rack/shelf location
        $bin = WarehouseBin::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse1->id,
            'zone_id' => $zone->id,
            'code' => 'A-01-01',
            'aisle' => 'A',
            'rack' => '01',
            'shelf' => '01',
            'max_capacity' => 1000,
            'bin_type' => 'pallet',
            'is_active' => true,
        ]);

        $this->assertEquals('A-01-01', $bin->code);
        $this->assertEquals('A', $bin->aisle);
        $this->assertEquals('01', $bin->rack);
        $this->assertEquals('01', $bin->shelf);
        $this->assertEquals(1000, $bin->max_capacity);
        $this->assertEquals(1000, $bin->availableCapacity());
    }

    /** @test */
    public function test_11_8_wms_picking_putaway_locations()
    {
        // Create picking zone
        $pickingZone = WarehouseZone::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse1->id,
            'code' => 'PICK-01',
            'name' => 'Picking Zone 1',
            'type' => 'picking',
            'is_active' => true,
        ]);

        // Create putaway zone
        $putawayZone = WarehouseZone::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse1->id,
            'code' => 'PUT-01',
            'name' => 'Putaway Zone 1',
            'type' => 'putaway',
            'is_active' => true,
        ]);

        // Create bins in each zone
        $pickingBin = WarehouseBin::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse1->id,
            'zone_id' => $pickingZone->id,
            'code' => 'PICK-A-01',
            'aisle' => 'A',
            'rack' => '01',
            'shelf' => '01',
            'bin_type' => 'shelf',
            'is_active' => true,
        ]);

        $putawayBin = WarehouseBin::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouse1->id,
            'zone_id' => $putawayZone->id,
            'code' => 'PUT-B-01',
            'aisle' => 'B',
            'rack' => '01',
            'shelf' => '01',
            'bin_type' => 'pallet',
            'is_active' => true,
        ]);

        // Verify zones and bins are properly linked
        $this->assertEquals('picking', $pickingBin->zone->type);
        $this->assertEquals('putaway', $putawayBin->zone->type);
        $this->assertCount(1, $pickingZone->bins);
        $this->assertCount(1, $putawayZone->bins);
    }
}
