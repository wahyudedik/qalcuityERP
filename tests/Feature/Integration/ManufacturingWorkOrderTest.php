<?php

namespace Tests\Feature\Integration;

use App\Models\Bom;
use App\Models\BomLine;
use App\Models\Product;
use App\Services\Manufacturing\BomExplosionService;
use App\Services\ManufacturingService;
use Tests\TestCase;

/**
 * Integration Test 14.2 — Manufacturing Work Order End-to-End
 *
 * Verifikasi alur lengkap:
 * 1. Buat BOM dengan 3 level sub-assembly
 * 2. Explode BOM — semua komponen di semua level harus ada (Bug 1.19 fix)
 * 3. Verifikasi komponen level 3 tersedia untuk di-reserve dari inventory
 *
 * Validates: Requirements 2.19
 */
class ManufacturingWorkOrderTest extends TestCase
{

    private $tenant;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user   = $this->createAdminUser($this->tenant);
        $this->actingAs($this->user);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Buat struktur BOM 3 level:
     *   Level 0: Produk Jadi (Meja Kantor)
     *   Level 1: Sub-assembly (Rangka Meja)
     *   Level 2: Sub-assembly (Kaki Meja)
     *   Level 3: Komponen dasar (Baut M8)
     *
     * @return array{bom: Bom, products: array}
     */
    private function createThreeLevelBom(): array
    {
        // Produk-produk
        $productMeja   = $this->createProduct($this->tenant->id, ['name' => 'Meja Kantor',  'sku' => 'MEJA-001']);
        $productRangka = $this->createProduct($this->tenant->id, ['name' => 'Rangka Meja',  'sku' => 'RANGKA-001']);
        $productKaki   = $this->createProduct($this->tenant->id, ['name' => 'Kaki Meja',    'sku' => 'KAKI-001']);
        $productBaut   = $this->createProduct($this->tenant->id, ['name' => 'Baut M8',      'sku' => 'BAUT-M8']);
        $productPapan  = $this->createProduct($this->tenant->id, ['name' => 'Papan Kayu',   'sku' => 'PAPAN-001']);

        // BOM Level 2: Kaki Meja → Baut M8 (4 baut per kaki)
        $bomKaki = Bom::create([
            'tenant_id'  => $this->tenant->id,
            'product_id' => $productKaki->id,
            'name'       => 'BOM Kaki Meja',
            'batch_size' => 1,
            'is_active'  => true,
        ]);
        BomLine::create([
            'bom_id'             => $bomKaki->id,
            'product_id'         => $productBaut->id,
            'quantity_per_batch' => 4,
            'unit'               => 'pcs',
            'sort_order'         => 1,
        ]);

        // BOM Level 1: Rangka Meja → Kaki Meja (4 kaki) + Papan Kayu (1 papan)
        $bomRangka = Bom::create([
            'tenant_id'  => $this->tenant->id,
            'product_id' => $productRangka->id,
            'name'       => 'BOM Rangka Meja',
            'batch_size' => 1,
            'is_active'  => true,
        ]);
        BomLine::create([
            'bom_id'             => $bomRangka->id,
            'product_id'         => $productKaki->id,
            'quantity_per_batch' => 4,
            'unit'               => 'unit',
            'child_bom_id'       => $bomKaki->id,
            'sort_order'         => 1,
        ]);
        BomLine::create([
            'bom_id'             => $bomRangka->id,
            'product_id'         => $productPapan->id,
            'quantity_per_batch' => 1,
            'unit'               => 'lembar',
            'sort_order'         => 2,
        ]);

        // BOM Level 0: Meja Kantor → Rangka Meja (1 rangka)
        $bomMeja = Bom::create([
            'tenant_id'  => $this->tenant->id,
            'product_id' => $productMeja->id,
            'name'       => 'BOM Meja Kantor',
            'batch_size' => 1,
            'is_active'  => true,
        ]);
        BomLine::create([
            'bom_id'             => $bomMeja->id,
            'product_id'         => $productRangka->id,
            'quantity_per_batch' => 1,
            'unit'               => 'unit',
            'child_bom_id'       => $bomRangka->id,
            'sort_order'         => 1,
        ]);

        return [
            'bom'      => $bomMeja,
            'products' => [
                'meja'   => $productMeja,
                'rangka' => $productRangka,
                'kaki'   => $productKaki,
                'baut'   => $productBaut,
                'papan'  => $productPapan,
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tests
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Integration 14.2 — Manufacturing: BOM explosion 3 level menyertakan semua komponen.
     * Bug 1.19 fix: recursive explosion untuk semua level sub-assembly.
     * Validates: Requirements 2.19
     */
    public function test_bom_explosion_three_levels_includes_all_components(): void
    {
        ['bom' => $bomMeja, 'products' => $products] = $this->createThreeLevelBom();

        $service = app(BomExplosionService::class);
        $result  = $service->explodeBom($bomMeja, 1, $this->tenant->id, false);

        $this->assertTrue($result['success'],
            'BOM explosion harus berhasil: ' . ($result['error'] ?? 'unknown error'));

        $materials   = $result['materials'] ?? [];
        $productIds  = array_column($materials, 'product_id');

        // Level 3: Baut M8 harus ada di hasil explosion
        $this->assertContains(
            $products['baut']->id,
            $productIds,
            'BOM explosion harus menyertakan komponen level 3 (Baut M8). ' .
            'Komponen ditemukan: ' . implode(', ', array_column($materials, 'product_name'))
        );

        // Level 2: Papan Kayu (raw material di level 1) harus ada
        $this->assertContains(
            $products['papan']->id,
            $productIds,
            'BOM explosion harus menyertakan Papan Kayu.'
        );

        // Max level harus >= 2 (level 3 = index 2)
        $maxLevel = $result['max_level'] ?? 0;
        $this->assertGreaterThanOrEqual(2, $maxLevel,
            "Max level BOM explosion adalah {$maxLevel}, seharusnya >= 2 untuk BOM 3 level.");
    }

    /**
     * @test
     * Integration 14.2 — Manufacturing: kuantitas komponen level 3 dihitung dengan benar.
     * 1 Meja → 1 Rangka → 4 Kaki → 4 Baut per kaki = 16 Baut total.
     * Validates: Requirements 2.19
     */
    public function test_bom_explosion_calculates_level_3_quantities_correctly(): void
    {
        ['bom' => $bomMeja, 'products' => $products] = $this->createThreeLevelBom();

        $service = app(BomExplosionService::class);
        $result  = $service->explodeBom($bomMeja, 1, $this->tenant->id, false);

        $this->assertTrue($result['success']);

        $materials = $result['materials'] ?? [];

        // Cari Baut M8 di hasil explosion
        $bautMaterials = array_filter($materials, fn($m) => $m['product_id'] === $products['baut']->id);

        $this->assertNotEmpty($bautMaterials,
            'Baut M8 harus ada di hasil BOM explosion.');

        // Total kuantitas Baut: 1 Meja × 1 Rangka × 4 Kaki × 4 Baut = 16 baut
        $totalBaut = array_sum(array_column($bautMaterials, 'quantity'));
        $this->assertEquals(16.0, $totalBaut,
            "Total Baut M8 harus 16 (1 Meja × 1 Rangka × 4 Kaki × 4 Baut). Actual: {$totalBaut}");
    }

    /**
     * @test
     * Integration 14.2 — Manufacturing: BOM explosion untuk 2 unit meja.
     * Validates: Requirements 2.19
     */
    public function test_bom_explosion_scales_correctly_for_multiple_units(): void
    {
        ['bom' => $bomMeja, 'products' => $products] = $this->createThreeLevelBom();

        $service = app(BomExplosionService::class);
        $result  = $service->explodeBom($bomMeja, 2, $this->tenant->id, false);

        $this->assertTrue($result['success']);

        $materials = $result['materials'] ?? [];

        // Untuk 2 meja: 2 × 16 = 32 baut
        $bautMaterials = array_filter($materials, fn($m) => $m['product_id'] === $products['baut']->id);
        $totalBaut     = array_sum(array_column($bautMaterials, 'quantity'));

        $this->assertEquals(32.0, $totalBaut,
            "Untuk 2 meja, total Baut M8 harus 32. Actual: {$totalBaut}");
    }

    /**
     * @test
     * Integration 14.2 — Manufacturing: ManufacturingService explodeBom recursive.
     * Validates: Requirements 2.19
     */
    public function test_manufacturing_service_explode_bom_recursive(): void
    {
        ['products' => $products] = $this->createThreeLevelBom();

        $service = app(ManufacturingService::class);

        // Explode dari produk Meja (level 0)
        $result = $service->explodeBom($products['meja']->id, 1);

        $productIds = array_column($result, 'product_id');

        // Harus ada Rangka (level 0), Kaki (level 1), Baut (level 2), Papan (level 1)
        $this->assertContains($products['rangka']->id, $productIds,
            'Rangka Meja harus ada di hasil explosion.');
        $this->assertContains($products['kaki']->id, $productIds,
            'Kaki Meja harus ada di hasil explosion.');
        $this->assertContains($products['baut']->id, $productIds,
            'Baut M8 (level 3) harus ada di hasil explosion.');
    }

    /**
     * @test
     * Integration 14.2 — Manufacturing: BOM explosion mendeteksi circular reference.
     * Guard clause: depth > 10 melempar DomainException.
     * Validates: Requirements 2.19
     */
    public function test_bom_explosion_detects_circular_reference(): void
    {
        // Buat BOM dengan circular reference
        $productA = $this->createProduct($this->tenant->id, ['name' => 'Produk A', 'sku' => 'PROD-A']);
        $productB = $this->createProduct($this->tenant->id, ['name' => 'Produk B', 'sku' => 'PROD-B']);

        $bomA = Bom::create([
            'tenant_id'  => $this->tenant->id,
            'product_id' => $productA->id,
            'name'       => 'BOM A',
            'batch_size' => 1,
            'is_active'  => true,
        ]);

        $bomB = Bom::create([
            'tenant_id'  => $this->tenant->id,
            'product_id' => $productB->id,
            'name'       => 'BOM B',
            'batch_size' => 1,
            'is_active'  => true,
        ]);

        // A → B → A (circular)
        BomLine::create([
            'bom_id'             => $bomA->id,
            'product_id'         => $productB->id,
            'quantity_per_batch' => 1,
            'unit'               => 'pcs',
            'child_bom_id'       => $bomB->id,
        ]);
        BomLine::create([
            'bom_id'             => $bomB->id,
            'product_id'         => $productA->id,
            'quantity_per_batch' => 1,
            'unit'               => 'pcs',
            'child_bom_id'       => $bomA->id,
        ]);

        // Harus melempar exception untuk circular reference
        $this->expectException(\RuntimeException::class);

        $bomA->explode(1);
    }
}
