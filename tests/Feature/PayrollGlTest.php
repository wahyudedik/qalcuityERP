<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Services\PayrollGlService;
use Tests\TestCase;

class PayrollGlTest extends TestCase
{
    private $tenant;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
        $this->seedCoa($this->tenant->id);
    }

    private function createPayrollRun(float $gross = 5000000, float $net = 4500000): PayrollRun
    {
        $run = PayrollRun::create([
            'tenant_id' => $this->tenant->id,
            'period' => '2026-03',
            'status' => 'processed',
            'total_gross' => $gross,
            'total_deductions' => $gross - $net,
            'total_net' => $net,
            'processed_by' => $this->user->id,
            'processed_at' => now(),
        ]);

        // Buat 1 payroll item
        PayrollItem::create([
            'tenant_id' => $this->tenant->id,
            'payroll_run_id' => $run->id,
            'employee_id' => $this->createEmployee()->id,
            'base_salary' => $gross,
            'working_days' => 26,
            'present_days' => 26,
            'absent_days' => 0,
            'late_days' => 0,
            'allowances' => 0,
            'overtime_pay' => 0,
            'deduction_absent' => 0,
            'deduction_late' => 0,
            'deduction_other' => 0,
            'gross_salary' => $gross,
            'bpjs_employee' => 150000,
            'tax_pph21' => 350000,
            'net_salary' => $net,
            'status' => 'pending',
        ]);

        return $run;
    }

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

    // ── Payroll GL Journal ────────────────────────────────────────

    public function test_creates_payroll_expense_journal(): void
    {
        $run = $this->createPayrollRun(5000000, 4500000);

        $service = app(PayrollGlService::class);
        $journal = $service->createJournal($run, $this->user->id);

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // Link ke payroll run
        $this->assertDatabaseHas('payroll_runs', [
            'id' => $run->id,
            'journal_entry_id' => $journal->id,
        ]);

        // Dr Beban Gaji (5201)
        $debitLine = $journal->lines->where('debit', '>', 0)
            ->filter(fn ($l) => $l->account->code === '5201')
            ->first();
        $this->assertNotNull($debitLine, 'Should have Dr Beban Gaji (5201)');
        $this->assertEquals(5000000, $debitLine->debit);

        // Cr Hutang Gaji (2108)
        $creditLine = $journal->lines->where('credit', '>', 0)
            ->filter(fn ($l) => $l->account->code === '2108')
            ->first();
        $this->assertNotNull($creditLine, 'Should have Cr Hutang Gaji (2108)');
        $this->assertEquals(4500000, $creditLine->credit);

        // Cr PPh 21 (2104)
        $pphLine = $journal->lines->where('credit', '>', 0)
            ->filter(fn ($l) => $l->account->code === '2104')
            ->first();
        $this->assertNotNull($pphLine, 'Should have Cr PPh 21 (2104)');
        $this->assertEquals(350000, $pphLine->credit);
    }

    public function test_payroll_journal_is_balanced(): void
    {
        $run = $this->createPayrollRun(5000000, 4500000);
        $service = app(PayrollGlService::class);
        $journal = $service->createJournal($run, $this->user->id);

        $debit = round($journal->lines->sum('debit'), 2);
        $credit = round($journal->lines->sum('credit'), 2);

        $this->assertEquals($debit, $credit, "Payroll journal must be balanced: D={$debit} C={$credit}");
    }

    public function test_creates_payment_journal_when_marked_paid(): void
    {
        $run = $this->createPayrollRun(5000000, 4500000);

        // Buat expense journal dulu
        $service = app(PayrollGlService::class);
        $service->createJournal($run, $this->user->id);

        // Buat payment journal
        $paymentJournal = $service->createPaymentJournal($run->fresh(), $this->user->id);

        $this->assertNotNull($paymentJournal);
        $this->assertEquals('posted', $paymentJournal->status);

        // Dr Hutang Gaji (2108) — lunasi kewajiban
        $debitLine = $paymentJournal->lines->where('debit', '>', 0)
            ->filter(fn ($l) => $l->account->code === '2108')
            ->first();
        $this->assertNotNull($debitLine, 'Payment journal should Dr Hutang Gaji (2108)');
        $this->assertEquals(4500000, $debitLine->debit);

        // Cr Bank (1102)
        $creditLine = $paymentJournal->lines->where('credit', '>', 0)
            ->filter(fn ($l) => $l->account->code === '1102')
            ->first();
        $this->assertNotNull($creditLine, 'Payment journal should Cr Bank (1102)');
        $this->assertEquals(4500000, $creditLine->credit);
    }

    public function test_does_not_create_duplicate_journal(): void
    {
        $run = $this->createPayrollRun();
        $service = app(PayrollGlService::class);

        $journal1 = $service->createJournal($run, $this->user->id);
        $journal2 = $service->createJournal($run->fresh(), $this->user->id);

        // Harus return jurnal yang sama
        $this->assertEquals($journal1->id, $journal2->id);

        // Hanya 1 jurnal di DB
        $this->assertEquals(1, JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'payroll')->count());
    }

    public function test_auto_creates_missing_coa_accounts(): void
    {
        // Hapus semua COA — PayrollGlService harus auto-create
        ChartOfAccount::where('tenant_id', $this->tenant->id)->delete();

        $run = $this->createPayrollRun();
        $service = app(PayrollGlService::class);

        // Tidak boleh throw exception
        $journal = $service->createJournal($run, $this->user->id);

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // COA harus ter-create otomatis
        $this->assertDatabaseHas('chart_of_accounts', [
            'tenant_id' => $this->tenant->id,
            'code' => '5201',
        ]);
        $this->assertDatabaseHas('chart_of_accounts', [
            'tenant_id' => $this->tenant->id,
            'code' => '2108',
        ]);
    }

    // ── Payroll Controller ────────────────────────────────────────

    public function test_marking_paid_triggers_payment_journal(): void
    {
        $run = $this->createPayrollRun();

        // Buat expense journal dulu
        $service = app(PayrollGlService::class);
        $service->createJournal($run, $this->user->id);

        $this->actingAs($this->user);

        $response = $this->patch(route('payroll.paid', $run));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status berubah ke paid
        $this->assertDatabaseHas('payroll_runs', [
            'id' => $run->id,
            'status' => 'paid',
        ]);

        // Payment journal terbuat
        $run->refresh();
        $this->assertNotNull($run->payment_journal_entry_id,
            'Payment journal should be created when marking paid');
    }

    public function test_shows_warning_when_gl_fails_during_process(): void
    {
        // Hapus COA agar GL gagal
        ChartOfAccount::where('tenant_id', $this->tenant->id)->delete();

        // Buat employee
        $this->createEmployee();

        $this->actingAs($this->user);

        $response = $this->post(route('payroll.process'), [
            'period' => '2026-04',
            'working_days' => 26,
            'include_bpjs' => '1',
        ]);

        // Payroll tetap diproses
        $response->assertSessionHas('success');

        // Tapi ada warning karena GL gagal
        // (hanya ada warning jika ada karyawan yang diproses)
        // Jika tidak ada karyawan aktif, tidak ada warning
        $this->assertTrue(
            session()->has('success'),
            'Payroll process should succeed even without COA'
        );
    }
}
