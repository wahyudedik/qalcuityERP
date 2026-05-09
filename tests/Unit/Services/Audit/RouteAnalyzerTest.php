<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\RouteAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for RouteAnalyzer.
 *
 * Uses temporary route fixture files and controller stubs to test:
 * - Route permission matrix generation
 * - Unprotected data-modifying route detection
 * - Orphaned route detection (missing controller methods)
 *
 * Validates: Requirements 5.1, 5.2, 5.7
 */
class RouteAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    private string $routeDir;

    private string $controllerDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir().'/route_analyzer_test_'.uniqid();
        $this->routeDir = $this->fixtureDir.'/routes';
        $this->controllerDir = $this->fixtureDir.'/app/Http/Controllers';
        mkdir($this->routeDir, 0777, true);
        mkdir($this->controllerDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_route(): void
    {
        $analyzer = $this->makeAnalyzer();
        $this->assertSame('route', $analyzer->category());
    }

    // ── Permission Matrix ─────────────────────────────────────────

    public function test_permission_matrix_parses_basic_routes(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:inventory,create');
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->getRoutePermissionMatrix();

        $this->assertCount(2, $matrix);

        $getRoute = $this->findRoute($matrix, 'GET', 'products');
        $this->assertNotNull($getRoute);
        $this->assertSame('App\Http\Controllers\ProductController@index', $getRoute['action']);
        $this->assertSame('products.index', $getRoute['name']);

        $postRoute = $this->findRoute($matrix, 'POST', 'products');
        $this->assertNotNull($postRoute);
        $this->assertSame('App\Http\Controllers\ProductController@store', $postRoute['action']);
        $this->assertContains('permission:inventory,create', $postRoute['middleware']);
    }

    public function test_permission_matrix_inherits_group_middleware(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:inventory,create');
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->getRoutePermissionMatrix();

        $this->assertCount(1, $matrix);
        $route = $matrix[0];
        $this->assertContains('auth', $route['middleware']);
        $this->assertContains('verified', $route['middleware']);
        $this->assertContains('permission:inventory,create', $route['middleware']);
    }

    public function test_permission_matrix_handles_role_middleware(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('/admin/users', [AdminController::class, 'store'])->middleware('role:admin,manager');
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->getRoutePermissionMatrix();

        $this->assertCount(1, $matrix);
        $this->assertContains('role:admin,manager', $matrix[0]['middleware']);
        $this->assertContains('auth', $matrix[0]['middleware']);
    }

    public function test_permission_matrix_handles_closure_routes(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->getRoutePermissionMatrix();

        $this->assertCount(1, $matrix);
        $this->assertSame('Closure', $matrix[0]['action']);
    }

    public function test_permission_matrix_parses_multiple_route_files(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
PHP);

        $this->writeRouteFile('api.php', <<<'PHP'
<?php

use App\Http\Controllers\Api\ApiProductController;
use Illuminate\Support\Facades\Route;

Route::get('/api/products', [ApiProductController::class, 'index']);
PHP);

        $analyzer = $this->makeAnalyzer(['web.php', 'api.php']);
        $matrix = $analyzer->getRoutePermissionMatrix();

        $this->assertCount(2, $matrix);
    }

    // ── Unprotected Routes ────────────────────────────────────────

    public function test_detects_unprotected_post_route(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/products', [ProductController::class, 'store'])->name('products.store');
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertNotEmpty($findings);
        $finding = $findings[0];
        $this->assertSame('route', $finding->category);
        $this->assertSame(Severity::Critical, $finding->severity);
        $this->assertStringContains('Unprotected POST route', $finding->title);
        $this->assertFalse($finding->metadata['has_auth']);
    }

    public function test_detects_unprotected_delete_route_with_auth(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertNotEmpty($findings);
        $finding = $findings[0];
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertTrue($finding->metadata['has_auth']);
    }

    public function test_does_not_flag_protected_route(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->middleware('permission:inventory,create');
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertEmpty($findings, 'Should not flag route with permission middleware');
    }

    public function test_does_not_flag_route_with_role_middleware(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::post('/admin/settings', [AdminController::class, 'update'])->middleware('role:admin');
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertEmpty($findings, 'Should not flag route with role middleware');
    }

    public function test_does_not_flag_route_with_can_middleware(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->middleware('can:update,invoice');
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertEmpty($findings, 'Should not flag route with can middleware');
    }

    public function test_does_not_flag_get_routes(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertEmpty($findings, 'Should not flag GET routes');
    }

    public function test_does_not_flag_exempt_routes(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'store']);
