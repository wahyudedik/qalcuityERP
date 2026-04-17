<?php

namespace Tests\Feature\BugExploration;

use App\Models\Bom;
use App\Models\BomLine;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Manufacturing\BomExplosionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bug 1.19 — BOM Explosion Hanya 2 Level
 *
 * Membuktikan bahwa BomExplosionService tidak melakukan recursive explosion
 * untuk komponen bertingkat lebih dari 2 level.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 *
 * CATATAN: Berdasarkan kode aktual, BomExplosionService menggunakan $bom->explode()
 * yang mungkin sudah recursive. Test ini memverifikasi apakah level 3 benar-benar diproses.
 */
class ManufacturingBomExplosionTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);

        $this->actingAs($this->user);
    }

    /**
     * @test
     * Bug 1.19: BOM explosion harus menyertakan komponen level 3
     *
     * Setup: Produk A → Sub-assembly B → Sub-assembly C → Komponen D (level 3)
     *
     * AKAN GAGAL jika BOM explosion tidak recursive
     *
     * Validates: Requirements 1.19
     */
    public function test_bom_explosion_includes_level_3_components(): void
    {
        // Arrange: Buat BOM 3 level
        // Level 0: Produk Jadi (Meja)
        $productMeja = $this->createProduct($this->tenant->id, [
            'name' => 'Meja Kantor',
            'sku' => 'MEJA-001',
        ]);

        // Level 1: Sub-assembly (Rangka Meja)
        $productRangka = $this->createProduct($this->tenant->id, [
            'name' => 'Rangka Meja',
            'sku' => 'RANGKA-001',
        ]);

        // Level 2: Sub-assembly (Kaki Meja)
        $productKaki = $this->createProduct($this->tenant->id, [
            'name' => 'Kaki Meja',
            'sku' => 'KAKI-001',
        ]);

        // Level 3: Komponen dasar (Baut)
        $productBaut = $this->createProduct($this->tenant->id, [
            'name' => 'Baut M8',
            'sku' => 'BAUT-M8',
        ]);

        // BOM Level 0: Meja → Rangka (1 unit)
        $bomMeja = Bom::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $productMeja->id,
            'name' => 'BOM Meja Kantor',
            'batch_size' => 1,
            'is_active' => true,
        ]);

        BomLine::create([
            'bom_id' => $bomMeja->id,
            'product_id' => $productRangka->id,
            'quantity_per_batch' => 1,
            'unit' => 'unit',
            'child_bom_id' => null, // Akan diisi setelah BOM Rangka dibuat
        ]);

        // BOM Level 1: Rangka → Kaki (4 unit)
        $bomRangka = Bom::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $productRangka->id,
            'name' => 'BOM Rangka Meja',
            'batch_size' => 1,
            'is_active' => true,
        ]);

        BomLine::create([
            'bom_id' => $bomRangka->id,
            'product_id' => $productKaki->id,
            'quantity_per_batch' => 4,
            'unit' => 'unit',
            'child_bom_id' => null, // Akan diisi setelah BOM Kaki dibuat
        ]);

        // BOM Level 2: Kaki → Baut (4 unit per kaki)
        $bomKaki = Bom::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $productKaki->id,
            'name' => 'BOM Kaki Meja',
            'batch_size' => 1,
            'is_active' => true,
        ]);

        BomLine::create([
            'bom_id' => $bomKaki->id,
            'product_id' => $productBaut->id,
            'quantity_per_batch' => 4,
            'unit' => 'pcs',
        ]);

        // Update child_bom_id untuk BOM lines
        BomLine::where('bom_id', $bomMeja->id)
            ->where('product_id', $productRangka->id)
            ->update(['child_bom_id' => $bomRangka->id]);

        BomLine::where('bom_id', $bomRangka->id)
            ->where('product_id', $productKaki->id)
            ->update(['child_bom_id' => $bomKaki->id]);

        // Act: Explode BOM
        $service = app(BomExplosionService::class);
        $result = $service->explodeBom($bomMeja, 1, $this->tenant->id, false);

        // Assert: Harus berhasil
        $this->assertTrue($result['success'], "BOM explosion gagal: " . ($result['error'] ?? 'unknown'));

        // Assert: Harus ada komponen level 3 (Baut M8)
        $materials = $result['materials'] ?? [];
        $productIds = array_column($materials, 'product_id');

        // Test ini AKAN GAGAL jika BOM explosion tidak recursive (tidak menyertakan level 3)
        $this->assertContains(
            $productBaut->id,
            $productIds,
            "Bug 1.19: BOM explosion tidak menyertakan komponen level 3 (Baut M8). " .
            "Komponen yang ditemukan: " . implode(', ', array_column($materials, 'product_name')) . ". " .
            "BOM explosion seharusnya recursive untuk semua level sub-assembly."
        );

        // Assert: Max level harus >= 2 (level 3 = index 2)
        $maxLevel = $result['max_level'] ?? 0;
        $this->assertGreaterThanOrEqual(
            2,
            $maxLevel,
            "Bug 1.19: Max level BOM explosion adalah {$maxLevel}, seharusnya >= 2 " .
            "untuk BOM dengan 3 level sub-assembly."
        );
    }
}
