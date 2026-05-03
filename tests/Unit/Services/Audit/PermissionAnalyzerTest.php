<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\PermissionAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PermissionAnalyzer.
 *
 * Uses temporary fixture files to test detection of:
 * - Modules without permission-protected routes
 * - Routes referencing undefined modules
 * - Critical models lacking authorization policies
 * - Role escalation paths and inconsistent role checks
 * - Healthcare RBAC integration issues
 * - Permission matrix generation
 *
 * Validates: Requirements 5.1, 5.2, 5.3, 5.6, 5.8
 */
class PermissionAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir() . '/permission_analyzer_test_' . uniqid();
        mkdir($this->fixtureDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_permissions(): void
    {
        $analyzer = $this->makeAnalyzer();
        $this->assertSame('permissions', $analyzer->category());
    }

    // ── Module Route Coverage (Requirement 5.2) ──────────────────

    public function test_detects_module_with_no_permission_protected_routes(): void
    {
        $this->writePermissionService([
            'sales' => ['view', 'create', 'edit', 'delete'],
            'inventory' => ['view', 'create', 'edit', 'delete'],
        ]);

        // Route file only has permission:sales, not inventory
        $this->writeRouteFile('routes/web.php', <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:sales,view'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index']);
});

Route::get('/inventory', [InventoryController::class, 'index']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModuleRouteCoverage();

        $inventoryFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'inventory')
        );

        $this->assertNotEmpty($inventoryFindings);
        $finding = reset($inventoryFindings);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertSame('module_route_coverage', $finding->metadata['check']);
    }

    public function test_detects_route_referencing_undefined_module(): void
    {
        $this->writePermissionService([
            'sales' => ['view', 'create'],
        ]);

        $this->writeRouteFile('routes/web.php', <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:sales,view'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index']);
});

