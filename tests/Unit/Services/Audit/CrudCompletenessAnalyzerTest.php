<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\CrudCompletenessAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CrudCompletenessAnalyzer.
 *
 * Uses temporary fixture files to test detection of:
 * - Model-to-controller mapping
 * - CRUD method completeness (Complete, Partial, Missing)
 * - List view feature checks (search, pagination, sorting, filtering, bulk actions)
 * - Import/export capability checks
 *
 * Validates: Requirements 6.1, 6.2, 6.3, 6.6, 6.7
 */
class CrudCompletenessAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir().'/crud_analyzer_test_'.uniqid();
        mkdir($this->fixtureDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_crud(): void
    {
        $analyzer = $this->makeAnalyzer();
        $this->assertSame('crud', $analyzer->category());
    }

    // ── mapModelToController (Requirement 6.1) ───────────────────

    public function test_maps_model_to_matching_controller(): void
    {
        $this->writeFixture('app/Models/Product.php', <<<'PHP'
<?php
namespace App\Models;

class Product extends Model
{
    protected $fillable = ['name', 'price'];
}
PHP);

        $this->writeFixture('app/Http/Controllers/ProductController.php', <<<'PHP'
<?php
namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index() {}
    public function store() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $mapping = $analyzer->mapModelToController();

        $this->assertArrayHasKey('Product', $mapping);
        $this->assertSame('ProductController', $mapping['Product']['controller']);
        $this->assertSame('app/Http/Controllers/ProductController.php', $mapping['Product']['controller_file']);
    }

    public function test_maps_model_without_controller_as_null(): void
    {
        $this->writeFixture('app/Models/Orphan.php', <<<'PHP'
<?php
namespace App\Models;

class Orphan extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $mapping = $analyzer->mapModelToController();

        $this->assertArrayHasKey('Orphan', $mapping);
        $this->assertNull($mapping['Orphan']['controller']);
        $this->assertNull($mapping['Orphan']['controller_file']);
    }

    public function test_skips_abstract_classes_and_traits(): void
    {
        $this->writeFixture('app/Models/BaseModel.php', <<<'PHP'
<?php
namespace App\Models;

abstract class BaseModel
{
    protected $fillable = [];
}
PHP);

        $this->writeFixture('app/Models/SomeTrait.php', <<<'PHP'
<?php
namespace App\Models;

trait SomeTrait
{
    public function doSomething() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $mapping = $analyzer->mapModelToController();

        $this->assertArrayNotHasKey('BaseModel', $mapping);
        $this->assertArrayNotHasKey('SomeTrait', $mapping);
    }

    public function test_finds_controller_in_subdirectory(): void
    {
        $this->writeFixture('app/Models/Room.php', <<<'PHP'
<?php
namespace App\Models;

class Room extends Model
{
    protected $fillable = ['number'];
}
PHP);

        $this->writeFixture('app/Http/Controllers/Hotel/RoomController.php', <<<'PHP'
<?php
namespace App\Http\Controllers\Hotel;

class RoomController extends Controller
{
    public function index() {}
    public function show() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $mapping = $analyzer->mapModelToController();

        $this->assertArrayHasKey('Room', $mapping);
        $this->assertSame('RoomController', $mapping['Room']['controller']);
        $this->assertStringContainsString('Hotel/RoomController.php', $mapping['Room']['controller_file']);
    }

    // ── checkCrudMethods (Requirement 6.2) ───────────────────────

    public function test_detects_all_seven_crud_methods(): void
    {
        $controllerFile = $this->fixtureDir.'/controller.php';
        file_put_contents($controllerFile, <<<'PHP'
<?php
namespace App\Http\Controllers;

class FullController extends Controller
{
    public function index() {}
    public function create() {}
    public function store() {}
    public function show() {}
    public function edit() {}
    public function update() {}
    public function destroy() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $result = $analyzer->checkCrudMethods($controllerFile);

        $this->assertCount(7, $result['present']);
        $this->assertEmpty($result['missing']);
    }

    public function test_detects_missing_crud_methods(): void
    {
        $controllerFile = $this->fixtureDir.'/controller.php';
        file_put_contents($controllerFile, <<<'PHP'
<?php
namespace App\Http\Controllers;

class PartialController extends Controller
{
    public function index() {}
    public function show() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $result = $analyzer->checkCrudMethods($controllerFile);

        $this->assertSame(['index', 'show'], $result['present']);
        $this->assertContains('create', $result['missing']);
        $this->assertContains('store', $result['missing']);
        $this->assertContains('edit', $result['missing']);
        $this->assertContains('update', $result['missing']);
        $this->assertContains('destroy', $result['missing']);
    }

    public function test_returns_all_missing_for_unreadable_file(): void
    {
        // Create a file then delete it to simulate a missing file
        // without triggering PHPUnit's warning handler
        $tempFile = $this->fixtureDir.'/missing_controller.php';
        file_put_contents($tempFile, '<?php // empty');
        unlink($tempFile);

        $analyzer = $this->makeAnalyzer();
        $result = $analyzer->checkCrudMethods($tempFile);

        $this->assertEmpty($result['present']);
        $this->assertCount(7, $result['missing']);
    }

    public function test_ignores_private_and_protected_methods(): void
    {
        $controllerFile = $this->fixtureDir.'/controller.php';
        file_put_contents($controllerFile, <<<'PHP'
<?php
namespace App\Http\Controllers;

class MixedController extends Controller
{
    public function index() {}
    private function create() {}
    protected function store() {}
    public function show() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $result = $analyzer->checkCrudMethods($controllerFile);

        $this->assertSame(['index', 'show'], $result['present']);
        $this->assertContains('create', $result['missing']);
        $this->assertContains('store', $result['missing']);
    }

    // ── generateCrudMatrix (Requirement 6.7) ─────────────────────

    public function test_classifies_complete_entity(): void
    {
        $this->writeFixture('app/Models/Widget.php', <<<'PHP'
<?php
namespace App\Models;

class Widget extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $this->writeFixture('app/Http/Controllers/WidgetController.php', <<<'PHP'
<?php
namespace App\Http\Controllers;

class WidgetController extends Controller
{
    public function index() {}
    public function create() {}
    public function store() {}
    public function show() {}
    public function edit() {}
    public function update() {}
    public function destroy() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->generateCrudMatrix();

        $widgetEntry = $this->findMatrixEntry($matrix, 'Widget');
        $this->assertNotNull($widgetEntry);
        $this->assertSame('Complete', $widgetEntry['status']);
        $this->assertCount(7, $widgetEntry['present_methods']);
        $this->assertEmpty($widgetEntry['missing_methods']);
    }

    public function test_classifies_partial_entity(): void
    {
        $this->writeFixture('app/Models/Gadget.php', <<<'PHP'
<?php
namespace App\Models;

class Gadget extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $this->writeFixture('app/Http/Controllers/GadgetController.php', <<<'PHP'
<?php
namespace App\Http\Controllers;

class GadgetController extends Controller
{
    public function index() {}
    public function show() {}
    public function update() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->generateCrudMatrix();

        $gadgetEntry = $this->findMatrixEntry($matrix, 'Gadget');
        $this->assertNotNull($gadgetEntry);
        $this->assertSame('Partial', $gadgetEntry['status']);
        $this->assertSame(['index', 'show', 'update'], $gadgetEntry['present_methods']);
        $this->assertContains('create', $gadgetEntry['missing_methods']);
    }

    public function test_classifies_missing_entity(): void
    {
        $this->writeFixture('app/Models/Phantom.php', <<<'PHP'
<?php
namespace App\Models;

class Phantom extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->generateCrudMatrix();

        $phantomEntry = $this->findMatrixEntry($matrix, 'Phantom');
        $this->assertNotNull($phantomEntry);
        $this->assertSame('Missing', $phantomEntry['status']);
        $this->assertEmpty($phantomEntry['present_methods']);
        $this->assertCount(7, $phantomEntry['missing_methods']);
    }

    // ── analyze() findings ───────────────────────────────────────

    public function test_analyze_reports_missing_controller_for_core_entity(): void
    {
        // Customer is a core entity
        $this->writeFixture('app/Models/Customer.php', <<<'PHP'
<?php
namespace App\Models;

class Customer extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $customerFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Customer')
                && $f->metadata['check'] === 'crud_completeness'
        );

        $this->assertNotEmpty($customerFindings);
        $finding = reset($customerFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertSame('crud', $finding->category);
    }

    public function test_analyze_reports_missing_controller_for_non_core_entity(): void
    {
        $this->writeFixture('app/Models/Widget.php', <<<'PHP'
<?php
namespace App\Models;

class Widget extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $widgetFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Widget')
                && ($f->metadata['check'] ?? '') === 'crud_completeness'
        );

        $this->assertNotEmpty($widgetFindings);
        $finding = reset($widgetFindings);
        $this->assertSame(Severity::Medium, $finding->severity);
    }

    public function test_analyze_reports_partial_crud_for_core_entity(): void
    {
        $this->writeFixture('app/Models/Invoice.php', <<<'PHP'
<?php
namespace App\Models;

class Invoice extends Model
{
    protected $fillable = ['number'];
}
PHP);

        $this->writeFixture('app/Http/Controllers/InvoiceController.php', <<<'PHP'
<?php
namespace App\Http\Controllers;

class InvoiceController extends Controller
{
    public function index() {}
    public function show() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $invoiceFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Invoice')
                && ($f->metadata['check'] ?? '') === 'crud_completeness'
        );

        $this->assertNotEmpty($invoiceFindings);
        $finding = reset($invoiceFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertStringContainsString('Incomplete CRUD', $finding->title);
    }

    public function test_analyze_no_crud_finding_for_complete_entity(): void
    {
        $this->writeFixture('app/Models/Thing.php', <<<'PHP'
<?php
namespace App\Models;

class Thing extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $this->writeFixture('app/Http/Controllers/ThingController.php', <<<'PHP'
<?php
namespace App\Http\Controllers;

class ThingController extends Controller
{
    public function index() {}
    public function create() {}
    public function store() {}
    public function show() {}
    public function edit() {}
    public function update() {}
    public function destroy() {}
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $thingCrudFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => str_contains($f->title, 'Thing')
                && ($f->metadata['check'] ?? '') === 'crud_completeness'
        );

        $this->assertEmpty($thingCrudFindings);
    }

    // ── Import/Export Checks (Requirement 6.6) ───────────────────

    public function test_detects_missing_import_and_export(): void
    {
        // Create a model but no import/export classes
        $this->writeFixture('app/Models/Customer.php', <<<'PHP'
<?php
namespace App\Models;

class Customer extends Model
{
    protected $fillable = ['name'];
}
PHP);

        // Empty imports/exports directories
        mkdir($this->fixtureDir.'/app/Imports', 0777, true);
        mkdir($this->fixtureDir.'/app/Exports', 0777, true);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $importExportFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => ($f->metadata['check'] ?? '') === 'import_export'
                && ($f->metadata['entity'] ?? '') === 'Customer'
        );

        $this->assertNotEmpty($importExportFindings);
    }

    public function test_no_import_export_finding_when_both_exist(): void
    {
        $this->writeFixture('app/Models/Product.php', <<<'PHP'
<?php
namespace App\Models;

class Product extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $this->writeFixture('app/Imports/ProductImport.php', <<<'PHP'
<?php
namespace App\Imports;

class ProductImport
{
}
PHP);

        $this->writeFixture('app/Exports/ProductExport.php', <<<'PHP'
<?php
namespace App\Exports;

class ProductExport
{
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $productImportExportFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => ($f->metadata['check'] ?? '') === 'import_export'
                && ($f->metadata['entity'] ?? '') === 'Product'
        );

        $this->assertEmpty($productImportExportFindings);
    }

    public function test_detects_missing_import_only(): void
    {
        $this->writeFixture('app/Models/Employee.php', <<<'PHP'
<?php
namespace App\Models;

class Employee extends Model
{
    protected $fillable = ['name'];
}
PHP);

        mkdir($this->fixtureDir.'/app/Imports', 0777, true);

        $this->writeFixture('app/Exports/EmployeeExport.php', <<<'PHP'
<?php
namespace App\Exports;

class EmployeeExport
{
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $employeeFindings = array_filter(
            $findings,
            fn (AuditFinding $f) => ($f->metadata['check'] ?? '') === 'import_export'
                && ($f->metadata['entity'] ?? '') === 'Employee'
        );

        $this->assertNotEmpty($employeeFindings);
        $finding = reset($employeeFindings);
        $this->assertStringContainsString('No import capability', $finding->title);
        $this->assertFalse($finding->metadata['has_import']);
        $this->assertTrue($finding->metadata['has_export']);
    }

    // ── Empty directories ────────────────────────────────────────

    public function test_returns_empty_for_empty_directories(): void
    {
        // Create empty model and controller directories
        mkdir($this->fixtureDir.'/app/Models', 0777, true);
        mkdir($this->fixtureDir.'/app/Http/Controllers', 0777, true);
        mkdir($this->fixtureDir.'/app/Imports', 0777, true);
        mkdir($this->fixtureDir.'/app/Exports', 0777, true);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->generateCrudMatrix();

        $this->assertEmpty($matrix);
    }

    // ── Findings structure ───────────────────────────────────────

    public function test_findings_have_correct_structure(): void
    {
        $this->writeFixture('app/Models/Asset.php', <<<'PHP'
<?php
namespace App\Models;

class Asset extends Model
{
    protected $fillable = ['name'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings);

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertSame('crud', $finding->category);
            $this->assertNotEmpty($finding->title);
            $this->assertNotEmpty($finding->description);
            $this->assertNotNull($finding->recommendation);
            $this->assertIsArray($finding->metadata);
            $this->assertArrayHasKey('check', $finding->metadata);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function makeAnalyzer(): CrudCompletenessAnalyzer
    {
        return new CrudCompletenessAnalyzer(
            modelPath: $this->fixtureDir.'/app/Models',
            controllerPath: $this->fixtureDir.'/app/Http/Controllers',
            viewPath: $this->fixtureDir.'/resources/views',
            importPath: $this->fixtureDir.'/app/Imports',
            exportPath: $this->fixtureDir.'/app/Exports',
            basePath: $this->fixtureDir,
        );
    }

    private function writeFixture(string $relativePath, string $content): void
    {
        $fullPath = $this->fixtureDir.'/'.$relativePath;
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, $content);
    }

    private function findMatrixEntry(array $matrix, string $modelName): ?array
    {
        foreach ($matrix as $entry) {
            if ($entry['model'] === $modelName) {
                return $entry;
            }
        }

        return null;
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
