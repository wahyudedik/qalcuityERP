<?php

namespace Tests\Feature\BugExploration;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bug 1.15 — Race Condition Stok Multi-Warehouse
 *
 * Membuktikan bahwa operasi pengurangan stok tidak menggunakan
 * database transaction dengan pessimistic locking, sehingga
 * stok bisa menjadi negatif saat concurrent requests.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class InventoriStockRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Product $product;
    private Warehouse $warehouse;
    private ProductStock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['is_active' => true]);
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);

        $this->actingAs($this->user);

        $this->warehouse = Warehouse::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Stok awal: 10 unit
        $this->stock = ProductStock::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);
    }

    /**
     * @test
     * Bug 1.15: Simulasi race condition - dua request concurrent mengurangi stok yang sama
     *
     * Dengan stok awal 10, dua request masing-masing mengurangi 8 unit.
     * Tanpa locking, keduanya bisa berhasil dan stok menjadi -6.
     *
     * AKAN GAGAL karena tidak ada pessimistic locking
     *
     * Validates: Requirements 1.15
     */
    public function test_concurrent_stock_deduction_does_not_result_in_negative_stock(): void
    {
        $initialStock = 10;
        $deductAmount = 8; // Masing-masing request mengurangi 8

        // Simulasi race condition: dua operasi membaca stok sebelum salah satunya commit
        // Ini mensimulasikan apa yang terjadi tanpa lockForUpdate()

        // Request 1: Baca stok (10), belum commit
        $stock1 = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        // Request 2: Baca stok (masih 10 karena request 1 belum commit)
        $stock2 = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        // Kedua request melihat stok = 10, keduanya valid untuk mengurangi 8
        $this->assertEquals(10, $stock1->quantity);
        $this->assertEquals(10, $stock2->quantity);

        // Request 1 commit: stok = 10 - 8 = 2
        $stock1->decrement('quantity', $deductAmount);

        // Request 2 commit: stok = 2 - 8 = -6 (NEGATIF!)
        $stock2->decrement('quantity', $deductAmount);

        // Refresh dari database
        $finalStock = ProductStock::find($this->stock->id)->quantity;

        // Assert: stok tidak boleh negatif
        // Test ini AKAN GAGAL karena stok menjadi -6
        $this->assertGreaterThanOrEqual(
            0,
            $finalStock,
            "Bug 1.15: Race condition menyebabkan stok negatif! " .
            "Stok awal: {$initialStock}, dua request masing-masing mengurangi {$deductAmount}. " .
            "Stok akhir: {$finalStock} (seharusnya >= 0). " .
            "Tidak ada pessimistic locking (lockForUpdate()) yang mencegah race condition."
        );
    }

    /**
     * @test
     * Bug 1.15: Verifikasi bahwa InventoryService menggunakan DB::transaction dengan lockForUpdate
     *
     * AKAN GAGAL jika InventoryService tidak menggunakan transaction dengan locking
     */
    public function test_inventory_service_uses_transaction_with_lock(): void
    {
        // Cari InventoryService atau service yang menangani pengurangan stok
        $serviceFiles = [
            'app/Services/InventoryService.php',
            'app/Services/OrderService.php',
            'app/Services/SalesOrderService.php',
        ];

        $hasTransactionWithLock = false;
        $foundFile = null;

        foreach ($serviceFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (
                    str_contains($content, 'DB::transaction') &&
                    str_contains($content, 'lockForUpdate')
                ) {
                    $hasTransactionWithLock = true;
                    $foundFile = $file;
                    break;
                }
            }
        }

        // Test ini AKAN GAGAL karena tidak ada service dengan transaction + lockForUpdate
        $this->assertTrue(
            $hasTransactionWithLock,
            "Bug 1.15: Tidak ditemukan service yang menggunakan DB::transaction() dengan " .
            "lockForUpdate() untuk operasi pengurangan stok. " .
            "File yang dicari: " . implode(', ', $serviceFiles) . ". " .
            "Tanpa pessimistic locking, race condition bisa menyebabkan stok negatif."
        );
    }
}
