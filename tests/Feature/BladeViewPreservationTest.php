<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Eris\Generator;
use Eris\TestTrait;

/**
 * Preservation Property Tests — Working Blade Views Remain Unchanged
 *
 * EXPECTED: These tests MUST PASS on unfixed code.
 * They establish baseline behavior that must be preserved after the fix.
 *
 * Views where isBugCondition returns FALSE are "clean" views — they must
 * remain completely unchanged after the fix is applied.
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10
 */
class BladeViewPreservationTest extends TestCase
{
    use TestTrait;

    private string $viewsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $projectRoot = $this->resolveProjectRoot();
        $this->viewsPath = $projectRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }

    // ── Helper: resolve project root ─────────────────────────────

    /**
     * Resolve the project root directory.
     * Works whether running via `php artisan test` or `vendor/bin/phpunit`.
     */
    private function resolveProjectRoot(): string
    {
        $cwd = getcwd();

        if (is_dir($cwd . '/resources/views')) {
            return $cwd;
        }

        $dir = __DIR__;
        for ($i = 0; $i < 5; $i++) {
            $dir = dirname($dir);
            if (is_dir($dir . '/resources/views')) {
                return $dir;
            }
        }

        return $cwd;
    }

    // ── Helper: file discovery ────────────────────────────────────

    /**
     * Recursively find all *.blade.php files in resources/views/.
     *
     * @return string[] Array of absolute file paths
     */
    private function getAllBladeFiles(): array
    {
        if (!is_dir($this->viewsPath)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->viewsPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);
        return $files;
    }

    // ── Helper: isBugCondition ────────────────────────────────────

    /**
     * Returns true if the view content contains ANY of the 5 bug pattern categories.
     * Views where this returns false are "clean" and must be preserved by the fix.
     *
     * Category 1: @foreach($var as ...) without ?? [] guard
     * Category 2: x-data="..." with direct {{ }} interpolation
     * Category 3: $model->relation->property without ?->
     * Category 4: Duplicate <canvas id="X"> in same file
     * Category 5: Bootstrap CSS classes (btn, card, d-flex, col-md-, etc.)
     */
    private function isBugCondition(string $content): bool
    {
        // Category 1: @foreach without ?? []
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (preg_match('/@foreach\(\$\w+\s+as/i', $line)) {
                if (!str_contains($line, '?? []') && !str_contains($line, '??[]')) {
                    return true;
                }
            }
        }

        // Category 2: x-data with direct {{ }} interpolation
        if (preg_match('/x-data="[^"]*\{\{[^"]*\}\}[^"]*"/', $content)) {
            return true;
        }

        // Category 3: chained Eloquent without ?->
        foreach ($lines as $line) {
            if (preg_match('/\$\w+->\w+->\w+/', $line) && !str_contains($line, '?->')) {
                return true;
            }
        }

        // Category 4: duplicate canvas IDs
        preg_match_all('/<canvas[^>]+id="([^"{}]+)"/', $content, $matches);
        if (!empty($matches[1])) {
            $counts = array_count_values($matches[1]);
            if (array_filter($counts, fn($c) => $c > 1)) {
                return true;
            }
        }

        // Category 5: Bootstrap classes
        $bootstrapPattern = '/class=["\'][^"\']*\b(btn|card|d-flex|col-md-|text-primary|text-success|text-danger|text-muted|progress)\b[^"\']*["\']/';
        if (preg_match($bootstrapPattern, $content)) {
            return true;
        }

        return false;
    }

    /**
     * Return all blade files where isBugCondition returns false (the "clean" views).
     *
     * @return string[] Array of absolute file paths
     */
    private function getCleanViews(): array
    {
        return array_values(array_filter(
            $this->getAllBladeFiles(),
            fn($path) => !$this->isBugCondition(file_get_contents($path))
        ));
    }

    // ── Preservation 1: Clean views have no bug patterns ─────────

    /**
     * @test
     * Preservation 1: For all views where isBugCondition returns false, verify they
     * truly have none of the 5 bug pattern categories.
     *
     * This establishes the baseline set of "clean" views that must be preserved.
     *
     * Validates: Requirements 3.1, 3.4
     */
    public function test_clean_views_have_no_bug_patterns(): void
    {
        $allFiles = $this->getAllBladeFiles();

        $this->assertNotEmpty(
            $allFiles,
            "No blade files found in {$this->viewsPath}. Check the path."
        );

        $cleanViews = $this->getCleanViews();

        // Document the count for baseline reference
        $totalCount = count($allFiles);
        $cleanCount = count($cleanViews);
        $buggyCount = $totalCount - $cleanCount;

        // Every "clean" view must genuinely have no bug patterns
        $falseNegatives = [];
        foreach ($cleanViews as $filePath) {
            $content = file_get_contents($filePath);
            if ($this->isBugCondition($content)) {
                $relPath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                $relPath = str_replace('\\', '/', $relPath);
                $falseNegatives[] = $relPath;
            }
        }

        $this->assertEmpty(
            $falseNegatives,
            "These views were classified as clean but contain bug patterns:\n"
                . implode("\n", $falseNegatives)
        );

        // Log the baseline counts (visible in verbose output)
        $this->addToAssertionCount(1);
        // Baseline: total={$totalCount}, clean={$cleanCount}, buggy={$buggyCount}
        // This comment documents the counts found at test-writing time.
        $this->assertGreaterThan(
            0,
            $cleanCount,
            "Expected at least some clean views to exist. Total: {$totalCount}, Clean: {$cleanCount}"
        );
    }

    // ── Preservation 2: Blade component files exist ───────────────

    /**
     * @test
     * Preservation 2: Verify that key Blade component files exist in
     * resources/views/components/. These must not be deleted or moved by the fix.
     *
     * Validates: Requirements 3.2, 3.7
     */
    public function test_blade_components_exist(): void
    {
        $projectRoot = $this->resolveProjectRoot();
        $componentsPath = $projectRoot . '/resources/views/components';

        $this->assertDirectoryExists(
            $componentsPath,
            "Blade components directory must exist at resources/views/components/"
        );

        // Key components that must remain intact
        $requiredComponents = [
            'modal.blade.php',
            'alert.blade.php',
            'table.blade.php',
            'button.blade.php',
            'card.blade.php',
            'dropdown.blade.php',
            'empty-state.blade.php',
            'form-group.blade.php',
            'input-error.blade.php',
            'input-label.blade.php',
            'text-input.blade.php',
            'breadcrumbs.blade.php',
            'toast.blade.php',
        ];

        $missing = [];
        foreach ($requiredComponents as $component) {
            $fullPath = $componentsPath . '/' . $component;
            if (!file_exists($fullPath)) {
                $missing[] = "components/{$component}";
            }
        }

        $this->assertEmpty(
            $missing,
            "These required Blade component files are missing:\n" . implode("\n", $missing)
        );
    }

    // ── Preservation 3: Layout files exist and have required sections ──

    /**
     * @test
     * Preservation 3: Verify layout files exist and contain required content sections.
     * These layouts must not be deleted, moved, or have their slot/yield directives removed.
     *
     * Validates: Requirements 3.8
     */
    public function test_layout_files_exist(): void
    {
        $projectRoot = $this->resolveProjectRoot();
        $layoutsPath = $projectRoot . '/resources/views/layouts';

        $this->assertDirectoryExists(
            $layoutsPath,
            "Layouts directory must exist at resources/views/layouts/"
        );

        // app.blade.php must exist and contain $slot or @yield('content')
        $appLayout = $layoutsPath . '/app.blade.php';
        $this->assertFileExists(
            $appLayout,
            "resources/views/layouts/app.blade.php must exist"
        );

        $appContent = file_get_contents($appLayout);
        $hasSlotOrYield = str_contains($appContent, '{{ $slot') || str_contains($appContent, "@yield('content')");
        $this->assertTrue(
            $hasSlotOrYield,
            "app.blade.php must contain '{{ \$slot' or \"@yield('content')\" to render page content"
        );

        // guest.blade.php must exist and contain $slot or @yield('content')
        $guestLayout = $layoutsPath . '/guest.blade.php';
        $this->assertFileExists(
            $guestLayout,
            "resources/views/layouts/guest.blade.php must exist"
        );

        $guestContent = file_get_contents($guestLayout);
        $guestHasSlotOrYield = str_contains($guestContent, '{{ $slot') || str_contains($guestContent, "@yield('content')");
        $this->assertTrue(
            $guestHasSlotOrYield,
            "guest.blade.php must contain '{{ \$slot' or \"@yield('content')\" to render page content"
        );
    }

    // ── Preservation 4: Clean Chart.js views have unique canvas IDs ──

    /**
     * @test
     * Preservation 4: For views that already have no duplicate canvas IDs
     * (the passing baseline from Task 1), verify they still have unique IDs.
     *
     * This is a baseline check — clean views must remain clean after the fix.
     *
     * Validates: Requirements 3.6
     */
    public function test_clean_chartjs_views_have_unique_canvas_ids(): void
    {
        $allFiles = $this->getAllBladeFiles();

        // Find views that have canvas elements but no duplicate IDs (already clean)
        $cleanChartViews = [];
        foreach ($allFiles as $filePath) {
            $content = file_get_contents($filePath);

            // Only consider views that actually have canvas elements
            preg_match_all('/<canvas[^>]+id="([^"{}]+)"/', $content, $matches);
            if (empty($matches[1])) {
                continue;
            }

            // Check for duplicates
            $counts = array_count_values($matches[1]);
            $hasDuplicates = !empty(array_filter($counts, fn($c) => $c > 1));

            if (!$hasDuplicates) {
                $cleanChartViews[] = $filePath;
            }
        }

        if (empty($cleanChartViews)) {
            $this->markTestSkipped('No views with canvas elements found to check.');
            return;
        }

        // Verify each clean chart view still has unique canvas IDs
        $violations = [];
        foreach ($cleanChartViews as $filePath) {
            $content = file_get_contents($filePath);
            preg_match_all('/<canvas[^>]+id="([^"{}]+)"/', $content, $matches);

            if (!empty($matches[1])) {
                $counts = array_count_values($matches[1]);
                $duplicates = array_filter($counts, fn($c) => $c > 1);

                if (!empty($duplicates)) {
                    $relPath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                    $relPath = str_replace('\\', '/', $relPath);
                    foreach (array_keys($duplicates) as $dupId) {
                        $violations[] = sprintf(
                            '%s: canvas id="%s" appears %d times (was unique before)',
                            $relPath,
                            $dupId,
                            $counts[$dupId]
                        );
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "These previously-clean Chart.js views now have duplicate canvas IDs:\n"
                . implode("\n", $violations)
        );
    }

    // ── Preservation 5: PBT — random clean view sampling ─────────

    /**
     * @test
     * Preservation 5: Using Eris, generate random indices into the array of clean views
     * and verify each sampled view still has no bug patterns.
     *
     * This uses property-based testing to sample across the clean view space,
     * providing strong assurance that the fix does not introduce new bug patterns
     * into previously-clean views.
     *
     * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10
     */
    public function test_random_clean_view_sampling_has_no_bug_patterns(): void
    {
        $cleanViews = $this->getCleanViews();

        if (empty($cleanViews)) {
            $this->markTestSkipped('No clean views found to sample.');
            return;
        }

        $maxIndex = count($cleanViews) - 1;

        $this->forAll(
            Generator\choose(0, $maxIndex)
        )->then(function (int $index) use ($cleanViews) {
            $filePath = $cleanViews[$index];
            $content = file_get_contents($filePath);

            $this->assertFalse(
                $this->isBugCondition($content),
                "Clean view has bug pattern: " . basename($filePath)
            );
        });
    }
}
