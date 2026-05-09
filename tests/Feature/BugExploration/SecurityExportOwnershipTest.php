<?php

namespace Tests\Feature\BugExploration;

use App\Models\ExportJob;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Bug 1.26 — Download Export Milik Tenant Lain
 *
 * Membuktikan bahwa ExportService tidak memvalidasi kepemilikan
 * file export berdasarkan tenant_id sebelum mengizinkan download.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class SecurityExportOwnershipTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenantA;

    private Tenant $tenantB;

    private User $userA;

    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = $this->createTenant();
        $this->tenantB = $this->createTenant();

        $this->userA = $this->createAdminUser($this->tenantA);

        $this->userB = $this->createAdminUser($this->tenantB);
    }

    /**
     * @test
     * Bug 1.26: ExportService.downloadExport harus memvalidasi tenant_id
     *
     * AKAN GAGAL karena ExportService tidak memvalidasi tenant_id saat download
     *
     * Validates: Requirements 1.26
     */
    public function test_export_download_validates_tenant_ownership(): void
    {
        // Arrange: Buat export job untuk tenant A
        $jobId = (string) Str::uuid();

        $exportJobA = ExportJob::create([
            'job_id' => $jobId,
            'user_id' => $this->userA->id,
            'tenant_id' => $this->tenantA->id,
            'export_type' => 'SalesReportExport',
            'filename' => 'sales-report-tenant-a.xlsx',
            'disk' => 'public',
            'status' => 'completed',
            'total_rows' => 100,
            'processed_rows' => 100,
            'file_path' => 'exports/tenant-a/sales-report.xlsx',
            'download_url' => '/storage/exports/tenant-a/sales-report.xlsx',
        ]);

        // Act: Coba download sebagai userB (tenant berbeda)
        $this->actingAs($this->userB);

        $service = app(ExportService::class);

        // ExportService.downloadExport() tidak memvalidasi tenant_id
        // Hanya mengecek job_id dan status
        $result = $service->downloadExport($jobId);

        // Assert: Seharusnya null (tidak bisa download) karena beda tenant
        // Test ini AKAN GAGAL karena ExportService tidak memvalidasi tenant_id
        $this->assertNull(
            $result,
            'Bug 1.26: ExportService mengizinkan download file export milik tenant A '.
            'oleh user dari tenant B. Tidak ada validasi tenant_id di downloadExport(). '.
            'Ini adalah kebocoran data antar tenant yang serius.'
        );
    }

    /**
     * @test
     * Bug 1.26: ExportService harus memvalidasi tenant_id di downloadExport
     *
     * AKAN GAGAL karena tidak ada validasi tenant_id
     */
    public function test_export_service_validates_tenant_id_in_download(): void
    {
        $exportServiceFile = 'app/Services/ExportService.php';

        if (! file_exists($exportServiceFile)) {
            $this->markTestSkipped('ExportService tidak ditemukan');
        }

        $content = file_get_contents($exportServiceFile);

        // Cari validasi tenant_id di downloadExport method
        // Ekstrak method downloadExport
        $methodPattern = '/public function downloadExport[^{]*\{[^}]*\}/s';
        preg_match($methodPattern, $content, $matches);
        $methodCode = $matches[0] ?? '';

        $hasTenantValidation = (
            str_contains($methodCode, 'tenant_id') &&
            (
                str_contains($methodCode, 'auth()->user()->tenant_id') ||
                str_contains($methodCode, 'Auth::user()->tenant_id') ||
                str_contains($methodCode, 'where(\'tenant_id\'')
            )
        );

        // Test ini AKAN GAGAL karena downloadExport tidak memvalidasi tenant_id
        $this->assertTrue(
            $hasTenantValidation,
            'Bug 1.26: ExportService.downloadExport() tidak memvalidasi tenant_id. '.
            'Siapapun yang mengetahui job_id bisa mendownload file export tenant lain. '.
            'Kode downloadExport yang ditemukan: '.substr($methodCode, 0, 300)
        );
    }

    /**
     * @test
     * Bug 1.26: ExportJob harus menggunakan UUID yang tidak bisa ditebak
     *
     * AKAN GAGAL jika job_id tidak menggunakan UUID
     */
    public function test_export_job_uses_unpredictable_uuid(): void
    {
        // Buat beberapa export job dan verifikasi job_id menggunakan UUID
        $this->actingAs($this->userA);

        $jobId1 = (string) Str::uuid();
        $jobId2 = (string) Str::uuid();

        // Verifikasi format UUID
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $jobId1,
            'job_id seharusnya menggunakan UUID format'
        );

        // Verifikasi bahwa dua UUID berbeda (tidak sequential)
        $this->assertNotEquals(
            $jobId1,
            $jobId2,
            'Bug 1.26: job_id seharusnya unik dan tidak bisa ditebak'
        );

        // Verifikasi bahwa ExportService menggunakan Str::uuid()
        $exportServiceFile = 'app/Services/ExportService.php';
        if (file_exists($exportServiceFile)) {
            $content = file_get_contents($exportServiceFile);
            $this->assertStringContainsString(
                'Str::uuid()',
                $content,
                'Bug 1.26: ExportService seharusnya menggunakan Str::uuid() untuk job_id'
            );
        }
    }
}
