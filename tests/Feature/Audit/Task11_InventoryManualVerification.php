<?php

namespace Tests\Feature\Audit;

use App\Models\LandedCost;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use App\Services\BarcodeService;
use App\Services\InventoryCostingService;
use App\Services\LandedCostService;
use Tests\TestCase;

/**
 * Task 11: Manual Verification Script
 *
 * This script verifies that all inventory models, services, and features exist
 * and are properly configured.
 */
class Task11_InventoryManualVerification extends TestCase
{
    /** @test */
    public function test_all_inventory_models_exist()
    {
        $models = [
            Product::class,
            Warehouse::class,
            ProductStock::class,
            StockMovement::class,
            ProductBatch::class,
            WarehouseZone::class,
            WarehouseBin::class,
            LandedCost::class,
        ];

        foreach ($models as $model) {
            $this->assertTrue(class_exists($model), "Model {$model} does not exist");
        }
    }

    /** @test */
    public function test_all_inventory_services_exist()
    {
        $services = [
            InventoryCostingService::class,
            BarcodeService::class,
            LandedCostService::class,
        ];

        foreach ($services as $service) {
            $this->assertTrue(class_exists($service), "Service {$service} does not exist");
        }
    }

    /** @test */
    public function test_product_model_has_required_methods()
    {
        $methods = [
            'stockInWarehouse',
            'totalStock',
            'stockMovements',
            'productStocks',
            'batches',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(Product::class, $method),
                "Product model missing method: {$method}"
            );
        }
    }

    /** @test */
    public function test_warehouse_model_has_wms_relations()
    {
        $methods = ['zones', 'bins', 'productStocks', 'stockMovements'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(Warehouse::class, $method),
                "Warehouse model missing method: {$method}"
            );
        }
    }

    /** @test */
    public function test_product_batch_has_expiry_methods()
    {
        $methods = ['daysUntilExpiry', 'isExpired'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(ProductBatch::class, $method),
                "ProductBatch model missing method: {$method}"
            );
        }
    }

    /** @test */
    public function test_costing_service_has_required_methods()
    {
        $methods = [
            'recordStockIn',
            'recordStockOut',
            'getCurrentCost',
            'valuationReport',
            'cogsReport',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(InventoryCostingService::class, $method),
                "InventoryCostingService missing method: {$method}"
            );
        }
    }

    /** @test */
    public function test_barcode_service_has_required_methods()
    {
        $methods = [
            'generate',
            'generateFromSKU',
            'validate',
            'batchGenerate',
            'printLabel',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(BarcodeService::class, $method),
                "BarcodeService missing method: {$method}"
            );
        }
    }

    /** @test */
    public function test_landed_cost_service_has_required_methods()
    {
        $methods = ['allocate', 'updateProductCosts'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(LandedCostService::class, $method),
                "LandedCostService missing method: {$method}"
            );
        }
    }

    /** @test */
    public function test_warehouse_bin_has_capacity_methods()
    {
        $methods = ['usedCapacity', 'availableCapacity'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(WarehouseBin::class, $method),
                "WarehouseBin model missing method: {$method}"
            );
        }
    }
}
