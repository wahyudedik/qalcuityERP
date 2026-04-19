<?php

namespace Tests\Unit\Audit;

use App\Models\Product;
use App\Models\ProductAvgCost;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryCostingService;
use Tests\TestCase;

/**
 * Task 24.8: Unit test for FIFO and Average Cost stock calculation
 * 
 * Validates: Requirements 11.2
 * 
 * This test ensures that:
 * - FIFO (First-In-First-Out) costing method calculates HPP correctly
 * - Average Cost costing method calculates HPP correctly
 * - Stock valuation is accurate for both methods
 * - Cost calculations handle multiple stock movements correctly
 */
class StockCalculationTest extends TestCase
{

    protected Tenant $tenant;
    protected User $user;
    protected Warehouse $warehouse;
    protected Product $product;
    protected InventoryCostingService $costingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant(['costing_method' => 'simple']);
        $this->user = $this->createAdminUser($this->tenant);
        $this->warehouse = $this->createWarehouse($this->tenant->id);
        $this->product = $this->createProduct($this->tenant->id, [
            'price_buy' => 10000,
        ]);

        $this->costingService = app(InventoryCostingService::class);

        $this->actingAs($this->user);
    }

    /** Helper to create a StockMovement with required fields */
    private function makeMovement(array $attrs): StockMovement
    {
        return StockMovement::create(array_merge([
            'tenant_id'    => $this->tenant->id,
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'user_id'      => $this->user->id,
        ], $attrs));
    }

    public function test_fifo_method_calculates_cogs_correctly_with_single_batch()
    {
        $this->tenant->update(['costing_method' => 'fifo']);

        $stockIn = $this->makeMovement(['type' => 'in', 'quantity' => 100, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn, 10000);

        $stockOut = $this->makeMovement(['type' => 'out', 'quantity' => -50, 'reference' => 'Sale 001']);
        $unitCost = $this->costingService->recordStockOut($stockOut, 'Sale 001');

        $this->assertEquals(10000, $unitCost);
        $this->assertEquals(10000, $stockOut->fresh()->cost_price);
        $this->assertEquals(500000, $stockOut->fresh()->cost_total);
    }

    public function test_fifo_method_consumes_oldest_batch_first()
    {
        $this->tenant->update(['costing_method' => 'fifo']);

        $stockIn1 = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn1, 10000);

        $stockIn2 = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 002']);
        $this->costingService->recordStockIn($stockIn2, 12000);

        $stockOut = $this->makeMovement(['type' => 'out', 'quantity' => -60, 'reference' => 'Sale 001']);
        $unitCost = $this->costingService->recordStockOut($stockOut, 'Sale 001');

        // Expected: (50 * 10,000 + 10 * 12,000) / 60 = 10,333.33
        $expectedCost = (50 * 10000 + 10 * 12000) / 60;
        $this->assertEquals($expectedCost, $unitCost, '', 0.01);

        $firstBatch = ProductBatch::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->orderBy('created_at')
            ->first();

        $this->assertEquals(0, $firstBatch->quantity_remaining);
        $this->assertEquals('consumed', $firstBatch->status);

        $secondBatch = ProductBatch::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->orderBy('created_at')
            ->skip(1)
            ->first();

        $this->assertEquals(40, $secondBatch->quantity_remaining);
        $this->assertEquals('active', $secondBatch->status);
    }

    public function test_fifo_method_handles_multiple_batches_correctly()
    {
        $this->tenant->update(['costing_method' => 'fifo']);

        $stockIn1 = $this->makeMovement(['type' => 'in', 'quantity' => 30, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn1, 8000);

        $stockIn2 = $this->makeMovement(['type' => 'in', 'quantity' => 40, 'reference' => 'Purchase 002']);
        $this->costingService->recordStockIn($stockIn2, 10000);

        $stockIn3 = $this->makeMovement(['type' => 'in', 'quantity' => 30, 'reference' => 'Purchase 003']);
        $this->costingService->recordStockIn($stockIn3, 12000);

        $stockOut = $this->makeMovement(['type' => 'out', 'quantity' => -80, 'reference' => 'Sale 001']);
        $unitCost = $this->costingService->recordStockOut($stockOut, 'Sale 001');

        // Expected: (30 * 8,000 + 40 * 10,000 + 10 * 12,000) / 80 = 9,750
        $expectedCost = (30 * 8000 + 40 * 10000 + 10 * 12000) / 80;
        $this->assertEquals($expectedCost, $unitCost, '', 0.01);

        $activeBatches = ProductBatch::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->get();

        $this->assertCount(1, $activeBatches);
        $this->assertEquals(20, $activeBatches->first()->quantity_remaining);
        $this->assertEquals(12000, $activeBatches->first()->cost_price);
    }

    public function test_average_cost_method_calculates_weighted_average_correctly()
    {
        $this->tenant->update(['costing_method' => 'avco']);

        $stockIn1 = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn1, 10000);

        $avgCost1 = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(10000, $avgCost1);

        $stockIn2 = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 002']);
        $this->costingService->recordStockIn($stockIn2, 12000);

        // Average cost should be (50*10,000 + 50*12,000) / 100 = 11,000
        $avgCost2 = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(11000, $avgCost2);
    }

    public function test_average_cost_method_updates_after_stock_out()
    {
        $this->tenant->update(['costing_method' => 'avco']);

        $stockIn = $this->makeMovement(['type' => 'in', 'quantity' => 100, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn, 10000);

        $stockOut = $this->makeMovement(['type' => 'out', 'quantity' => -40, 'reference' => 'Sale 001']);
        $unitCost = $this->costingService->recordStockOut($stockOut, 'Sale 001');

        $this->assertEquals(10000, $unitCost);

        $avgCostRecord = ProductAvgCost::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertNotNull($avgCostRecord);
        $this->assertEquals(60, $avgCostRecord->total_qty);
        $this->assertEquals(10000, $avgCostRecord->avg_cost);
        $this->assertEquals(600000, $avgCostRecord->total_value);
    }

    public function test_average_cost_method_handles_multiple_purchases_and_sales()
    {
        $this->tenant->update(['costing_method' => 'avco']);

        $stockIn1 = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn1, 8000);

        $avgCost1 = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(8000, $avgCost1);

        $stockOut1 = $this->makeMovement(['type' => 'out', 'quantity' => -20, 'reference' => 'Sale 001']);
        $this->costingService->recordStockOut($stockOut1, 'Sale 001');

        $avgCost2 = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(8000, $avgCost2);

        $stockIn2 = $this->makeMovement(['type' => 'in', 'quantity' => 70, 'reference' => 'Purchase 002']);
        $this->costingService->recordStockIn($stockIn2, 12000);

        // New average: (30*8,000 + 70*12,000) / 100 = 10,800
        $avgCost3 = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(10800, $avgCost3);

        $stockOut2 = $this->makeMovement(['type' => 'out', 'quantity' => -50, 'reference' => 'Sale 002']);
        $unitCost2 = $this->costingService->recordStockOut($stockOut2, 'Sale 002');

        $this->assertEquals(10800, $unitCost2);

        $avgCostRecord = ProductAvgCost::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(50, $avgCostRecord->total_qty);
        $this->assertEquals(10800, $avgCostRecord->avg_cost);
    }

    public function test_simple_method_uses_product_price_buy()
    {
        $this->assertEquals('simple', $this->tenant->costing_method);

        $stockIn = $this->makeMovement(['type' => 'in', 'quantity' => 100, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn, 15000);

        $stockOut = $this->makeMovement(['type' => 'out', 'quantity' => -50, 'reference' => 'Sale 001']);
        $unitCost = $this->costingService->recordStockOut($stockOut, 'Sale 001');

        $this->assertEquals(10000, $unitCost);
    }

    public function test_get_current_cost_returns_correct_value_for_each_method()
    {
        // Test FIFO
        $this->tenant->update(['costing_method' => 'fifo']);

        $stockIn1 = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn1, 11000);

        $fifoCost = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(11000, $fifoCost);

        // Test AVCO
        $this->tenant->update(['costing_method' => 'avco']);

        ProductBatch::where('product_id', $this->product->id)->delete();
        ProductAvgCost::where('product_id', $this->product->id)->delete();

        $stockIn2 = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 002']);
        $this->costingService->recordStockIn($stockIn2, 13000);

        $avcoCost = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(13000, $avcoCost);

        // Test Simple
        $this->tenant->update(['costing_method' => 'simple']);

        $simpleCost = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(10000, $simpleCost);
    }

    public function test_valuation_report_calculates_total_value_correctly()
    {
        $this->tenant->update(['costing_method' => 'avco']);

        $this->setStock($this->product->id, $this->warehouse->id, 0);

        $stockIn = $this->makeMovement(['type' => 'in', 'quantity' => 100, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn, 10000);

        $this->setStock($this->product->id, $this->warehouse->id, 100);

        $report = $this->costingService->valuationReport($this->tenant->id);

        $this->assertEquals('avco', $report['method']);
        $this->assertNotEmpty($report['rows']);
        $this->assertEquals(1000000, $report['total']);
    }

    public function test_cogs_report_calculates_total_cogs_correctly()
    {
        $this->tenant->update(['costing_method' => 'fifo']);

        $stockIn = $this->makeMovement(['type' => 'in', 'quantity' => 100, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn, 10000);

        $stockOut1 = $this->makeMovement(['type' => 'out', 'quantity' => -30, 'reference' => 'Sale 001']);
        $this->costingService->recordStockOut($stockOut1, 'Sale 001');

        $stockOut2 = $this->makeMovement(['type' => 'out', 'quantity' => -20, 'reference' => 'Sale 002']);
        $this->costingService->recordStockOut($stockOut2, 'Sale 002');

        $report = $this->costingService->cogsReport(
            $this->tenant->id,
            today()->toDateString(),
            today()->toDateString()
        );

        $this->assertEquals('fifo', $report['method']);
        $this->assertNotEmpty($report['rows']);
        $this->assertEquals(500000, $report['total_cogs']);
    }

    public function test_fifo_handles_zero_quantity_remaining_correctly()
    {
        $this->tenant->update(['costing_method' => 'fifo']);

        $stockIn = $this->makeMovement(['type' => 'in', 'quantity' => 50, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn, 10000);

        $stockOut = $this->makeMovement(['type' => 'out', 'quantity' => -50, 'reference' => 'Sale 001']);
        $this->costingService->recordStockOut($stockOut, 'Sale 001');

        $batch = ProductBatch::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(0, $batch->quantity_remaining);
        $this->assertEquals('consumed', $batch->status);
    }

    public function test_average_cost_handles_decimal_quantities()
    {
        $this->tenant->update(['costing_method' => 'avco']);

        $stockIn = $this->makeMovement(['type' => 'in', 'quantity' => 33.5, 'reference' => 'Purchase 001']);
        $this->costingService->recordStockIn($stockIn, 10000);

        $avgCost = $this->costingService->getCurrentCost($this->tenant->id, $this->product->id, $this->warehouse->id);
        $this->assertEquals(10000, $avgCost);

        $stockOut = $this->makeMovement(['type' => 'out', 'quantity' => -15.5, 'reference' => 'Sale 001']);
        $unitCost = $this->costingService->recordStockOut($stockOut, 'Sale 001');

        $this->assertEquals(10000, $unitCost);

        $avgCostRecord = ProductAvgCost::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(18, $avgCostRecord->total_qty);
        $this->assertEquals(10000, $avgCostRecord->avg_cost);
    }
}
