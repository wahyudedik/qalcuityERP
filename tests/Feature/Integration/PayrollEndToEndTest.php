<?php

namespace Tests\Feature\Integration;

use App\Models\AccountingPeriod;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollItemComponent;
use App\Models\PayrollRun;
use App\Services\PayrollCalculationService;
use App\Services\PayrollGlService;
use Tests\TestCase;

/**
 * Integration Test 14.2 — Payroll End-to-End
 *
 * Verifikasi alur lengkap:
 * 1. Buat payroll dengan komponen null
 * 2. Hitung formula — tidak boleh error (Bug 1.17 fix)
 * 3. Buat jurnal GL ke periode open (Bug 1.14 preservation)
 *
 * Validates: Requirements 2.17, 3.8
 */
class PayrollEndToEndTest extends TestCase
{
    private $tenant;

    private $user;

    private AccountingPeriod $openPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
        $this->seedCoa($this->tenant->id);
        $this->actingAs($this->user);

        // Buat periode open untuk bulan ini
        $this->openPeriod = AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Periode '.now()->format('Y-m'),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function createEmployee(): Employee
    {
        return Employee::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Karyawan Test',
            'employee_id' => 'EMP-'.uniqid(),
            'status' => 'active',
            'salary' => 5000000,
        ]);
    }

    private function createPayrollRunWithNullComponent(Employee $employee): PayrollRun
    {
        $run = PayrollRun::create([
            'tenant_id' => $this->tenant->id,
            'period' => now()->format('Y-m'),
            'status' => 'processed',
            'total_gross' => 5000000,
            'total_deductions' => 500000,
            'total_net' => 4500000,
            'processed_by' => $this->user->id,
            'processed_at' => now(),
        ]);

        PayrollItem::create([
            'tenant_id' => $this->tenant->id,
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'base_salary' => 5000000,
            'working_days' => 26,
            'present_days' => 26,
            'absent_days' => 0,
            'late_days' => 0,
            'allowances' => 0,
            'overtime_pay' => 0,
            'deduction_absent' => 0,
            'deduction_late' => 0,
            'deduction_other' => 0,
            'gross_salary' => 5000000,
            'bpjs_employee' => 150000,
            'tax_pph21' => 350000,
            'net_salary' => 4500000,
            'status' => 'pending',
        ]);

        // Note: PayrollItemComponent.amount is NOT NULL in DB (default 0).
        // The null-component scenario is tested at the service level directly
        // (see test_payroll_formula_with_null_component_evaluates_without_error).

        return $run;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tests
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * Integration 14.2 — Payroll End-to-End: formula dengan komponen null tidak error.
     * Bug 1.17 fix: null diganti 0.0 sebelum evaluasi.
     * Validates: Requirements 2.17
     */
    public function test_payroll_formula_with_null_component_evaluates_without_error(): void
    {
        $service = app(PayrollCalculationService::class);

        // Komponen dengan nilai null — seharusnya diganti 0.0
        $components = [
            'basic_salary' => 5000000.0,
            'allowance' => null,   // null — Bug 1.17 case
            'deduction' => 500000.0,
        ];

        // Tidak boleh melempar exception
        $result = $service->evaluateFormula('basic_salary + allowance - deduction', $components);

        $this->assertEquals(4500000.0, $result,
            'Formula dengan null component harus menghasilkan nilai yang benar (null → 0).');
    }

    /**
     * @test
     * Integration 14.2 — Payroll End-to-End: semua komponen null menghasilkan 0.
     * Validates: Requirements 2.17
     */
    public function test_payroll_formula_with_all_null_components_returns_zero(): void
    {
        $service = app(PayrollCalculationService::class);

        $components = [
            'basic_salary' => null,
            'allowance' => null,
        ];

        $result = $service->evaluateFormula('basic_salary + allowance', $components);

        $this->assertEquals(0.0, $result,
            'Formula dengan semua komponen null harus menghasilkan 0.');
    }

    /**
     * @test
     * Integration 14.2 — Payroll End-to-End: jurnal GL berhasil dibuat ke periode open.
     * Preservation: jurnal berhasil dibuat untuk periode open (Bug 1.14 preservation).
     * Validates: Requirements 2.17, 3.8
     */
    public function test_payroll_end_to_end_creates_journal_to_open_period(): void
    {
        $employee = $this->createEmployee();
        $run = $this->createPayrollRunWithNullComponent($employee);

        $glService = app(PayrollGlService::class);

        // Tidak boleh melempar exception
        $journal = $glService->createJournal($run, $this->user->id);

        // Jurnal harus terbuat
        $this->assertNotNull($journal, 'Jurnal GL harus terbuat.');
        $this->assertEquals('posted', $journal->status,
            'Jurnal harus dalam status posted.');

        // Jurnal harus terhubung ke periode open
        $this->assertNotNull($journal->period_id,
            'Jurnal harus memiliki period_id.');
        $this->assertEquals($this->openPeriod->id, $journal->period_id,
            'Jurnal harus terhubung ke periode open yang benar.');

        // Jurnal harus balance
        $this->assertTrue($journal->isBalanced(),
            'Jurnal GL harus balance (total debit = total credit).');

        // Payroll run harus terhubung ke jurnal
        $run->refresh();
        $this->assertEquals($journal->id, $run->journal_entry_id,
            'PayrollRun harus terhubung ke jurnal yang dibuat.');
    }

    /**
     * @test
     * Integration 14.2 — Payroll End-to-End: jurnal ditolak untuk periode locked.
     * Bug 1.14 fix: tidak boleh membuat jurnal ke periode locked.
     * Validates: Requirements 2.14
     */
    public function test_payroll_journal_rejected_for_locked_period(): void
    {
        // Ubah periode menjadi locked
        $this->openPeriod->update(['status' => 'locked']);

        $employee = $this->createEmployee();
        $run = $this->createPayrollRunWithNullComponent($employee);

        $glService = app(PayrollGlService::class);

        $this->expectException(\RuntimeException::class);

        $glService->createJournal($run, $this->user->id);
    }

    /**
     * @test
     * Integration 14.2 — Payroll End-to-End: formula valid tanpa komponen null tetap berfungsi.
     * Preservation: kalkulasi normal tidak terganggu oleh fix Bug 1.17.
     * Validates: Requirements 3.8
     */
    public function test_payroll_formula_without_null_components_works_correctly(): void
    {
        $service = app(PayrollCalculationService::class);

        $components = [
            'basic_salary' => 5000000.0,
            'allowance' => 1000000.0,
            'deduction' => 500000.0,
        ];

        $result = $service->evaluateFormula('basic_salary + allowance - deduction', $components);

        $this->assertEquals(5500000.0, $result,
            'Formula tanpa null component harus menghasilkan nilai yang benar.');
    }
}
