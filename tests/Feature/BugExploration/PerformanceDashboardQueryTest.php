<?php

namespace Tests\Feature\BugExploration;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bug 1.27 — Dashboard N+1 Query Problem
 *
 * Membuktikan bahwa dashboard controller menjalankan terlalu banyak query
 * karena N+1 problem, melebihi threshold yang ditetapkan.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 *
 * CATATAN: Berdasarkan kode aktual, DashboardController sudah memiliki
 * caching dan eager loading. Test ini memverifikasi apakah query count
 * masih melebihi threshold.
 */
class PerformanceDashboardQueryTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant(['plan' => 'business']);

        $this->user = $this->createAdminUser($this->tenant);
    }

    /**
     * @test
     * Bug 1.27: Dashboard harus menjalankan query <= threshold (20 queries)
     *
     * AKAN GAGAL jika dashboard menjalankan terlalu banyak query
     *
     * Validates: Requirements 1.27
     */
    public function test_dashboard_query_count_within_threshold(): void
    {
        $queryThreshold = 20;

        // Enable query logging
        DB::enableQueryLog();
        DB::flushQueryLog();

        // Act: Load dashboard
        $this->actingAs($this->user);
        $response = $this->get(route('dashboard'));

        // Get query log
        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        DB::disableQueryLog();

        // Assert: Query count harus <= threshold
        // Test ini AKAN GAGAL jika dashboard menjalankan terlalu banyak query
        $this->assertLessThanOrEqual(
            $queryThreshold,
            $queryCount,
            "Bug 1.27: Dashboard menjalankan {$queryCount} queries, melebihi threshold {$queryThreshold}. " .
            "Ini mengindikasikan N+1 query problem. " .
            "Query yang dijalankan:\n" .
            implode("\n", array_map(
                fn($q) => substr($q['query'], 0, 100),
                array_slice($queries, 0, 10)
            ))
        );
    }

    /**
     * @test
     * Bug 1.27: Dashboard harus menggunakan cache untuk query agregat
     *
     * AKAN GAGAL jika tidak ada cache
     */
    public function test_dashboard_uses_cache_for_aggregate_queries(): void
    {
        $dashboardFile = 'app/Http/Controllers/DashboardController.php';

        if (!file_exists($dashboardFile)) {
            $this->markTestSkipped("DashboardController tidak ditemukan");
        }

        $content = file_get_contents($dashboardFile);

        // Cari penggunaan cache
        $usesCache = (
            str_contains($content, 'Cache::remember') ||
            str_contains($content, 'cache()->remember') ||
            str_contains($content, 'cache()')
        );

        // Test ini AKAN GAGAL jika tidak ada cache
        $this->assertTrue(
            $usesCache,
            "Bug 1.27: DashboardController tidak menggunakan cache untuk query agregat. " .
            "Setiap load dashboard akan menjalankan semua query dari awal."
        );
    }

    /**
     * @test
     * Bug 1.27: Dashboard harus menggunakan eager loading untuk relasi
     *
     * AKAN GAGAL jika tidak ada eager loading
     */
    public function test_dashboard_uses_eager_loading(): void
    {
        $dashboardFile = 'app/Http/Controllers/DashboardController.php';

        if (!file_exists($dashboardFile)) {
            $this->markTestSkipped("DashboardController tidak ditemukan");
        }

        $content = file_get_contents($dashboardFile);

        // Cari eager loading
        $usesEagerLoading = (
            str_contains($content, '->with(') ||
            str_contains($content, '->load(') ||
            str_contains($content, 'with([')
        );

        // Test ini AKAN GAGAL jika tidak ada eager loading
        $this->assertTrue(
            $usesEagerLoading,
            "Bug 1.27: DashboardController tidak menggunakan eager loading untuk relasi. " .
            "Ini menyebabkan N+1 query problem saat memuat data dashboard."
        );
    }

    /**
     * @test
     * Bug 1.27: Dashboard harus menggunakan selectRaw untuk agregasi
     *
     * AKAN GAGAL jika tidak ada selectRaw
     */
    public function test_dashboard_uses_aggregate_queries(): void
    {
        $dashboardFile = 'app/Http/Controllers/DashboardController.php';

        if (!file_exists($dashboardFile)) {
            $this->markTestSkipped("DashboardController tidak ditemukan");
        }

        $content = file_get_contents($dashboardFile);

        // Cari selectRaw atau aggregate queries
        $usesAggregates = (
            str_contains($content, 'selectRaw') ||
            str_contains($content, '->sum(') ||
            str_contains($content, '->count(') ||
            str_contains($content, 'groupBy')
        );

        // Test ini AKAN GAGAL jika tidak ada aggregate queries
        $this->assertTrue(
            $usesAggregates,
            "Bug 1.27: DashboardController tidak menggunakan aggregate queries (selectRaw, sum, count). " .
            "Ini menyebabkan N+1 query problem."
        );
    }
}
