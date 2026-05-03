<?php

namespace Tests\Unit\Services\Audit;

use App\Services\Audit\PerformanceAnalyzer;
use PHPUnit\Framework\TestCase;

class PerformanceAnalyzerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/performance_analyzer_test_' . uniqid();
        mkdir($this->tempDir . '/app/Http/Controllers', 0777, true);
        mkdir($this->tempDir . '/app/Exports', 0777, true);
        mkdir($this->tempDir . '/app/Jobs', 0777, true);
        mkdir($this->tempDir . '/app/Services', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_detects_unbounded_queries_in_controller_methods(): void
    {
        $controller = <<<'PHP'
<?php
namespace App\Http\Controllers;
class ProductController
{
    public function index()
    {
        $products = Product::query()->get();
        return $products;
    }
}
PHP;
        file_put_contents($this->tempDir . '/app/Http/Controllers/ProductController.php', $controller);

        $analyzer = new PerformanceAnalyzer(basePath: $this->tempDir);
        $findings = $analyzer->checkPagination();

        $this->assertNotEmpty($findings);
        $this->assertSame('pagination', $findings[0]->metadata['check']);
    }

    public function test_detects_export_classes_missing_chunked_processing(): void
    {
        $export = <<<'PHP'
<?php
namespace App\Exports;
class SalesExport
{
}
PHP;
        file_put_contents($this->tempDir . '/app/Exports/SalesExport.php', $export);

        $analyzer = new PerformanceAnalyzer(basePath: $this->tempDir);
        $findings = $analyzer->checkExportChunking();

        $this->assertNotEmpty($findings);
        $this->assertSame('export_chunking', $findings[0]->metadata['check']);
    }

    public function test_detects_queue_jobs_without_timeout_configuration(): void
    {
        $job = <<<'PHP'
<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
class SyncUsersJob implements ShouldQueue
{
    public function handle(): void
    {
    }
}
PHP;
        file_put_contents($this->tempDir . '/app/Jobs/SyncUsersJob.php', $job);

        $analyzer = new PerformanceAnalyzer(basePath: $this->tempDir);
        $findings = $analyzer->checkJobConfiguration();

        $timeoutFindings = array_filter(
            $findings,
            static fn ($finding) => ($finding->metadata['check'] ?? null) === 'job_timeout'
        );

        $this->assertNotEmpty($timeoutFindings);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
