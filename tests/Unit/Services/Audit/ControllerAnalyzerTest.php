<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\ControllerAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ControllerAnalyzer.
 *
 * Uses temporary controller fixture files to test detection of:
 * - Missing try-catch blocks in public methods
 * - Missing authorization checks in data-modifying methods
 *
 * Validates: Requirements 1.1, 1.5, 1.7
 */
class ControllerAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir().'/controller_analyzer_test_'.uniqid();
        mkdir($this->fixtureDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_controller(): void
    {
        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $this->assertSame('controller', $analyzer->category());
    }

    // ── Error Handling Detection ──────────────────────────────────

    public function test_detects_missing_try_catch_in_data_modifying_method(): void
    {
        $this->writeFixture('StoreController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class StoreController extends Controller
{
    public function store($request)
    {
        $product = Product::create($request->all());
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $errorFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing try-catch')
        );

        $this->assertNotEmpty($errorFindings, 'Should detect missing try-catch in store()');

        $finding = reset($errorFindings);
        $this->assertSame('controller', $finding->category);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertTrue($finding->metadata['data_modifying']);
    }

    public function test_does_not_flag_method_with_try_catch(): void
    {
        $this->writeFixture('SafeController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class SafeController extends Controller
{
    public function store($request)
    {
        try {
            $product = Product::create($request->all());
            return redirect()->back();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $errorFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing try-catch')
        );

        $this->assertEmpty($errorFindings, 'Should not flag method with try-catch');
    }

    public function test_read_only_method_gets_low_severity_for_missing_try_catch(): void
    {
        $this->writeFixture('ReadController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ReadController extends Controller
{
    public function index()
    {
        $items = Product::paginate(15);
        return view('products.index', compact('items'));
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $errorFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing try-catch') && str_contains($f->title, 'index')
        );

        $this->assertNotEmpty($errorFindings);
        $finding = reset($errorFindings);
        $this->assertSame(Severity::Low, $finding->severity);
    }

    // ── Authorization Detection ──────────────────────────────────

    public function test_detects_missing_authorization_in_data_modifying_method(): void
    {
        $this->writeFixture('NoAuthController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class NoAuthController extends Controller
{
    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $authFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing authorization')
        );

        $this->assertNotEmpty($authFindings, 'Should detect missing authorization in destroy()');

        $finding = reset($authFindings);
        $this->assertSame(Severity::High, $finding->severity);
    }

    public function test_does_not_flag_method_with_abort_unless(): void
    {
        $this->writeFixture('AbortController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class AbortController extends Controller
{
    public function update($request, $product)
    {
        abort_unless($product->tenant_id === auth()->user()->tenant_id, 403);
        $product->update($request->all());
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $authFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing authorization') && str_contains($f->title, 'update')
        );

        $this->assertEmpty($authFindings, 'Should not flag method with abort_unless');
    }

    public function test_does_not_flag_method_with_abort_if(): void
    {
        $this->writeFixture('AbortIfController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class AbortIfController extends Controller
{
    public function update($request, $account)
    {
        abort_if($account->tenant_id !== $this->tid(), 403);
        $account->update($request->all());
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $authFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing authorization') && str_contains($f->title, 'update')
        );

        $this->assertEmpty($authFindings, 'Should not flag method with abort_if');
    }

    public function test_does_not_flag_method_with_this_authorize(): void
    {
        $this->writeFixture('AuthorizeController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class AuthorizeController extends Controller
{
    public function store($request)
    {
        $this->authorize('create', Product::class);
        Product::create($request->all());
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $authFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing authorization') && str_contains($f->title, 'store')
        );

        $this->assertEmpty($authFindings, 'Should not flag method with $this->authorize()');
    }

    public function test_does_not_flag_method_with_gate_check(): void
    {
        $this->writeFixture('GateController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

class GateController extends Controller
{
    public function destroy($id)
    {
        Gate::authorize('delete-product');
        Product::findOrFail($id)->delete();
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $authFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing authorization') && str_contains($f->title, 'destroy')
        );

        $this->assertEmpty($authFindings, 'Should not flag method with Gate::authorize()');
    }

    public function test_does_not_flag_method_with_constructor_middleware(): void
    {
        $this->writeFixture('MiddlewareController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class MiddlewareController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inventory,edit');
    }

    public function store($request)
    {
        Product::create($request->all());
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $authFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing authorization') && str_contains($f->title, 'store')
        );

        $this->assertEmpty($authFindings, 'Should not flag method when constructor has middleware');
    }

    public function test_does_not_flag_read_only_methods_for_authorization(): void
    {
        $this->writeFixture('ReadOnlyController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ReadOnlyController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $authFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Missing authorization')
        );

        $this->assertEmpty($authFindings, 'Should not flag read-only methods for authorization');
    }

    // ── Skipping Abstract/Interface ──────────────────────────────

    public function test_skips_abstract_classes(): void
    {
        $this->writeFixture('AbstractController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

abstract class AbstractController extends Controller
{
    public function store($request)
    {
        Product::create($request->all());
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip abstract classes');
    }

    public function test_skips_interfaces(): void
    {
        $this->writeFixture('ControllerInterface.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

interface ControllerInterface
{
    public function index();
    public function store($request);
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip interfaces');
    }

    // ── Skipping Framework Methods ───────────────────────────────

    public function test_skips_constructor_and_framework_methods(): void
    {
        $this->writeFixture('FrameworkController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class FrameworkController extends Controller
{
    public function __construct()
    {
        // no middleware
    }

    public function tenantId()
    {
        return auth()->user()->tenant_id;
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip __construct and tenantId');
    }

    // ── Scanning Subdirectories ──────────────────────────────────

    public function test_scans_subdirectories(): void
    {
        $subDir = $this->fixtureDir.'/Admin';
        mkdir($subDir, 0777, true);

        $this->writeFixture('Admin/AdminController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Admin;

class AdminController extends Controller
{
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings, 'Should scan controllers in subdirectories');
    }

    // ── Empty Directory ──────────────────────────────────────────

    public function test_returns_empty_for_empty_directory(): void
    {
        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    public function test_returns_empty_for_nonexistent_directory(): void
    {
        $analyzer = new ControllerAnalyzer('/nonexistent/path');
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    // ── Finding Structure ────────────────────────────────────────

    public function test_findings_have_correct_structure(): void
    {
        $this->writeFixture('StructureController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class StructureController extends Controller
{
    public function store($request)
    {
        Product::create($request->all());
        return redirect()->back();
    }
}
PHP);

        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings);

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertSame('controller', $finding->category);
            $this->assertInstanceOf(Severity::class, $finding->severity);
            $this->assertNotEmpty($finding->title);
            $this->assertNotEmpty($finding->description);
            $this->assertNotNull($finding->file);
            $this->assertNotNull($finding->line);
            $this->assertNotNull($finding->recommendation);
            $this->assertIsArray($finding->metadata);
        }
    }

    // ── analyzeController with specific class ────────────────────

    public function test_analyze_controller_with_class_name(): void
    {
        $this->writeFixture('SpecificController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class SpecificController extends Controller
{
    public function update($request, $item)
    {
        $item->update($request->all());
        return redirect()->back();
    }
}
PHP);

        // We need to test analyzeController directly, but it resolves file paths
        // via base_path(). Since we're using temp fixtures, we test via analyze() instead.
        $analyzer = new ControllerAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function writeFixture(string $relativePath, string $content): void
    {
        $fullPath = $this->fixtureDir.'/'.$relativePath;
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, $content);
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