Route::post('/register', [RegisterController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy']);
Route::post('/password/reset', [LoginController::class, 'resetPassword']);
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertEmpty($findings, 'Should not flag exempt routes (login, register, webhooks)');
    }

    public function test_detects_unprotected_put_and_patch_routes(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::put('/products/{id}', [ProductController::class, 'update']);
Route::patch('/products/{id}/status', [ProductController::class, 'updateStatus']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertCount(2, $findings);

        $methods = array_map(fn (AuditFinding $f) => $f->metadata['method'], $findings);
        $this->assertContains('PUT', $methods);
        $this->assertContains('PATCH', $methods);
    }

    // ── Orphaned Routes ───────────────────────────────────────────

    public function test_detects_missing_controller_file(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/missing', [\App\Http\Controllers\MissingController::class, 'index']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findOrphanedRoutes();

        $this->assertNotEmpty($findings);
        $finding = $findings[0];
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertStringContains('Missing controller', $finding->title);
    }

    public function test_detects_missing_controller_method(): void
    {
        // Create a controller file without the referenced method
        $this->writeControllerFile('ProductController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }
}
PHP);

        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findOrphanedRoutes();

        $this->assertNotEmpty($findings);
        $finding = $findings[0];
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertStringContains('Missing method', $finding->title);
        $this->assertStringContains('store', $finding->title);
    }

    public function test_does_not_flag_existing_controller_method(): void
    {
        $this->writeControllerFile('ProductController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function store($request)
    {
        Product::create($request->all());
        return redirect()->back();
    }
}
PHP);

        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findOrphanedRoutes();

        $this->assertEmpty($findings, 'Should not flag routes with existing controller methods');
    }

    public function test_does_not_flag_closure_routes_as_orphaned(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findOrphanedRoutes();

        $this->assertEmpty($findings, 'Should not flag closure routes as orphaned');
    }

    // ── Full Analyze ──────────────────────────────────────────────

    public function test_analyze_returns_combined_findings(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/products', [ProductController::class, 'store']);
Route::get('/missing', [\App\Http\Controllers\MissingController::class, 'index']);
PHP);

        $this->writeControllerFile('ProductController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function store($request)
    {
        return redirect()->back();
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        // Should have at least one unprotected route finding and one orphaned route finding
        $unprotected = array_filter($findings, fn (AuditFinding $f) => str_contains($f->title, 'Unprotected'));
        $orphaned = array_filter($findings, fn (AuditFinding $f) => str_contains($f->title, 'Missing controller'));

        $this->assertNotEmpty($unprotected, 'Should detect unprotected POST route');
        $this->assertNotEmpty($orphaned, 'Should detect missing controller');
    }

    // ── Edge Cases ────────────────────────────────────────────────

    public function test_returns_empty_for_nonexistent_route_files(): void
    {
        $analyzer = new RouteAnalyzer(
            ['/nonexistent/routes/web.php'],
            $this->fixtureDir
        );

        $findings = $analyzer->analyze();
        $this->assertSame([], $findings);
    }

    public function test_returns_empty_for_empty_route_file(): void
    {
        $this->writeRouteFile('web.php', "<?php\n");

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    public function test_handles_nested_middleware_groups(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::middleware(['tenant.isolation'])->group(function () {
        Route::post('/products', [ProductController::class, 'store'])->middleware('permission:inventory,create');
    });
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->getRoutePermissionMatrix();

        $this->assertCount(1, $matrix);
        $route = $matrix[0];
        $this->assertContains('auth', $route['middleware']);
        $this->assertContains('tenant.isolation', $route['middleware']);
        $this->assertContains('permission:inventory,create', $route['middleware']);
    }

    public function test_handles_prefix_with_middleware_group(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ManufacturingController;
use Illuminate\Support\Facades\Route;

Route::prefix('manufacturing')->middleware(['role:admin,manager,gudang', 'tenant.isolation'])->group(function () {
    Route::post('/bom', [ManufacturingController::class, 'storeBom'])->middleware('permission:manufacturing,create');
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->getRoutePermissionMatrix();

        $this->assertCount(1, $matrix);
        $route = $matrix[0];
        $this->assertContains('role:admin,manager,gudang', $route['middleware']);
        $this->assertContains('tenant.isolation', $route['middleware']);
        $this->assertContains('permission:manufacturing,create', $route['middleware']);
    }

    public function test_finding_structure_is_correct(): void
    {
        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/products', [ProductController::class, 'store']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findUnprotectedRoutes();

        $this->assertNotEmpty($findings);
        $finding = $findings[0];

        $this->assertInstanceOf(AuditFinding::class, $finding);
        $this->assertSame('route', $finding->category);
        $this->assertInstanceOf(Severity::class, $finding->severity);
        $this->assertNotEmpty($finding->title);
        $this->assertNotEmpty($finding->description);
        $this->assertNotNull($finding->recommendation);
        $this->assertIsArray($finding->metadata);
        $this->assertArrayHasKey('method', $finding->metadata);
        $this->assertArrayHasKey('uri', $finding->metadata);
        $this->assertArrayHasKey('action', $finding->metadata);
        $this->assertArrayHasKey('middleware', $finding->metadata);
        $this->assertArrayHasKey('has_auth', $finding->metadata);
    }

    public function test_detects_orphaned_route_in_subdirectory_controller(): void
    {
        $adminDir = $this->controllerDir.'/Admin';
        mkdir($adminDir, 0777, true);

        file_put_contents($adminDir.'/UserController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Admin;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }
}
PHP);

        $this->writeRouteFile('web.php', <<<'PHP'
<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/users', [UserController::class, 'index']);
Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->findOrphanedRoutes();

        $this->assertNotEmpty($findings);
        $finding = $findings[0];
        $this->assertStringContains('Missing method', $finding->title);
        $this->assertStringContains('destroy', $finding->title);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function makeAnalyzer(?array $routeFileNames = null): RouteAnalyzer
    {
        $routeFiles = $routeFileNames
            ? array_map(fn ($name) => $this->routeDir.'/'.$name, $routeFileNames)
            : [$this->routeDir.'/web.php'];

        return new RouteAnalyzer($routeFiles, $this->fixtureDir);
    }

    private function writeRouteFile(string $filename, string $content): void
    {
        file_put_contents($this->routeDir.'/'.$filename, $content);
    }

    private function writeControllerFile(string $relativePath, string $content): void
    {
        $fullPath = $this->controllerDir.'/'.$relativePath;
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, $content);
    }

    /**
     * Find a route in the matrix by method and URI.
     */
    private function findRoute(array $matrix, string $method, string $uri): ?array
    {
        foreach ($matrix as $route) {
            if ($route['method'] === $method && $route['uri'] === $uri) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Custom assertion: string contains substring.
     */
    private function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            $message ?: "Failed asserting that '{$haystack}' contains '{$needle}'"
        );
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
