<?php

namespace Tests\Feature\BugExploration;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bug 1.18 — Progress Task Melebihi 100%
 *
 * Membuktikan bahwa kalkulasi progress task tidak dibatasi maksimum 100%
 * saat actualVolume > plannedVolume.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class ProyekProgressOverflowTest extends TestCase
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
     * Bug 1.18: Progress task tidak boleh melebihi 100% meskipun actualVolume > plannedVolume
     *
     * AKAN GAGAL karena tidak ada min(100, ...) cap di kalkulasi progress
     *
     * Validates: Requirements 1.18
     */
    public function test_task_progress_does_not_exceed_100_percent(): void
    {
        // Arrange: Buat project dan task dengan volume tracking
        $project = Project::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'name' => 'Proyek Test',
            'number' => 'PRJ-' . uniqid(),
            'status' => 'active',
        ]);

        $task = ProjectTask::create([
            'tenant_id' => $this->tenant->id,
            'project_id' => $project->id,
            'name' => 'Pengecoran Lantai 1',
            'progress_method' => 'volume',
            'target_volume' => 100.0, // Rencana: 100 m3
            'volume_unit' => 'm3',
            'actual_volume' => 0,
            'progress' => 0,
            'status' => 'in_progress',
        ]);

        // Act: Update progress dengan actualVolume > plannedVolume
        $actualVolume = 150.0; // Melebihi rencana 100 m3

        // Simulasi kalkulasi progress tanpa cap
        $progressWithoutCap = ($actualVolume / $task->target_volume) * 100;
        // = (150 / 100) * 100 = 150%

        // Update task dengan kalkulasi yang ada (tanpa cap)
        $task->update([
            'actual_volume' => $actualVolume,
            'progress' => $progressWithoutCap, // 150% - ini yang salah
        ]);

        $task->refresh();

        // Assert: Progress tidak boleh melebihi 100
        // Test ini AKAN GAGAL karena progress = 150
        $this->assertLessThanOrEqual(
            100,
            $task->progress,
            "Bug 1.18: Progress task melebihi 100%! " .
            "actualVolume: {$actualVolume}, plannedVolume: {$task->target_volume}, " .
            "progress: {$task->progress}%. " .
            "Seharusnya progress dibatasi maksimum 100% menggunakan min(100, ...)."
        );
    }

    /**
     * @test
     * Bug 1.18: Verifikasi bahwa ada min(100, ...) cap di kalkulasi progress
     *
     * AKAN GAGAL jika tidak ada cap di kode
     */
    public function test_project_service_has_progress_cap(): void
    {
        $serviceFiles = [
            'app/Services/ProjectService.php',
            'app/Services/ERP/ProjectTools.php',
            'app/Http/Controllers/ProjectController.php',
        ];

        $hasProgressCap = false;
        $foundFile = null;

        foreach ($serviceFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (
                    str_contains($content, 'min(100') ||
                    str_contains($content, 'min(100.0') ||
                    str_contains($content, 'progress > 100') ||
                    str_contains($content, 'progress >= 100')
                ) {
                    $hasProgressCap = true;
                    $foundFile = $file;
                    break;
                }
            }
        }

        // Test ini AKAN GAGAL karena tidak ada min(100, ...) cap
        $this->assertTrue(
            $hasProgressCap,
            "Bug 1.18: Tidak ditemukan min(100, ...) cap di kalkulasi progress task. " .
            "File yang dicari: " . implode(', ', $serviceFiles) . ". " .
            "Progress bisa melebihi 100% saat actualVolume > plannedVolume."
        );
    }

    /**
     * @test
     * Bug 1.18: Kalkulasi progress dengan volume tracking harus dibatasi 100%
     *
     * AKAN GAGAL karena tidak ada validasi
     */
    public function test_volume_progress_calculation_is_capped_at_100(): void
    {
        // Simulasi kalkulasi progress yang ada di sistem
        $plannedVolume = 100.0;
        $actualVolumes = [50.0, 100.0, 120.0, 200.0];

        foreach ($actualVolumes as $actual) {
            // Kalkulasi tanpa cap (bug)
            $progressWithBug = ($actual / $plannedVolume) * 100;

            // Assert: progress tidak boleh melebihi 100
            // Test ini AKAN GAGAL untuk actual = 120 dan 200
            $this->assertLessThanOrEqual(
                100,
                $progressWithBug,
                "Bug 1.18: Kalkulasi progress untuk actualVolume={$actual}, " .
                "plannedVolume={$plannedVolume} menghasilkan {$progressWithBug}% " .
                "(melebihi 100%). Seharusnya dibatasi dengan min(100, progress)."
            );
        }
    }
}
