<?php

namespace Tests\Feature\Preservation;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Preservation Test — Single-Warehouse Stock Deduction Without Concurrent Requests
 *
 * Memverifikasi bahwa pengurangan stok single-warehouse tanpa concurrent requests
 * yang SUDAH BENAR tidak berubah setelah fix diterapkan.
 *
 * Ini adalah NON-BUGGY case — kontras dengan Bug 1.15 yang menguji concurrent requests.
 * Pengurangan stok normal (satu request) harus tetap berfungsi dengan benar.
 *
 * Validates: Requirements 3.15
 */
class StockDeductionPreservationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;
    private Product $product;
    private Warehouse $warehouse;
    private ProductStock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant    = $this->createTenant();
        $this->user      = $this->createAdminUser($this->tenant);
        $this->warehouse = $this->createWarehouse($this->tenant->id);
        $this->product   = $this->createProduct($this->tenant->id);

        // Stok awal: 50 unit
        $this->stock = $this->setStock($this->product->id, $this->warehouse->id, 50);

        $this->actingAs($this->user);
    }

    // ── Requirement 3.15: Single request stock deduction ─────────────────────

    /**
     * @test
     * Preservation 3.15: Pengurangan stok single request berfungsi dengan benar
     *
     * Ini adalah NON-BUGGY case. Satu request pengurangan stok harus berfungsi.
     * Validates: Requirements 3.15
     */
    public function test_single_stock_deduction_works_correctly(): void
    {
        $initialStock = 50;
        $deductAmount = 10;

        // Kurangi stok
        $this->stock->decrement('quantity', $deductAmount);

        $finalStock = ProductStock::find($this->stock->id)->quantity;

        $this->assertEquals(
            $initialStock - $deductAmount,
            $finalStock,
            "Stok harus berkurang dari {$initialStock} menjadi " . ($initialStock - $deductAmount)
        );
    }

    /**
     * @test
     * Preservation 3.15: Stok tidak menjadi negatif untuk pengurangan yang valid
     *
     * Validates: Requirements 3.15
     */
    public function test_stock_does_not_go_negative_for_valid_deduction(): void
    {
        $initialStock = 50;
        $deductAmount = 50; // Kurangi semua stok

        $this->stock->decrement('quantity', $deductAmount);

        $finalStock = ProductStock::find($this->stock->id)->quantity;

        $this->assertGreaterThanOrEqual(
            0,
            $finalStock,
            "Stok tidak boleh negatif setelah pengurangan yang valid"
        );

        $this->assertEquals(0, $finalStock, "Stok harus menjadi 0 setelah dikurangi semua");
    }

    /**
     * @test
     * Preservation 3.15: Pengurangan stok bertahap berfungsi dengan benar
     *
     * Validates: Requirements 3.15
     */
    public function test_sequential_stock_deductions_work_correctly(): void
    {
        $initialStock = 50;

        // Pengurangan pertama: -10
        $this->stock->decrement('quantity', 10);
        $afterFirst = ProductStock::find($this->stock->id)->quantity;
        $this->assertEquals(40, $afterFirst, "Setelah pengurangan pertama, stok harus 40");

        // Pengurangan kedua: -15
        $this->stock->refresh();
        $this->stock->decrement('quantity', 15);
        $afterSecond = ProductStock::find($this->stock->id)->quantity;
        $this->assertEquals(25, $afterSecond, "Setelah pengurangan kedua, stok harus 25");

        // Pengurangan ketiga: -25
        $this->stock->refresh();
        $this->stock->decrement('quantity', 25);
        $afterThird = ProductStock::find($this->stock->id)->quantity;
        $this->assertEquals(0, $afterThird, "Setelah pengurangan ketiga, stok harus 0");
    }

    /**
     * @test
     * Preservation 3.15: Pengurangan stok melalui sales order berfungsi
     *
     * Validates: Requirements 3.15
     */
    public function test_stock_deduction_via_sales_order_works(): void
    {
        $this->seedCoa($this->tenant->id);
        $customer = $this->createCustomer($this->tenant->id);
        $product  = $this->createProduct($this->tenant->id, ['price_sell' => 100000]);
        $this->setStock($product->id, $this->warehouse->id, 30);

        $response = $this->post(route('sales.store'), [
            'customer_id'  => $customer->id,
            'date'         => today()->toDateString(),
            'payment_type' => 'credit',
            'due_date'     => today()->addDays(30)->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items'        => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 100000, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        // Stok harus berkurang dari 30 menjadi 25
        $this->assertDatabaseHas('product_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 25,
        ]);
    }

    // ── Requirement 3.15: Stock quantity accuracy ─────────────────────────────

    /**
     * @test
     * Preservation 3.15: Jumlah stok akurat setelah pengurangan
     *
     * Validates: Requirements 3.15
     */
    public function test_stock_quantity_is_accurate_after_deduction(): void
    {
        $initialStock = 50;
        $deductAmount = 17; // Angka tidak bulat untuk memastikan akurasi

        $this->stock->decrement('quantity', $deductAmount);

        $finalStock = ProductStock::find($this->stock->id)->quantity;

        $this->assertEquals(
            $initialStock - $deductAmount,
            $finalStock,
            "Jumlah stok harus akurat: {$initialStock} - {$deductAmount} = " . ($initialStock - $deductAmount)
        );
    }

    /**
     * @test
     * Preservation 3.15: ProductStock.updateOrCreate berfungsi untuk set stok
     *
     * Validates: Requirements 3.15
     */
    public function test_product_stock_update_or_create_works(): void
    {
        // Update stok yang sudah ada
        $updated = ProductStock::updateOrCreate(
            ['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id],
            ['quantity' => 100]
        );

        $this->assertEquals(100, $updated->quantity, "Stok harus diupdate menjadi 100");

        // Buat stok baru untuk produk lain
        $newProduct = $this->createProduct($this->tenant->id);
        $newStock = ProductStock::updateOrCreate(
            ['product_id' => $newProduct->id, 'warehouse_id' => $this->warehouse->id],
            ['quantity' => 25]
        );

        $this->assertEquals(25, $newStock->quantity, "Stok baru harus dibuat dengan quantity 25");
    }

    /**
     * @test
     * Preservation 3.15: Pengurangan stok dalam DB transaction berfungsi
     *
     * Validates: Requirements 3.15
     */
    public function test_stock_deduction_within_db_transaction_works(): void
    {
        $initialStock = 50;
        $deductAmount = 20;

        DB::transaction(function () use ($deductAmount) {
            $stock = ProductStock::where('product_id', $this->product->id)
                ->where('warehouse_id', $this->warehouse->id)
                ->lockForUpdate()
                ->first();

            $stock->decrement('quantity', $deductAmount);
        });

        $finalStock = ProductStock::find($this->stock->id)->quantity;

        $this->assertEquals(
            $initialStock - $deductAmount,
            $finalStock,
            "Pengurangan stok dalam DB transaction harus berfungsi dengan benar"
        );
    }

    /**
     * @test
     * Preservation 3.15: Stok tidak berubah jika transaction di-rollback
     *
     * Validates: Requirements 3.15
     */
    public function test_stock_unchanged_when_transaction_rolled_back(): void
    {
        $initialStock = 50;

        try {
            DB::transaction(function () {
                $stock = ProductStock::where('product_id', $this->product->id)
                    ->where('warehouse_id', $this->warehouse->id)
                    ->lockForUpdate()
                    ->first();

                $stock->decrement('quantity', 30);

                // Simulasi error yang menyebabkan rollback
                throw new \RuntimeException("Simulasi error untuk rollback");
            });
        } catch (\RuntimeException $e) {
            // Expected
        }

        $finalStock = ProductStock::find($this->stock->id)->quantity;

        $this->assertEquals(
            $initialStock,
            $finalStock,
            "Stok harus kembali ke nilai awal setelah transaction di-rollback"
        );
    }

    /**
     * @test
     * Preservation 3.15: Stok tidak bisa negatif untuk pengurangan yang melebihi stok tersedia
     *
     * Validates: Requirements 3.15
     */
    public function test_stock_validation_prevents_over_deduction_via_sales_order(): void
    {
        $this->seedCoa($this->tenant->id);
        $customer = $this->createCustomer($this->tenant->id);
        $product  = $this->createProduct($this->tenant->id, ['price_sell' => 100000]);
        $this->setStock($product->id, $this->warehouse->id, 5); // Hanya 5 unit

        $response = $this->post(route('sales.store'), [
            'customer_id'  => $customer->id,
            'date'         => today()->toDateString(),
            'payment_type' => 'credit',
            'due_date'     => today()->addDays(30)->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items'        => [
                ['product_id' => $product->id, 'quantity' => 10, 'price' => 100000, 'discount' => 0], // Minta 10, stok hanya 5
            ],
        ]);

        // Harus ditolak karena stok tidak cukup
        $response->assertSessionHasErrors('items');

        // Stok tidak boleh berubah
        $this->assertDatabaseHas('product_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 5,
        ]);
    }
}
