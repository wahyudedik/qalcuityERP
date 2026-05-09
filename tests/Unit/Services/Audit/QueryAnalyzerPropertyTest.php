<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\QueryAnalyzer;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-Based Tests for QueryAnalyzer.
 *
 * Feature: comprehensive-erp-audit
 *
 * These tests generate random controller method stubs with various
 * combinations of query patterns (->get(), ::all()) and bounding
 * patterns (paginate, chunk, limit, cursor, first, aggregates),
 * then run the QueryAnalyzer against them to verify bounded query enforcement.
 */
class QueryAnalyzerPropertyTest extends TestCase
{
    use TestTrait;

    private string $tempDir;

    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/query_analyzer_test_'.uniqid();
        mkdir($this->tempDir.'/app/Http/Controllers', 0777, true);
        mkdir($this->tempDir.'/app/Services', 0777, true);
        $this->basePath = $this->tempDir;
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Property 7: Bounded Query Enforcement ───────────────────

    /**
     * Property 7: Bounded Query Enforcement
     *
     * For any controller method that returns a list/index view, if the
     * database query uses ->get() or ::all() WITHOUT paginate(),
     * simplePaginate(), chunk(), cursor(), limit(), take(), first(),
     * or aggregate functions, the analyzer SHALL produce a finding.
     * If the method DOES use bounded patterns, no finding should be produced.
     *
     * **Validates: Requirements 11.1, 11.6**
     *
     * // Feature: comprehensive-erp-audit, Property 7: Bounded Query Enforcement
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_7_bounded_query_enforcement(): void
    {
        $this->forAll(
            Generators::elements('get', 'all'),           // queryPattern — unbounded query type
            Generators::elements(
                'none',              // no bounding → should be flagged
                'paginate',          // paginate() → bounded
                'simplePaginate',    // simplePaginate() → bounded
                'chunk',             // chunk() → bounded
                'cursor',            // cursor() → bounded
                'limit',             // limit() → bounded
                'take',              // take() → bounded
                'first',             // first() → bounded
                'count',             // count() → aggregate, bounded
                'sum',               // sum() → aggregate, bounded
                'pluck'              // pluck() → bounded
            ), // boundingPattern — whether/how the query is bounded
            Generators::elements(
                'index',
                'list',
                'report',
                'search',
                'export',
                'dashboard',
                'overview',
                'getAll',
                'fetchRecords',
                'browse'
            ) // methodName — controller method name
        )->then(function (string $queryPattern, string $boundingPattern, string $methodName) {
            $uniqueClass = 'TestController_Q'.uniqid();
            $filePath = $this->tempDir.'/app/Http/Controllers/'.$uniqueClass.'.php';

            $source = $this->generateControllerStub(
                $uniqueClass,
                $methodName,
                $queryPattern,
                $boundingPattern
            );
            file_put_contents($filePath, $source);

            $analyzer = new QueryAnalyzer(
                $this->tempDir.'/app/Http/Controllers',
                $this->tempDir.'/app/Services',
                $this->basePath
            );

            $findings = $analyzer->detectUnboundedQueries(
                $source,
                'App\\Http\\Controllers\\'.$uniqueClass,
                $filePath
            );

            if ($boundingPattern === 'none') {
                // Unbounded query without any bounding pattern → MUST produce a finding
                $this->assertNotEmpty(
                    $findings,
                    "Controller method with unbounded {$queryPattern}() and NO bounding pattern "
                        ."MUST produce a finding. class={$uniqueClass}, method={$methodName}"
                );

                $finding = $findings[0];
                $this->assertInstanceOf(AuditFinding::class, $finding);
                $this->assertSame('query', $finding->category);
                $this->assertSame(Severity::Medium, $finding->severity);
                $this->assertStringContainsString($methodName, $finding->title);
                $this->assertStringContainsString('Unbounded', $finding->title);
                $this->assertSame($methodName, $finding->metadata['method']);
                $this->assertSame('unbounded_query', $finding->metadata['check']);
            } else {
                // Query with a bounding pattern → should NOT produce a finding
                $this->assertEmpty(
                    $findings,
                    "Controller method with {$queryPattern}() bounded by {$boundingPattern}() "
                        ."should NOT produce a finding. class={$uniqueClass}, method={$methodName}"
                );
            }

            @unlink($filePath);
        });
    }

    // ── Controller Stub Generators ──────────────────────────────

    /**
     * Generate a controller stub with a single public method containing
     * the specified query pattern and optional bounding pattern.
     */
    private function generateControllerStub(
        string $className,
        string $methodName,
        string $queryPattern,
        string $boundingPattern,
    ): string {
        $methodBody = $this->buildMethodBody($queryPattern, $boundingPattern);

        return <<<PHP
<?php

namespace App\Http\Controllers;

class {$className}
{
    public function {$methodName}()
    {
{$methodBody}
    }
}
PHP;
    }

    /**
     * Build the method body with the appropriate query and bounding patterns.
     */
    private function buildMethodBody(string $queryPattern, string $boundingPattern): string
    {
        $lines = [];

        // Add the bounding pattern BEFORE the query if applicable
        // This simulates real code where bounding is part of the query chain
        if ($boundingPattern !== 'none') {
            return $this->buildBoundedQueryBody($queryPattern, $boundingPattern);
        }

        // Unbounded query — just ->get() or ::all() with no bounding
        if ($queryPattern === 'get') {
            $lines[] = '        $items = \\App\\Models\\Product::query()->where(\'status\', \'active\')->get();';
        } else {
            // 'all'
            $lines[] = '        $items = \\App\\Models\\Product::all();';
        }

        $lines[] = '        return view(\'products.index\', compact(\'items\'));';

        return implode("\n", $lines);
    }

    /**
     * Build a method body where the query is bounded by the specified pattern.
     */
    private function buildBoundedQueryBody(string $queryPattern, string $boundingPattern): string
    {
        $lines = [];

        // For patterns that replace ->get(), build the chain differently
        $queryBase = $queryPattern === 'all'
            ? '\\App\\Models\\Product::query()'
            : '\\App\\Models\\Product::query()->where(\'status\', \'active\')';

        $boundedCall = match ($boundingPattern) {
            'paginate' => '->paginate(15)',
            'simplePaginate' => '->simplePaginate(15)',
            'chunk' => '->chunk(100, function ($products) { /* process */ })',
            'cursor' => '->cursor()',
            'limit' => '->limit(50)->get()',
            'take' => '->take(10)->get()',
            'first' => '->first()',
            'count' => '->count()',
            'sum' => '->sum(\'amount\')',
            'pluck' => '->pluck(\'name\')',
            default => '->paginate(15)',
        };

        $lines[] = "        \$items = {$queryBase}{$boundedCall};";
        $lines[] = '        return view(\'products.index\', compact(\'items\'));';

        return implode("\n", $lines);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Recursively remove a directory and all its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
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
