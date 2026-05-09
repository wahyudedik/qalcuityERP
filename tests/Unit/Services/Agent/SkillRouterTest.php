<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\ErpContext;
use App\Services\Agent\SkillRouter;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Unit Tests for SkillRouter.
 *
 * Feature: erp-ai-agent
 * Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6
 */
class SkillRouterTest extends TestCase
{
    private SkillRouter $router;

    private ErpContext $baseContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new SkillRouter;

        $this->baseContext = new ErpContext(
            tenantId: 1,
            kpiSummary: [
                'revenue' => 50_000_000.0,
                'critical_stock' => 3,
                'overdue_ar' => 5_000_000.0,
                'active_employees' => 20,
            ],
            activeModules: ['accounting', 'inventory', 'hrm', 'sales'],
            accountingPeriod: 'Januari 2025',
            industrySkills: [],
            builtAt: Carbon::now(),
        );
    }

    // =========================================================================
    // detectSkills() — Bahasa Indonesia
    // =========================================================================

    public function test_detects_accounting_skill_from_indonesian_keywords(): void
    {
        $messages = [
            'Buatkan jurnal penyesuaian untuk bulan ini',
            'Tampilkan laporan neraca per hari ini',
            'Berapa total piutang yang jatuh tempo?',
            'Rekonsiliasi bank untuk bulan Januari',
            'Hitung penyusutan aset tetap',
            'Posting jurnal ke buku besar',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_ACCOUNTING,
                $skills,
                "Pesan '{$message}' harus mendeteksi skill accounting"
            );
        }
    }

    public function test_detects_inventory_skill_from_indonesian_keywords(): void
    {
        $messages = [
            'Berapa stok produk A saat ini?',
            'Lakukan penyesuaian stok untuk gudang utama',
            'Tampilkan produk dengan stok kritis',
            'Buat transfer stok antar gudang',
            'Hitung HPP dengan metode FIFO',
            'Cetak barcode untuk produk baru',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_INVENTORY,
                $skills,
                "Pesan '{$message}' harus mendeteksi skill inventory"
            );
        }
    }

    public function test_detects_hrm_skill_from_indonesian_keywords(): void
    {
        $messages = [
            'Proses penggajian karyawan bulan ini',
            'Berapa total lembur pegawai minggu ini?',
            'Tampilkan rekap absensi bulan Januari',
            'Hitung BPJS Ketenagakerjaan untuk semua karyawan',
            'Buat slip gaji untuk divisi IT',
            'Karyawan mana yang kontraknya habis bulan depan?',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_HRM,
                $skills,
                "Pesan '{$message}' harus mendeteksi skill hrm"
            );
        }
    }

    public function test_detects_sales_skill_from_indonesian_keywords(): void
    {
        $messages = [
            'Tampilkan laporan penjualan bulan ini',
            'Buat invoice untuk pelanggan PT ABC',
            'Berapa total piutang dari customer yang belum bayar?',
            'Buat penawaran harga untuk order baru',
            'Hitung komisi sales bulan ini',
            'Tampilkan pipeline CRM saat ini',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_SALES,
                $skills,
                "Pesan '{$message}' harus mendeteksi skill sales"
            );
        }
    }

    public function test_detects_project_skill_from_indonesian_keywords(): void
    {
        $messages = [
            'Tampilkan progress proyek bulan ini',
            'Buat task baru untuk milestone Q1',
            'Berapa biaya proyek yang sudah terpakai?',
            'Tampilkan gantt chart proyek aktif',
            'Update deadline untuk task pengembangan',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_PROJECT,
                $skills,
                "Pesan '{$message}' harus mendeteksi skill project"
            );
        }
    }

    // =========================================================================
    // detectSkills() — Bahasa Inggris
    // =========================================================================

    public function test_detects_accounting_skill_from_english_keywords(): void
    {
        $messages = [
            'Create a journal entry for this month',
            'Show me the balance sheet as of today',
            'What is the total accounts receivable?',
            'Run bank reconciliation for January',
            'Calculate asset depreciation',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_ACCOUNTING,
                $skills,
                "Message '{$message}' should detect accounting skill"
            );
        }
    }

    public function test_detects_inventory_skill_from_english_keywords(): void
    {
        $messages = [
            'What is the current stock level for product A?',
            'Show me all items below reorder point',
            'Create a stock adjustment for the main warehouse',
            'Calculate cost of goods sold using FIFO',
            'Show critical stock items',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_INVENTORY,
                $skills,
                "Message '{$message}' should detect inventory skill"
            );
        }
    }

    public function test_detects_hrm_skill_from_english_keywords(): void
    {
        $messages = [
            'Process payroll for this month',
            'Show employee attendance summary',
            'Which employees have expiring contracts?',
            'Calculate overtime for the IT department',
            'Generate payslip for all staff',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_HRM,
                $skills,
                "Message '{$message}' should detect hrm skill"
            );
        }
    }

    public function test_detects_sales_skill_from_english_keywords(): void
    {
        $messages = [
            'Show me the sales report for this month',
            'Create an invoice for customer ABC',
            'Show me all pending sales orders',
            'Show the CRM pipeline status',
            'Calculate sales commission for this quarter',
        ];

        foreach ($messages as $message) {
            $skills = $this->router->detectSkills($message, []);
            $this->assertContains(
                SkillRouter::SKILL_SALES,
                $skills,
                "Message '{$message}' should detect sales skill"
            );
        }
    }

    // =========================================================================
    // detectSkills() — Multiple skills dari satu pesan
    // =========================================================================

    public function test_detects_multiple_skills_from_single_message(): void
    {
        $message = 'Analisis laporan keuangan dan stok kritis bulan ini';
        $skills = $this->router->detectSkills($message, []);

        $this->assertContains(SkillRouter::SKILL_ACCOUNTING, $skills);
        $this->assertContains(SkillRouter::SKILL_INVENTORY, $skills);
    }

    public function test_detects_multiple_skills_cross_module_english(): void
    {
        $message = 'Compare sales revenue with payroll expenses this month';
        $skills = $this->router->detectSkills($message, []);

        $this->assertContains(SkillRouter::SKILL_SALES, $skills);
        $this->assertContains(SkillRouter::SKILL_HRM, $skills);
    }

    // =========================================================================
    // detectSkills() — Skill industri khusus dari activeModules
    // =========================================================================

    public function test_detects_healthcare_skill_when_module_active(): void
    {
        $skills = $this->router->detectSkills('Tampilkan data pasien hari ini', ['healthcare']);
        $this->assertContains(SkillRouter::SKILL_HEALTHCARE, $skills);
    }

    public function test_detects_manufacture_skill_when_module_active(): void
    {
        $skills = $this->router->detectSkills('Buat work order produksi', ['manufacturing']);
        $this->assertContains(SkillRouter::SKILL_MANUFACTURE, $skills);
    }

    public function test_detects_telecom_skill_when_module_active(): void
    {
        $skills = $this->router->detectSkills('Tampilkan daftar pelanggan internet', ['telecom']);
        $this->assertContains(SkillRouter::SKILL_TELECOM, $skills);
    }

    public function test_industry_skill_not_activated_when_module_inactive(): void
    {
        // Pesan tidak mengandung keyword healthcare, dan modul tidak aktif
        $skills = $this->router->detectSkills('Tampilkan laporan keuangan', []);
        $this->assertNotContains(SkillRouter::SKILL_HEALTHCARE, $skills);
        $this->assertNotContains(SkillRouter::SKILL_MANUFACTURE, $skills);
        $this->assertNotContains(SkillRouter::SKILL_TELECOM, $skills);
    }

    public function test_industry_skill_activated_even_with_unrelated_message(): void
    {
        // Modul healthcare aktif → skill healthcare selalu aktif meski pesan tidak terkait
        $skills = $this->router->detectSkills('Tampilkan laporan keuangan', ['healthcare']);
        $this->assertContains(SkillRouter::SKILL_HEALTHCARE, $skills);
    }

    // =========================================================================
    // detectSkills() — Edge cases
    // =========================================================================

    public function test_empty_message_returns_no_skills_from_keywords(): void
    {
        $skills = $this->router->detectSkills('', []);
        $this->assertEmpty($skills);
    }

    public function test_empty_message_with_active_industry_module_returns_industry_skill(): void
    {
        $skills = $this->router->detectSkills('', ['healthcare']);
        $this->assertContains(SkillRouter::SKILL_HEALTHCARE, $skills);
    }

    public function test_no_duplicate_skills_returned(): void
    {
        // Pesan mengandung banyak keyword accounting
        $message = 'Buat jurnal debit kredit untuk piutang dan hutang di buku besar neraca';
        $skills = $this->router->detectSkills($message, []);

        $uniqueSkills = array_unique($skills);
        $this->assertCount(count($uniqueSkills), $skills, 'Tidak boleh ada skill duplikat');
    }

    public function test_case_insensitive_detection(): void
    {
        $skills1 = $this->router->detectSkills('JURNAL AKUNTANSI', []);
        $skills2 = $this->router->detectSkills('jurnal akuntansi', []);
        $skills3 = $this->router->detectSkills('Jurnal Akuntansi', []);

        $this->assertContains(SkillRouter::SKILL_ACCOUNTING, $skills1);
        $this->assertContains(SkillRouter::SKILL_ACCOUNTING, $skills2);
        $this->assertContains(SkillRouter::SKILL_ACCOUNTING, $skills3);
    }

    // =========================================================================
    // buildSkillPrompt() — Terminologi per domain
    // =========================================================================

    public function test_accounting_prompt_contains_required_terminology(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_ACCOUNTING], $this->baseContext);

        // Terminologi akuntansi standar Indonesia (Requirements 8.3)
        $this->assertStringContainsStringIgnoringCase('debit', $prompt);
        $this->assertStringContainsStringIgnoringCase('kredit', $prompt);
        $this->assertStringContainsStringIgnoringCase('neraca', $prompt);
        $this->assertStringContainsStringIgnoringCase('laba rugi', $prompt);
        $this->assertStringContainsStringIgnoringCase('arus kas', $prompt);
        $this->assertStringContainsStringIgnoringCase('jurnal', $prompt);
        $this->assertStringContainsStringIgnoringCase('buku besar', $prompt);
        $this->assertStringContainsStringIgnoringCase('ppn', $prompt);
        $this->assertStringContainsStringIgnoringCase('pph', $prompt);
    }

    public function test_accounting_prompt_includes_active_period(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_ACCOUNTING], $this->baseContext);
        $this->assertStringContainsString('Januari 2025', $prompt);
    }

    public function test_hrm_prompt_contains_indonesian_labor_regulations(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_HRM], $this->baseContext);

        // Regulasi ketenagakerjaan Indonesia (Requirements 8.4)
        $this->assertStringContainsStringIgnoringCase('umr', $prompt);
        $this->assertStringContainsStringIgnoringCase('bpjs', $prompt);
        $this->assertStringContainsStringIgnoringCase('pph 21', $prompt);
        $this->assertStringContainsStringIgnoringCase('ptkp', $prompt);
        $this->assertStringContainsStringIgnoringCase('lembur', $prompt);
    }

    public function test_inventory_prompt_contains_fifo_by_default(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_INVENTORY], $this->baseContext);

        // Default costing method FIFO (Requirements 8.5)
        $this->assertStringContainsStringIgnoringCase('fifo', $prompt);
    }

    public function test_inventory_prompt_uses_average_costing_when_specified(): void
    {
        $contextWithAverage = new ErpContext(
            tenantId: 1,
            kpiSummary: array_merge($this->baseContext->kpiSummary, ['costing_method' => 'average']),
            activeModules: $this->baseContext->activeModules,
            accountingPeriod: $this->baseContext->accountingPeriod,
            industrySkills: [],
            builtAt: Carbon::now(),
        );

        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_INVENTORY], $contextWithAverage);
        $this->assertStringContainsStringIgnoringCase('average', $prompt);
    }

    public function test_inventory_prompt_contains_required_terminology(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_INVENTORY], $this->baseContext);

        $this->assertStringContainsStringIgnoringCase('stok', $prompt);
        $this->assertStringContainsStringIgnoringCase('hpp', $prompt);
        $this->assertStringContainsStringIgnoringCase('gudang', $prompt);
    }

    public function test_sales_prompt_contains_required_terminology(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_SALES], $this->baseContext);

        $this->assertStringContainsStringIgnoringCase('invoice', $prompt);
        $this->assertStringContainsStringIgnoringCase('piutang', $prompt);
        $this->assertStringContainsStringIgnoringCase('crm', $prompt);
        $this->assertStringContainsStringIgnoringCase('komisi', $prompt);
    }

    public function test_project_prompt_contains_required_terminology(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_PROJECT], $this->baseContext);

        $this->assertStringContainsStringIgnoringCase('milestone', $prompt);
        $this->assertStringContainsStringIgnoringCase('gantt', $prompt);
        $this->assertStringContainsStringIgnoringCase('wbs', $prompt);
    }

    public function test_healthcare_prompt_contains_required_terminology(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_HEALTHCARE], $this->baseContext);

        // Requirements 8.6
        $this->assertStringContainsStringIgnoringCase('bpjs', $prompt);
        $this->assertStringContainsStringIgnoringCase('rekam medis', $prompt);
        $this->assertStringContainsStringIgnoringCase('farmasi', $prompt);
        $this->assertStringContainsStringIgnoringCase('icd', $prompt);
    }

    public function test_manufacture_prompt_contains_required_terminology(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_MANUFACTURE], $this->baseContext);

        // Requirements 8.6
        $this->assertStringContainsStringIgnoringCase('bom', $prompt);
        $this->assertStringContainsStringIgnoringCase('work order', $prompt);
        $this->assertStringContainsStringIgnoringCase('mrp', $prompt);
        $this->assertStringContainsStringIgnoringCase('oee', $prompt);
    }

    public function test_telecom_prompt_contains_required_terminology(): void
    {
        $prompt = $this->router->buildSkillPrompt([SkillRouter::SKILL_TELECOM], $this->baseContext);

        // Requirements 8.6
        $this->assertStringContainsStringIgnoringCase('bandwidth', $prompt);
        $this->assertStringContainsStringIgnoringCase('radius', $prompt);
        $this->assertStringContainsStringIgnoringCase('sla', $prompt);
        $this->assertStringContainsStringIgnoringCase('arpu', $prompt);
    }

    // =========================================================================
    // buildSkillPrompt() — Multiple skills
    // =========================================================================

    public function test_multiple_skills_produce_combined_prompt(): void
    {
        $prompt = $this->router->buildSkillPrompt(
            [SkillRouter::SKILL_ACCOUNTING, SkillRouter::SKILL_INVENTORY],
            $this->baseContext
        );

        // Harus mengandung terminologi dari kedua skill
        $this->assertStringContainsStringIgnoringCase('debit', $prompt);
        $this->assertStringContainsStringIgnoringCase('stok', $prompt);
    }

    public function test_empty_skills_returns_empty_string(): void
    {
        $prompt = $this->router->buildSkillPrompt([], $this->baseContext);
        $this->assertSame('', $prompt);
    }

    public function test_unknown_skill_is_ignored_gracefully(): void
    {
        $prompt = $this->router->buildSkillPrompt(['unknown_skill_xyz'], $this->baseContext);
        $this->assertSame('', $prompt);
    }

    // =========================================================================
    // Integration: detectSkills() → buildSkillPrompt()
    // =========================================================================

    public function test_detect_and_build_flow_for_accounting_message(): void
    {
        $message = 'Buatkan jurnal penyesuaian untuk piutang yang jatuh tempo';
        $skills = $this->router->detectSkills($message, []);
        $prompt = $this->router->buildSkillPrompt($skills, $this->baseContext);

        $this->assertNotEmpty($skills);
        $this->assertNotEmpty($prompt);
        $this->assertStringContainsStringIgnoringCase('debit', $prompt);
    }

    public function test_detect_and_build_flow_for_hrm_message(): void
    {
        $message = 'Hitung gaji karyawan dengan potongan BPJS dan PPh 21';
        $skills = $this->router->detectSkills($message, []);
        $prompt = $this->router->buildSkillPrompt($skills, $this->baseContext);

        $this->assertContains(SkillRouter::SKILL_HRM, $skills);
        $this->assertStringContainsStringIgnoringCase('bpjs', $prompt);
        $this->assertStringContainsStringIgnoringCase('pph 21', $prompt);
    }

    public function test_detect_and_build_flow_with_industry_module(): void
    {
        $message = 'Tampilkan laporan keuangan';
        $skills = $this->router->detectSkills($message, ['healthcare', 'accounting']);
        $prompt = $this->router->buildSkillPrompt($skills, $this->baseContext);

        $this->assertContains(SkillRouter::SKILL_HEALTHCARE, $skills);
        $this->assertStringContainsStringIgnoringCase('rekam medis', $prompt);
    }
}
