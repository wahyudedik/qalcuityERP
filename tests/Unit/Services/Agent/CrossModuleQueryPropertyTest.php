<?php

namespace Tests\Unit\Services\Agent;

use App\Models\Attendance;
use App\Models\CrmLead;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Project;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Services\Agent\CrossModuleQueryService;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for CrossModuleQueryService.
 *
 * Feature: erp-ai-agent
 *
 * Property 7: Partial Cross-Module Results
 *
 * Untuk kombinasi modul di mana sebagian tidak aktif, hasil selalu parsial
 * (bukan error total) dengan daftar modul tidak tersedia.
 *
 * Validates: Requirements 3.5
 */
class CrossModuleQueryPropertyTest extends TestCase
{
    use TestTrait;

    // Semua kombinasi query yang tersedia
    private array $queryMethods = [
        'queryAkuntansiInventory',
        'queryAkuntansiHrm',
        'queryPenjualanCrmInventory',
        'queryHrmPayrollAbsensi',
        'queryProjectKeuangan',
    ];

    // Semua modul yang digunakan dalam cross-module queries
    private array $allModules = [
        'accounting', 'inventory', 'hrm', 'sales', 'crm', 'payroll', 'project',
    ];

    // =========================================================================
    // Property 7: Partial Cross-Module Results
    //
    // Untuk kombinasi modul di mana sebagian tidak aktif, hasil selalu parsial
    // (bukan error total) dengan daftar modul tidak tersedia.
    //
    // Feature: erp-ai-agent, Property 7: Partial Cross-Module Results
    // Validates: Requirements 3.5
    // =========================================================================

    #[ErisRepeat(repeat: 30)]
    public function testPartialResultsWhenSomeModulesInactive(): void
    {
        $this->forAll(
            // Pilih query method secara acak
            Generators::elements(...$this->queryMethods),
            // Pilih subset modul yang aktif (0 hingga semua modul)
            Generators::map(
                function (int $bitmask) {
                    $selected = [];
                    foreach ($this->allModules as $i => $module) {
                        if ($bitmask & (1 << $i)) {
                            $selected[] = $module;
                        }
                    }
                    return $selected;
                },
                Generators::choose(0, (1 << count($this->allModules)) - 1)
            )
        )->then(function (string $method, array $activeModules) {
            $tenant  = $this->createTenant();
            $service = new CrossModuleQueryService($tenant->id, $activeModules);

            // Eksekusi query — tidak boleh throw exception apapun
            $result = $service->$method([]);

            // ── Assert: selalu mengembalikan array (tidak pernah null/exception) ──
            $this->assertIsArray($result, "{$method}() harus selalu mengembalikan array");

            // ── Assert: status selalu 'success' (tidak pernah 'error' total) ──
            $this->assertArrayHasKey('status', $result, "{$method}() harus mengandung key 'status'");
            $this->assertSame(
                'success',
                $result['status'],
                "{$method}() harus mengembalikan status 'success' bahkan jika modul tidak aktif"
            );

            // ── Assert: selalu mengandung key 'data' ──
            $this->assertArrayHasKey('data', $result, "{$method}() harus mengandung key 'data'");
            $this->assertIsArray($result['data'], "key 'data' harus berupa array");

            // ── Assert: selalu mengandung key 'unavailable_modules' ──
            $this->assertArrayHasKey(
                'unavailable_modules',
                $result,
                "{$method}() harus mengandung key 'unavailable_modules'"
            );
            $this->assertIsArray(
                $result['unavailable_modules'],
                "key 'unavailable_modules' harus berupa array"
            );

            // ── Assert: jika ada modul tidak aktif, 'partial' = true dan ada 'message' ──
            if (!empty($result['unavailable_modules'])) {
                $this->assertTrue(
                    $result['partial'] ?? false,
                    "Jika ada modul tidak tersedia, 'partial' harus true"
                );
                $this->assertArrayHasKey(
                    'message',
                    $result,
                    "Jika ada modul tidak tersedia, harus ada 'message' informatif"
                );
                $this->assertNotEmpty(
                    $result['message'],
                    "Pesan modul tidak tersedia tidak boleh kosong"
                );
            }

            // ── Assert: 'correlation' selalu ada (bisa kosong) ──
            $this->assertArrayHasKey(
                'correlation',
                $result,
                "{$method}() harus mengandung key 'correlation'"
            );
            $this->assertIsArray($result['correlation'], "key 'correlation' harus berupa array");
        });
    }

    // =========================================================================
    // Property 7 (edge case): Semua modul tidak aktif → hasil parsial kosong
    //
    // Bahkan jika semua modul tidak aktif, tidak boleh ada error total.
    //
    // Feature: erp-ai-agent, Property 7: Partial Cross-Module Results
    // Validates: Requirements 3.5
    // =========================================================================