Route::middleware(['auth', 'permission:nonexistent,view'])->group(function () {
    Route::get('/nonexistent', [NonexistentController::class, 'index']);
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModuleRouteCoverage();

        $undefinedFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'nonexistent')
        );

        $this->assertNotEmpty($undefinedFindings);
        $finding = reset($undefinedFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertSame('route_references_undefined', $finding->metadata['direction']);
    }

    public function test_no_findings_when_all_modules_have_routes(): void
    {
        $this->writePermissionService([
            'sales' => ['view', 'create'],
        ]);

        $this->writeRouteFile('routes/web.php', <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:sales,view'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index']);
    Route::post('/sales', [SalesController::class, 'store'])->middleware('permission:sales,create');
});
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModuleRouteCoverage();

        $this->assertEmpty($findings);
    }

    public function test_reports_missing_permission_service_file(): void
    {
        // Don't create PermissionService file
        $this->writeRouteFile('routes/web.php', "<?php\n");

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModuleRouteCoverage();

        $this->assertNotEmpty($findings);
        $this->assertStringContainsString('Cannot read PermissionService', $findings[0]->title);
    }

    // ── Policy Gaps (Requirement 5.6) ────────────────────────────

    public function test_detects_critical_model_without_policy(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        // Create only one policy
        $this->writeFixture('policies/CompanyGroupPolicy.php', <<<'PHP'
<?php

namespace App\Policies;

class CompanyGroupPolicy
{
    public function viewAny($user): bool { return true; }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkPolicyGaps();

        // Should flag all critical models except those with policies
        $this->assertNotEmpty($findings);

        $modelNames = array_map(
            fn(AuditFinding $f) => $f->metadata['model'],
            $findings
        );

        // Invoice, SalesOrder, etc. should be flagged
        $this->assertContains('Invoice', $modelNames);
        $this->assertContains('SalesOrder', $modelNames);
        $this->assertContains('JournalEntry', $modelNames);
    }

    public function test_does_not_flag_model_with_existing_policy(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        // Create InvoicePolicy
        $this->writeFixture('policies/InvoicePolicy.php', <<<'PHP'
<?php

namespace App\Policies;

class InvoicePolicy
{
    public function viewAny($user): bool { return true; }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkPolicyGaps();

        $invoiceFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => $f->metadata['model'] === 'Invoice'
        );

        $this->assertEmpty($invoiceFindings);
    }

    public function test_policy_gaps_with_empty_policy_directory(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        // Don't create any policies
        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkPolicyGaps();

        // All 11 critical models should be flagged
        $this->assertCount(11, $findings);
    }

    // ── Role Consistency (Requirement 5.8) ───────────────────────

    public function test_detects_role_escalation_gap(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        // Controller that checks admin and staff but skips manager
        $this->writeFixture('controllers/ReportController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ReportController
{
    public function sensitiveReport()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return $this->adminReport();
        }
        if ($user->isStaff()) {
            return $this->staffReport();
        }
        abort(403);
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRoleConsistency();

        $escalationFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'role escalation')
        );

        $this->assertNotEmpty($escalationFindings);
        $finding = reset($escalationFindings);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertContains('manager', $finding->metadata['missing_roles']);
    }

    public function test_no_escalation_finding_for_single_role_check(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        $this->writeFixture('controllers/DashboardController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class DashboardController
{
    public function index()
    {
        if (auth()->user()->isAdmin()) {
            return view('admin.dashboard');
        }
        return view('dashboard');
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRoleConsistency();

        $escalationFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'role escalation')
        );

        $this->assertEmpty($escalationFindings);
    }

    public function test_detects_direct_role_comparison(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        $this->writeFixture('controllers/UserController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class UserController
{
    public function promote($userId)
    {
        $user = User::find($userId);
        if ($user->role === 'admin') {
            abort(403, 'Cannot promote admin');
        }
        $user->role = 'manager';
        $user->save();
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRoleConsistency();

        $directFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Direct role comparison')
        );

        $this->assertNotEmpty($directFindings);
        $finding = reset($directFindings);
        $this->assertSame(Severity::Low, $finding->severity);
        $this->assertSame('admin', $finding->metadata['role_compared']);
    }

    public function test_no_findings_for_clean_controller(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        $this->writeFixture('controllers/CleanController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class CleanController
{
    public function index()
    {
        return view('clean.index');
    }

    public function store()
    {
        // No role checks — relies on middleware
        return redirect()->back();
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRoleConsistency();

        $this->assertEmpty($findings);
    }

    public function test_skips_abstract_classes(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        $this->writeFixture('controllers/BaseController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

abstract class BaseController
{
    public function checkAccess()
    {
        if (auth()->user()->role === 'admin') {
            return true;
        }
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRoleConsistency();

        $this->assertEmpty($findings);
    }

    // ── Permission Matrix ────────────────────────────────────────

    public function test_generates_permission_matrix(): void
    {
        $this->writePermissionService(['sales' => ['view', 'create']]);

        $this->writeRouteFile('routes/web.php', <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:sales,view'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index']);
    Route::post('/sales', [SalesController::class, 'store'])->middleware('permission:sales,create');
});

Route::get('/public', [PublicController::class, 'index']);
PHP);

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->generatePermissionMatrix();

        $this->assertNotEmpty($matrix);

        // Find the GET /sales entry
        $salesGet = array_filter($matrix, fn($e) => $e['uri'] === 'sales' && $e['method'] === 'GET');
        $this->assertNotEmpty($salesGet);
        $entry = reset($salesGet);
        $this->assertSame('sales', $entry['module']);
        $this->assertSame('view', $entry['action']);

        // Find the POST /sales entry
        $salesPost = array_filter($matrix, fn($e) => $e['uri'] === 'sales' && $e['method'] === 'POST');
        $this->assertNotEmpty($salesPost);
        $postEntry = reset($salesPost);
        $this->assertSame('sales', $postEntry['module']);
        $this->assertSame('create', $postEntry['action']);

        // Public route should have no module
        $publicRoute = array_filter($matrix, fn($e) => $e['uri'] === 'public');
        $this->assertNotEmpty($publicRoute);
        $publicEntry = reset($publicRoute);
        $this->assertNull($publicEntry['module']);
    }

    public function test_permission_matrix_with_no_route_files(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        // Don't create route files

        $analyzer = $this->makeAnalyzer();
        $matrix = $analyzer->generatePermissionMatrix();

        $this->assertEmpty($matrix);
    }

    // ── Healthcare RBAC Integration (Requirement 5.3) ────────────

    public function test_detects_healthcare_rbac_role_naming_conflict(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        // RBAC middleware with 'superadmin' (no underscore) vs main 'super_admin'
        $this->writeFixture('middleware/RBACMiddleware.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

class RBACMiddleware
{
    protected array $rolePermissions = [
        'superadmin' => ['*'],
        'admin' => ['healthcare.*'],
        'doctor' => ['healthcare.patients.view'],
    ];

    public function handle($request, $next, string $permission)
    {
        return $next($request);
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkHealthcareRbacIntegration();

        $conflictFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'conflicts')
        );

        $this->assertNotEmpty($conflictFindings);
        $finding = reset($conflictFindings);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertSame('superadmin', $finding->metadata['rbac_role']);
        $this->assertSame('super_admin', $finding->metadata['main_role']);
    }

    public function test_detects_rbac_not_integrated_with_permission_service(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        $this->writeFixture('middleware/RBACMiddleware.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

class RBACMiddleware
{
    protected array $rolePermissions = [
        'doctor' => ['healthcare.patients.view'],
        'nurse' => ['healthcare.vitals.*'],
    ];

    public function handle($request, $next, string $permission)
    {
        $user = $request->user();
        // Own permission logic, standalone
        return $next($request);
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkHealthcareRbacIntegration();

        $integrationFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'not integrated')
        );

        $this->assertNotEmpty($integrationFindings);
        $finding = reset($integrationFindings);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertFalse($finding->metadata['uses_permission_service']);
    }

    public function test_no_findings_when_rbac_middleware_missing(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', "<?php\n");
        // Don't create RBAC middleware

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkHealthcareRbacIntegration();

        $this->assertEmpty($findings);
    }

    // ── Full analyze() Integration ────────────────────────────────

    public function test_analyze_aggregates_all_checks(): void
    {
        $this->writePermissionService([
            'sales' => ['view', 'create'],
            'inventory' => ['view'],
        ]);

        $this->writeRouteFile('routes/web.php', <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:sales,view'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index']);
});
PHP);

        // Controller with role issues
        $this->writeFixture('controllers/TestController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class TestController
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            return 'admin';
        }
    }
}
PHP);

        // RBAC middleware
        $this->writeFixture('middleware/RBACMiddleware.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

class RBACMiddleware
{
    protected array $rolePermissions = [
        'doctor' => ['healthcare.patients.view'],
    ];

    public function handle($request, $next, string $permission)
    {
        return $next($request);
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        // Should have findings from multiple checks
        $checks = array_unique(array_map(fn($f) => $f->metadata['check'], $findings));

        // At minimum: module_route_coverage (inventory has no routes), policy_gaps, role_consistency
        $this->assertContains('module_route_coverage', $checks);
        $this->assertContains('policy_gaps', $checks);
        $this->assertContains('role_consistency', $checks);
    }

    // ── Finding Structure ─────────────────────────────────────────

    public function test_findings_have_correct_structure(): void
    {
        $this->writePermissionService([
            'sales' => ['view'],
            'orphaned' => ['view'],
        ]);
        $this->writeRouteFile('routes/web.php', "<?php\n");

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModuleRouteCoverage();

        $this->assertNotEmpty($findings);

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertSame('permissions', $finding->category);
            $this->assertInstanceOf(Severity::class, $finding->severity);
            $this->assertNotEmpty($finding->title);
            $this->assertNotEmpty($finding->description);
            $this->assertNotNull($finding->recommendation);
            $this->assertIsArray($finding->metadata);
            $this->assertArrayHasKey('check', $finding->metadata);
        }
    }

    // ── Empty/Nonexistent Directories ─────────────────────────────

    public function test_returns_empty_for_empty_directories(): void
    {
        $this->writePermissionService(['sales' => ['view']]);
        $this->writeRouteFile('routes/web.php', <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;
Route::middleware(['auth', 'permission:sales,view'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index']);
});
PHP);

        $analyzer = $this->makeAnalyzer();

        // No controllers => no role consistency findings
        $this->assertSame([], $analyzer->checkRoleConsistency());
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function makeAnalyzer(): PermissionAnalyzer
    {
        return new PermissionAnalyzer(
            permissionServiceFile: $this->fixtureDir . '/services/PermissionService.php',
            policyPath: $this->fixtureDir . '/policies',
            controllerPath: $this->fixtureDir . '/controllers',
            rbacMiddlewareFile: $this->fixtureDir . '/middleware/RBACMiddleware.php',
            routeFiles: [$this->fixtureDir . '/routes/web.php'],
            basePath: $this->fixtureDir,
        );
    }

    /**
     * Write a minimal PermissionService fixture with given modules.
     *
     * @param array<string, string[]> $modules
     */
    private function writePermissionService(array $modules): void
    {
        $moduleEntries = [];
        foreach ($modules as $name => $actions) {
            $actionList = implode("', '", $actions);
            $moduleEntries[] = "        '{$name}' => ['{$actionList}']";
        }
        $modulesStr = implode(",\n", $moduleEntries);

        $content = <<<PHP
<?php

namespace App\Services;

class PermissionService
{
    public const MODULES = [
{$modulesStr},
    ];
}
PHP;

        $this->writeFixture('services/PermissionService.php', $content);
    }

    private function writeRouteFile(string $relativePath, string $content): void
    {
        $this->writeFixture($relativePath, $content);
    }

    private function writeFixture(string $relativePath, string $content): void
    {
        $fullPath = $this->fixtureDir . '/' . $relativePath;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, $content);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
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
