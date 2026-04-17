<?php

namespace Tests\Feature\BugExploration;

use App\Jobs\Telecom\SyncHotspotUsersJob;
use App\Models\NetworkDevice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Telecom\RouterIntegrationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Bug 1.21 — MikroTik Sync Tanpa Graceful Timeout Handling
 *
 * Membuktikan bahwa SyncHotspotUsersJob tidak menangani ConnectionException
 * dengan graceful fallback dan exponential backoff.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 *
 * CATATAN: Berdasarkan kode aktual, SyncHotspotUsersJob memiliki $tries = 2
 * tapi tidak ada backoff array dan tidak ada graceful ConnectionException handling.
 */
class TelecomMikrotikTimeoutTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
    }

    /**
     * @test
     * Bug 1.21: SyncHotspotUsersJob harus memiliki exponential backoff
     *
     * AKAN GAGAL karena job tidak memiliki backoff array
     *
     * Validates: Requirements 1.21
     */
    public function test_sync_hotspot_job_has_exponential_backoff(): void
    {
        $job = new SyncHotspotUsersJob();

        // Assert: Job harus memiliki backoff property
        $hasBackoff = property_exists($job, 'backoff');

        // Test ini AKAN GAGAL karena SyncHotspotUsersJob tidak memiliki backoff property
        $this->assertTrue(
            $hasBackoff,
            "Bug 1.21: SyncHotspotUsersJob tidak memiliki property 'backoff' untuk " .
            "exponential backoff. Job akan retry dengan interval yang sama, " .
            "bukan dengan interval yang meningkat."
        );

        if ($hasBackoff) {
            // Assert: backoff harus berupa array dengan nilai yang meningkat
            $this->assertIsArray(
                $job->backoff,
                "Bug 1.21: Property 'backoff' seharusnya berupa array [30, 60, 120, 300, 600]"
            );

            $this->assertGreaterThan(
                1,
                count($job->backoff),
                "Bug 1.21: Backoff array seharusnya memiliki lebih dari 1 nilai untuk exponential backoff"
            );
        }
    }

    /**
     * @test
     * Bug 1.21: SyncHotspotUsersJob harus memiliki tries yang cukup
     *
     * AKAN GAGAL jika tries terlalu sedikit
     */
    public function test_sync_hotspot_job_has_sufficient_tries(): void
    {
        $job = new SyncHotspotUsersJob();

        // Assert: Job harus memiliki tries >= 3 untuk graceful retry
        // Berdasarkan kode aktual: $tries = 2 (terlalu sedikit)
        $this->assertGreaterThanOrEqual(
            3,
            $job->tries,
            "Bug 1.21: SyncHotspotUsersJob memiliki tries = {$job->tries}, " .
            "seharusnya >= 3 untuk graceful retry saat MikroTik timeout. " .
            "Dengan tries = 2, job akan fail permanen setelah 2 kali gagal."
        );
    }

    /**
     * @test
     * Bug 1.21: SyncHotspotUsersJob harus menangani ConnectionException dengan release
     *
     * AKAN GAGAL karena job tidak menangani ConnectionException dengan $this->release()
     */
    public function test_sync_hotspot_job_handles_connection_exception_with_release(): void
    {
        $jobFile = 'app/Jobs/Telecom/SyncHotspotUsersJob.php';

        if (!file_exists($jobFile)) {
            $this->markTestSkipped("SyncHotspotUsersJob tidak ditemukan");
        }

        $content = file_get_contents($jobFile);

        // Cari ConnectionException handling dengan $this->release()
        $hasGracefulHandling = (
            str_contains($content, 'ConnectionException') &&
            str_contains($content, '$this->release')
        );

        // Test ini AKAN GAGAL karena job tidak menangani ConnectionException dengan release
        $this->assertTrue(
            $hasGracefulHandling,
            "Bug 1.21: SyncHotspotUsersJob tidak menangani ConnectionException dengan " .
            "\$this->release(backoff). Job akan fail permanen saat MikroTik timeout " .
            "tanpa retry dengan backoff yang tepat."
        );
    }

    /**
     * @test
     * Bug 1.21: Verifikasi bahwa ada PollRouterUsageJob dengan backoff yang benar
     *
     * AKAN GAGAL jika PollRouterUsageJob tidak memiliki backoff
     */
    public function test_poll_router_usage_job_has_backoff(): void
    {
        $jobFile = 'app/Jobs/Telecom/PollRouterUsageJob.php';

        if (!file_exists($jobFile)) {
            $this->markTestSkipped("PollRouterUsageJob tidak ditemukan");
        }

        $content = file_get_contents($jobFile);

        // Cari backoff property atau method
        $hasBackoff = (
            str_contains($content, 'backoff') ||
            str_contains($content, 'release(') ||
            str_contains($content, 'ConnectionException')
        );

        // Test ini AKAN GAGAL jika tidak ada backoff handling
        $this->assertTrue(
            $hasBackoff,
            "Bug 1.21: PollRouterUsageJob tidak memiliki backoff atau graceful " .
            "ConnectionException handling."
        );
    }
}