    public function testAllModulesInactiveReturnsPartialNotError(): void
    {
        $tenant = $this->createTenant();

        foreach ($this->queryMethods as $method) {
            // Berikan modul yang tidak relevan sama sekali
            $service = new CrossModuleQueryService($tenant->id, ['nonexistent_module']);
            $result  = $service->$method([]);

            $this->assertSame(
                'success',
                $result['status'],
                "{$method}() harus mengembalikan status 'success' bahkan jika semua modul tidak aktif"
            );
            $this->assertIsArray($result['data'], "{$method}() harus mengembalikan 'data' array");
            $this->assertNotEmpty(
                $result['unavailable_modules'],
                "{$method}() harus mencantumkan modul yang tidak tersedia"
            );
            $this->assertTrue(
                $result['partial'] ?? false,
                "{$method}() harus menandai hasil sebagai parsial"
            );
        }
    }

    // =========================================================================
    // Property 7 (edge case): Semua modul aktif → tidak ada unavailable_modules
    //
    // Jika semua modul aktif, unavailable_modules harus kosong.
    //
    // Feature: erp-ai-agent, Property 7: Partial Cross-Module Results
    // Validates: Requirements 3.5
    // =========================================================================

    public function testAllModulesActiveReturnsNoUnavailableModules(): void
    {
        $tenant = $this->createTenant();

        foreach ($this->queryMethods as $method) {
            // Berikan semua modul sebagai aktif
            $service = new CrossModuleQueryService($tenant->id, $this->allModules);
            $result  = $service->$method([]);

            $this->assertSame('success', $result['status']);
            $this->assertEmpty(
                $result['unavailable_modules'],
                "{$method}() tidak boleh ada unavailable_modules jika semua modul aktif"
            );
            $this->assertArrayNotHasKey(
                'partial',
                $result,
                "{$method}() tidak boleh ada key 'partial' jika semua modul aktif"
            );
        }
    }

    // =========================================================================
    // Property 7 (edge case): activeModules kosong → semua modul dianggap aktif
    //
    // Jika activeModules tidak diisi, semua modul dianggap aktif.
    //
    // Feature: erp-ai-agent, Property 7: Partial Cross-Module Results
    // Validates: Requirements 3.5
    // =========================================================================

    public function testEmptyActiveModulesAllowsAllModules(): void
    {
        $tenant = $this->createTenant();

        foreach ($this->queryMethods as $method) {
            $service = new CrossModuleQueryService($tenant->id, []);
            $result  = $service->$method([]);

            $this->assertSame('success', $result['status']);
            $this->assertEmpty(
                $result['unavailable_modules'],
                "{$method}() dengan activeModules kosong tidak boleh ada unavailable_modules"
            );
        }
    }

    // =========================================================================
    // Property 7 (partial): Hanya sebagian modul aktif → data parsial + daftar unavailable
    //
    // Jika hanya satu modul aktif dari kombinasi yang memerlukan dua modul,
    // hasil harus parsial dengan data dari modul yang aktif saja.
    //
    // Feature: erp-ai-agent, Property 7: Partial Cross-Module Results
    // Validates: Requirements 3.5
    // =========================================================================

    public function testPartialModulesReturnPartialDataWithUnavailableList(): void
    {
        $tenant = $this->createTenant();

        // Hanya accounting aktif, inventory tidak aktif
        $service = new CrossModuleQueryService($tenant->id, ['accounting']);
        $result  = $service->queryAkuntansiInventory([]);

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('akuntansi', $result['data'],
            'Data akuntansi harus ada karena modul aktif');
        $this->assertArrayNotHasKey('inventory', $result['data'],
            'Data inventory tidak boleh ada karena modul tidak aktif');
        $this->assertContains('inventory', $result['unavailable_modules'],
            "'inventory' harus ada di unavailable_modules");
        $this->assertTrue($result['partial'] ?? false);

        // Hanya inventory aktif, accounting tidak aktif
        $service2 = new CrossModuleQueryService($tenant->id, ['inventory']);
        $result2  = $service2->queryAkuntansiInventory([]);

        $this->assertSame('success', $result2['status']);
        $this->assertArrayHasKey('inventory', $result2['data'],
            'Data inventory harus ada karena modul aktif');
        $this->assertArrayNotHasKey('akuntansi', $result2['data'],
            'Data akuntansi tidak boleh ada karena modul tidak aktif');
        $this->assertContains('accounting', $result2['unavailable_modules'],
            "'accounting' harus ada di unavailable_modules");
    }
}
