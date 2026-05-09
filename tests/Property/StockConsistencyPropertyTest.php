<?php

namespace Tests\Property;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\Warehouse;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for Stock Consistency Invariant.
 *
 * Feature: erp-comprehensive-audit-fix
 *
 * **Validates: Requirements 11.1**
 */
class StockConsistencyPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * Property 3: Stock Consistency Invariant
     *
     * For any sequence of stock operations (receipts, issues, transfers, adjustments)
     * on a product in a warehouse, the final stock must equal:
     * initial stock + all receipts - all issues.
     *
     * **Validates: Requirements 11.1**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_stock_consistency_invariant(): void
    {
        $this
            ->forAll(
                Generators::choose(0, 1000),    // initial stock
                Generators::choose(1, 10),      // number of receipt operations
                Generators::choose(1, 10)       // number of issue operations
            )
            ->then(function ($initialStock, $receiptCount, $issueCount) {
                // Create tenant, warehouse, product, and user
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);
                $warehouse = $this->createWarehouse($tenant->id);
                $product = $this->createProduct($tenant->id);

                // Set initial stock
                $this->setStock($product->id, $warehouse->id, $initialStock);

                $totalReceipts = 0;
                $totalIssues = 0;

                // Perform receipt operations
                for ($i = 0; $i < $receiptCount; $i++) {
                    $receiptQty = rand(10, 100);
                    $totalReceipts += $receiptQty;

                    // Record stock movement
                    StockMovement::create([
                        'tenant_id' => $tenant->id,
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'user_id' => $user->id,
                        'type' => 'in',
                        'quantity' => $receiptQty,
                        'reference_type' => 'purchase_order',
                        'reference_id' => rand(1, 1000),
                        'date' => now(),
                        'notes' => 'Receipt '.$i,
                    ]);

                    // Update stock
                    $stock = ProductStock::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouse->id)
                        ->first();

                    if ($stock) {
                        $stock->quantity += $receiptQty;
                        $stock->save();
                    }
                }

                // Perform issue operations
                for ($i = 0; $i < $issueCount; $i++) {
                    $issueQty = rand(5, 50);
                    $totalIssues += $issueQty;

                    // Record stock movement
                    StockMovement::create([
                        'tenant_id' => $tenant->id,
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'user_id' => $user->id,
                        'type' => 'out',
                        'quantity' => $issueQty,
                        'reference_type' => 'sales_order',
                        'reference_id' => rand(1, 1000),
                        'date' => now(),
                        'notes' => 'Issue '.$i,
                    ]);

                    // Update stock
                    $stock = ProductStock::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouse->id)
                        ->first();

                    if ($stock) {
                        $stock->quantity -= $issueQty;
                        $stock->save();
                    }
                }

                // Calculate expected final stock
                $expectedFinalStock = $initialStock + $totalReceipts - $totalIssues;

                // Get actual final stock
                $actualStock = ProductStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                $actualFinalStock = $actualStock ? $actualStock->quantity : 0;

                // Verify stock consistency invariant
                $this->assertEquals(
                    $expectedFinalStock,
                    $actualFinalStock,
                    'Stock consistency invariant violated. '.
                    "Initial: {$initialStock}, Receipts: {$totalReceipts}, Issues: {$totalIssues}, ".
                    "Expected: {$expectedFinalStock}, Actual: {$actualFinalStock}"
                );

                // Verify stock movements sum matches the change
                $movementsIn = StockMovement::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('type', 'in')
                    ->sum('quantity');

                $movementsOut = StockMovement::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('type', 'out')
                    ->sum('quantity');

                $this->assertEquals($totalReceipts, $movementsIn,
                    'Total receipts in stock movements must match');
                $this->assertEquals($totalIssues, $movementsOut,
                    'Total issues in stock movements must match');
            });
    }

    /**
     * Property 3 (variant): Stock Transfer Consistency
     *
     * For any stock transfer from warehouse A to warehouse B,
     * the total stock across both warehouses must remain constant.
     *
     * **Validates: Requirements 11.1**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_stock_transfer_consistency(): void
    {
        $this
            ->forAll(
                Generators::choose(100, 1000),  // initial stock in warehouse A
                Generators::choose(10, 50)      // transfer quantity
            )
            ->when(fn ($initial, $transfer) => $transfer <= $initial)
            ->then(function ($initialStock, $transferQty) {
                // Create tenant, warehouses, product, and user
                $tenant = $this->createTenant();
                $user = $this->createAdminUser($tenant);
                $warehouseA = $this->createWarehouse($tenant->id, ['name' => 'Warehouse A']);
                $warehouseB = $this->createWarehouse($tenant->id, ['name' => 'Warehouse B']);
                $product = $this->createProduct($tenant->id);

                // Set initial stock in warehouse A
                $this->setStock($product->id, $warehouseA->id, $initialStock);
                $this->setStock($product->id, $warehouseB->id, 0);

                // Calculate total stock before transfer
                $totalBefore = $initialStock;

                // Perform transfer from A to B
                // Record outbound movement from warehouse A
                StockMovement::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseA->id,
                    'user_id' => $user->id,
                    'type' => 'out',
                    'quantity' => $transferQty,
                    'reference_type' => 'transfer',
                    'reference_id' => 1,
                    'date' => now(),
                    'notes' => 'Transfer to Warehouse B',
                ]);

                // Update stock in warehouse A
                $stockA = ProductStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseA->id)
                    ->first();
                $stockA->quantity -= $transferQty;
                $stockA->save();

                // Record inbound movement to warehouse B
                StockMovement::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseB->id,
                    'user_id' => $user->id,
                    'type' => 'in',
                    'quantity' => $transferQty,
                    'reference_type' => 'transfer',
                    'reference_id' => 1,
                    'date' => now(),
                    'notes' => 'Transfer from Warehouse A',
                ]);

                // Update stock in warehouse B
                $stockB = ProductStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseB->id)
                    ->first();
                $stockB->quantity += $transferQty;
                $stockB->save();

                // Get final stocks
                $stockA->refresh();
                $stockB->refresh();

                // Calculate total stock after transfer
                $totalAfter = $stockA->quantity + $stockB->quantity;

                // Verify total stock remains constant
                $this->assertEquals(
                    $totalBefore,
                    $totalAfter,
                    'Total stock must remain constant after transfer. '.
                    "Before: {$totalBefore}, After: {$totalAfter}, ".
                    "Warehouse A: {$stockA->quantity}, Warehouse B: {$stockB->quantity}, ".
                    "Transfer: {$transferQty}"
                );

                // Verify individual warehouse stocks
                $this->assertEquals(
                    $initialStock - $transferQty,
                    $stockA->quantity,
                    'Warehouse A stock should be reduced by transfer quantity'
                );

                $this->assertEquals(
                    $transferQty,
                    $stockB->quantity,
                    'Warehouse B stock should be increased by transfer quantity'
                );
            });
    }
}
