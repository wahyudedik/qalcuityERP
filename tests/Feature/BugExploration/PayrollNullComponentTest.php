<?php

namespace Tests\Feature\BugExploration;

use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollItemComponent;
use App\Models\PayrollRun;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PayrollGlService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bug 1.17 — Payroll Formula Tidak Null-Safe
 *
 * Membuktikan bahwa kalkulasi payroll tidak menangani nilai null
 * pada komponen opsional, menyebabkan error kalkulasi.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class PayrollNullComponentTest extends TestCase
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
     * Bug 1.17: PayrollGlService harus menangani komponen null tanpa error
     *
     * AKAN GAGAL karena PayrollGlService tidak menangani null component
     *
     * Validates: Requirements 1.17
     */
    public function test_payroll_with_null_component_does_not_throw_error(): void
    {
        // Arrange: Buat payroll run dengan item yang memiliki komponen null
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Employee',
            'employee_id' => 'EMP-' . uniqid(),
            'status' => 'active',
            'salary' => 5000000,
            'join_date' => now()->subYear(),
        ]);

        $payrollRun = PayrollRun::create([
            'tenant_id' => $this->tenant->id,
            'period' => '2025-01',
            'status' => 'processed',
            'processed_by' => $this->user->id,
            'processed_at' => now(),
        ]);

        $payrollItem = PayrollItem::create([
            'tenant_id' => $this->tenant->id,
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $employee->id,
            'basic_salary' => 5000000,
            'gross_salary' => 5000000,
            'net_salary' => 5000000,
            'total_deductions' => 0,
            'total_allowances' => 0,
        ]);

        // Buat komponen dengan nilai null (komponen opsional yang tidak diisi)
        // Gunakan DB::statement untuk bypass constraint karena ini mensimulasikan bug
        \Illuminate\Support\Facades\DB::statement(
            "INSERT INTO payroll_item_components (payroll_item_id, component_name, component_type, amount, created_at, updated_at) VALUES (?, ?, ?, NULL, NOW(), NOW())",
            [$payrollItem->id, 'Tunjangan Khusus', 'allowance']
        );

        // Act: Coba posting payroll GL
        $glService = app(PayrollGlService::class);

        $threwError = false;
        $errorMessage = '';

        try {
            $result = $glService->postPayrollJournal($payrollRun);
        } catch (\Throwable $e) {
            $threwError = true;
            $errorMessage = $e->getMessage();
        }

        // Assert: Tidak boleh ada error karena komponen null
        // Test ini AKAN GAGAL karena PayrollGlService tidak menangani null
        $this->assertFalse(
            $threwError,
            "Bug 1.17: PayrollGlService melempar error saat ada komponen null: {$errorMessage}. " .
            "Komponen null seharusnya diperlakukan sebagai 0 (default value)."
        );
    }

    /**
     * @test
     * Bug 1.17: Verifikasi bahwa PayrollGlService menggunakan null coalescing untuk komponen
     *
     * AKAN GAGAL jika tidak ada null coalescing
     */
    public function test_payroll_gl_service_uses_null_coalescing(): void
    {
        $payrollGlFile = 'app/Services/PayrollGlService.php';

        if (!file_exists($payrollGlFile)) {
            $this->markTestSkipped("PayrollGlService tidak ditemukan");
        }

        $content = file_get_contents($payrollGlFile);

        // Cari null coalescing operator untuk komponen
        $hasNullCoalescing = (
            str_contains($content, '?? 0') ||
            str_contains($content, '?? 0.0') ||
            str_contains($content, 'null_safe') ||
            str_contains($content, 'array_map') && str_contains($content, '?? 0')
        );

        // Test ini AKAN GAGAL jika tidak ada null coalescing
        $this->assertTrue(
            $hasNullCoalescing,
            "Bug 1.17: PayrollGlService tidak menggunakan null coalescing (?? 0) " .
            "untuk menangani komponen null. Ini akan menyebabkan error kalkulasi " .
            "saat ada komponen opsional yang tidak diisi."
        );
    }
}
