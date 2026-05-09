<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\QueryAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for QueryAnalyzer.
 *
 * Uses temporary fixture files to test detection of:
 * - Unbounded queries (->get()/::all() without pagination)
 * - N+1 query patterns (relationship access in loops without eager loading)
 * - Raw queries (DB::select, DB::table, DB::raw) missing tenant_id filtering
 *
 * Validates: Requirements 1.2, 11.1
 */
class QueryAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    private string $controllerDir;

    private string $serviceDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir().'/query_analyzer_test_'.uniqid();
        $this->controllerDir = $this->fixtureDir.'/app/Http/Controllers';
        $this->serviceDir = $this->fixtureDir.'/app/Services';
        mkdir($this->controllerDir, 0777, true);
        mkdir($this->serviceDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_query(): void
    {
        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $this->assertSame('query', $analyzer->category());
    }

    // ── Unbounded Query Detection ─────────────────────────────────

    public function test_detects_unbounded_get_in_controller(): void
    {
        $this->writeController('ProductController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ProductController
{
    public function index()
    {
        $products = Product::where('tenant_id', $this->tid())
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertNotEmpty($unbounded, 'Should detect unbounded ->get() in controller');

        $finding = reset($unbounded);
        $this->assertSame('query', $finding->category);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertStringContainsString('ProductController', $finding->title);
        $this->assertStringContainsString('index', $finding->title);
    }

    public function test_detects_unbounded_all_in_controller(): void
    {
        $this->writeController('ItemController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ItemController
{
    public function list()
    {
        $items = Item::all();
        return view('items.list', compact('items'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertNotEmpty($unbounded, 'Should detect unbounded ::all() in controller');
    }

    public function test_does_not_flag_query_with_paginate(): void
    {
        $this->writeController('PaginatedController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class PaginatedController
{
    public function index()
    {
        $products = Product::where('tenant_id', $this->tid())
            ->paginate(25);

        return view('products.index', compact('products'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertEmpty($unbounded, 'Should not flag query with paginate()');
    }

    public function test_does_not_flag_query_with_chunk(): void
    {
        $this->writeController('ChunkedController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ChunkedController
{
    public function export()
    {
        Product::where('tenant_id', $this->tid())
            ->chunk(100, function ($products) {
                // process chunk
            });
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertEmpty($unbounded, 'Should not flag query with chunk()');
    }

    public function test_does_not_flag_query_with_limit(): void
    {
        $this->writeController('LimitedController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class LimitedController
{
    public function recent()
    {
        $items = Product::where('tenant_id', $this->tid())
            ->limit(10)
            ->get();

        return view('products.recent', compact('items'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertEmpty($unbounded, 'Should not flag query with limit()');
    }

    public function test_does_not_flag_query_with_first(): void
    {
        $this->writeController('FirstController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class FirstController
{
    public function show($id)
    {
        $product = Product::where('id', $id)->first();
        return view('products.show', compact('product'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertEmpty($unbounded, 'Should not flag query with first()');
    }

    public function test_does_not_flag_query_with_cursor(): void
    {
        $this->writeController('CursorController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class CursorController
{
    public function export()
    {
        $products = Product::where('tenant_id', $this->tid())
            ->cursor();

        foreach ($products as $product) {
            // process
        }
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertEmpty($unbounded, 'Should not flag query with cursor()');
    }

    // ── N+1 Query Detection ──────────────────────────────────────

    public function test_detects_n_plus_one_pattern(): void
    {
        $this->writeController('OrderController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class OrderController
{
    public function index()
    {
        $orders = Order::where('tenant_id', $this->tid())->get();

        foreach ($orders as $order) {
            $customerName = $order->customer->name;
        }

        return view('orders.index', compact('orders'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $nPlusOne = $this->filterByCheck($findings, 'n_plus_one');
        $this->assertNotEmpty($nPlusOne, 'Should detect N+1 pattern');

        $finding = reset($nPlusOne);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertStringContainsString('N+1', $finding->title);
    }

    public function test_does_not_flag_n_plus_one_with_eager_loading(): void
    {
        $this->writeController('EagerController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class EagerController
{
    public function index()
    {
        $orders = Order::where('tenant_id', $this->tid())
            ->with(['customer'])
            ->get();

        foreach ($orders as $order) {
            $customerName = $order->customer->name;
        }

        return view('orders.index', compact('orders'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $nPlusOne = $this->filterByCheck($findings, 'n_plus_one');
        $this->assertEmpty($nPlusOne, 'Should not flag N+1 when eager loading is present');
    }

    public function test_does_not_flag_n_plus_one_with_load(): void
    {
        $this->writeController('LoadController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class LoadController
{
    public function index()
    {
        $orders = Order::where('tenant_id', $this->tid())->get();
        $orders->load('customer');

        foreach ($orders as $order) {
            $customerName = $order->customer->name;
        }

        return view('orders.index', compact('orders'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $nPlusOne = $this->filterByCheck($findings, 'n_plus_one');
        $this->assertEmpty($nPlusOne, 'Should not flag N+1 when ->load() is present');
    }

    public function test_does_not_flag_loop_without_relationship_access(): void
    {
        $this->writeController('SimpleLoopController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class SimpleLoopController
{
    public function index()
    {
        $products = Product::where('tenant_id', $this->tid())->get();

        foreach ($products as $product) {
            $name = $product->name;
        }

        return view('products.index', compact('products'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $nPlusOne = $this->filterByCheck($findings, 'n_plus_one');
        $this->assertEmpty($nPlusOne, 'Should not flag loop without chained relationship access');
    }

    // ── Raw Query Tenant ID Detection ────────────────────────────

    public function test_detects_db_table_without_tenant_id(): void
    {
        $this->writeService('ReportService.php', <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getReport()
    {
        $results = DB::table('invoices')
            ->where('status', 'paid')
            ->sum('total');

        return $results;
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $rawFindings = $this->filterByCheck($findings, 'raw_query_tenant');
        $this->assertNotEmpty($rawFindings, 'Should detect DB::table() without tenant_id');

        $finding = reset($rawFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertStringContainsString('tenant_id', $finding->description);
    }

    public function test_detects_db_select_without_tenant_id(): void
    {
        $this->writeService('QueryService.php', <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QueryService
{
    public function customQuery()
    {
        return DB::select('SELECT * FROM orders WHERE status = ?', ['active']);
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $rawFindings = $this->filterByCheck($findings, 'raw_query_tenant');
        $this->assertNotEmpty($rawFindings, 'Should detect DB::select() without tenant_id');
    }

    public function test_detects_db_raw_without_tenant_id(): void
    {
        $this->writeController('RawController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class RawController
{
    public function stats()
    {
        $result = DB::raw('SELECT COUNT(*) FROM products');
        return response()->json($result);
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $rawFindings = $this->filterByCheck($findings, 'raw_query_tenant');
        $this->assertNotEmpty($rawFindings, 'Should detect DB::raw() without tenant_id');
    }

    public function test_does_not_flag_db_table_with_tenant_id_where(): void
    {
        $this->writeService('TenantReportService.php', <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TenantReportService
{
    public function getReport($tenantId)
    {
        $results = DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->sum('total');

        return $results;
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $rawFindings = $this->filterByCheck($findings, 'raw_query_tenant');
        $this->assertEmpty($rawFindings, 'Should not flag DB::table() with tenant_id filtering');
    }

    public function test_does_not_flag_db_select_with_tenant_id_in_sql(): void
    {
        $this->writeService('TenantQueryService.php', <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TenantQueryService
{
    public function customQuery($tid)
    {
        return DB::select('SELECT * FROM orders WHERE tenant_id = ? AND status = ?', [$tid, 'active']);
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $rawFindings = $this->filterByCheck($findings, 'raw_query_tenant');
        $this->assertEmpty($rawFindings, 'Should not flag DB::select() with tenant_id in SQL');
    }

    public function test_does_not_flag_raw_query_with_tid_variable(): void
    {
        $this->writeService('TidService.php', <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TidService
{
    public function getData()
    {
        $tid = auth()->user()->tenant_id;
        $results = DB::table('products')
            ->where('tenant_id', $tid)
            ->get();

        return $results;
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $rawFindings = $this->filterByCheck($findings, 'raw_query_tenant');
        $this->assertEmpty($rawFindings, 'Should not flag raw query when $tid is used with tenant_id');
    }

    public function test_detects_db_statement_without_tenant_id(): void
    {
        $this->writeService('MigrationService.php', <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MigrationService
{
    public function runCustom()
    {
        DB::statement('UPDATE products SET status = ? WHERE status = ?', ['active', 'pending']);
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $rawFindings = $this->filterByCheck($findings, 'raw_query_tenant');
        $this->assertNotEmpty($rawFindings, 'Should detect DB::statement() without tenant_id');
    }

    // ── Skipping Abstract/Interface/Trait ─────────────────────────

    public function test_skips_abstract_classes(): void
    {
        $this->writeController('AbstractController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

abstract class AbstractController
{
    public function index()
    {
        $items = Item::all();
        return view('items.index', compact('items'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip abstract classes');
    }

    public function test_skips_traits(): void
    {
        $this->writeService('QueryTrait.php', <<<'PHP'
<?php

namespace App\Services;

trait QueryTrait
{
    public function getAll()
    {
        return DB::table('items')->get();
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip traits');
    }

    // ── Empty/Nonexistent Directories ─────────────────────────────

    public function test_returns_empty_for_empty_directories(): void
    {
        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    public function test_returns_empty_for_nonexistent_directories(): void
    {
        $analyzer = new QueryAnalyzer('/nonexistent/controllers', '/nonexistent/services');
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    // ── Finding Structure ────────────────────────────────────────

    public function test_findings_have_correct_structure(): void
    {
        $this->writeController('StructureController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class StructureController
{
    public function index()
    {
        $items = Product::where('active', true)->get();
        return view('products.index', compact('items'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings);

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertSame('query', $finding->category);
            $this->assertInstanceOf(Severity::class, $finding->severity);
            $this->assertNotEmpty($finding->title);
            $this->assertNotEmpty($finding->description);
            $this->assertNotNull($finding->file);
            $this->assertNotNull($finding->recommendation);
            $this->assertIsArray($finding->metadata);
            $this->assertArrayHasKey('check', $finding->metadata);
        }
    }

    // ── Subdirectory Scanning ────────────────────────────────────

    public function test_scans_controller_subdirectories(): void
    {
        $subDir = $this->controllerDir.'/Admin';
        mkdir($subDir, 0777, true);

        file_put_contents($subDir.'/AdminController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Admin;

class AdminController
{
    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertNotEmpty($unbounded, 'Should scan controllers in subdirectories');
    }

    // ── Multiple Issues in Same File ─────────────────────────────

    public function test_detects_multiple_issues_in_same_file(): void
    {
        $this->writeController('MultiIssueController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class MultiIssueController
{
    public function index()
    {
        $items = Product::where('active', true)->get();
        return view('products.index', compact('items'));
    }

    public function report()
    {
        $total = DB::table('sales')->sum('amount');
        return response()->json(['total' => $total]);
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $rawQuery = $this->filterByCheck($findings, 'raw_query_tenant');

        $this->assertNotEmpty($unbounded, 'Should detect unbounded query');
        $this->assertNotEmpty($rawQuery, 'Should detect raw query without tenant_id');
    }

    // ── Does Not Flag Pluck/Count/Aggregates ─────────────────────

    public function test_does_not_flag_pluck_as_unbounded(): void
    {
        $this->writeController('PluckController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class PluckController
{
    public function names()
    {
        $names = Product::where('tenant_id', $this->tid())
            ->pluck('name');

        return view('products.names', compact('names'));
    }
}
PHP);

        $analyzer = new QueryAnalyzer($this->controllerDir, $this->serviceDir);
        $findings = $analyzer->analyze();

        $unbounded = $this->filterByCheck($findings, 'unbounded_query');
        $this->assertEmpty($unbounded, 'Should not flag pluck() as unbounded');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function writeController(string $relativePath, string $content): void
    {
        $fullPath = $this->controllerDir.'/'.$relativePath;
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, $content);
    }

    private function writeService(string $relativePath, string $content): void
    {
        $fullPath = $this->serviceDir.'/'.$relativePath;
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, $content);
    }

    /**
     * Filter findings by the 'check' metadata key.
     *
     * @param  AuditFinding[]  $findings
     * @return AuditFinding[]
     */
    private function filterByCheck(array $findings, string $check): array
    {
        return array_values(array_filter(
            $findings,
            fn (AuditFinding $f) => ($f->metadata['check'] ?? null) === $check
        ));
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
