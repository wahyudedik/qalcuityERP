<?php

namespace Tests\Feature;

use App\Http\Middleware\PermissionMiddleware;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    private $tenant;

    private $user;

    private $customer;

    private $product;

    private $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
        $this->customer = $this->createCustomer($this->tenant->id);
        $this->warehouse = $this->createWarehouse($this->tenant->id);
        $this->product = $this->createProduct($this->tenant->id, ['price_sell' => 100000]);

        $this->setStock($this->product->id, $this->warehouse->id, 10);
    }

    public function test_pos_checkout_creates_order_items_and_deducts_stock(): void
    {
        $this->actingAs($this->user);
        $this->withoutMiddleware(PermissionMiddleware::class);

        $response = $this->postJson(route('pos.checkout'), [
            'customer_id' => $this->customer->id,
            'items' => [
                ['id' => $this->product->id, 'qty' => 2, 'price' => 100000],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 500000,
            'discount' => 0,
            'tax' => 0,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'total' => 200000,
            'change' => 300000,
        ]);

        $orderNumber = $response->json('order_number');
        $this->assertNotEmpty($orderNumber);

        $this->assertDatabaseHas('sales_orders', [
            'tenant_id' => $this->tenant->id,
            'number' => $orderNumber,
            'status' => 'completed',
            'payment_type' => 'cash',
            'source' => 'pos',
        ]);

        $order = SalesOrder::where('tenant_id', $this->tenant->id)->where('number', $orderNumber)->first();
        $this->assertNotNull($order);

        $this->assertDatabaseHas('sales_order_items', [
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 100000,
            'total' => 200000,
        ]);

        $stock = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertNotNull($stock);
        $this->assertEquals(8, (int) $stock->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 2,
            'reference' => $orderNumber,
            'notes' => 'POS Checkout',
        ]);

        $movement = StockMovement::where('tenant_id', $this->tenant->id)
            ->where('reference', $orderNumber)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10, (int) $movement->quantity_before);
        $this->assertEquals(8, (int) $movement->quantity_after);
    }

    public function test_pos_checkout_rolls_back_when_stock_insufficient(): void
    {
        $this->actingAs($this->user);
        $this->withoutMiddleware(PermissionMiddleware::class);

        $response = $this->postJson(route('pos.checkout'), [
            'customer_id' => $this->customer->id,
            'items' => [
                ['id' => $this->product->id, 'qty' => 999, 'price' => 100000],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 0,
        ]);

        $response->assertStatus(500);
        $response->assertJson(['status' => 'error']);

        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseCount('sales_order_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);

        $stock = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertNotNull($stock);
        $this->assertEquals(10, (int) $stock->quantity);
    }
}
