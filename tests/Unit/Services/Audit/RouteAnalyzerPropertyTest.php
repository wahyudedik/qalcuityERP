<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\RouteAnalyzer;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-Based Tests for RouteAnalyzer.
 *
 * Feature: comprehensive-erp-audit
 *
 * These tests generate random route fixture files with various combinations
 * of HTTP methods, middleware configurations, and URI patterns, then run
 * the RouteAnalyzer against them to verify route permission enforcement.
 */
class RouteAnalyzerPropertyTest extends TestCase
{
    use TestTrait;

    private string $tempDir;

    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/route_analyzer_test_'.uniqid();
        mkdir($this->tempDir.'/routes', 0777, true);
        $this->basePath = $this->tempDir;
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Property 4: Route Permission Enforcement ────────────────

    /**
     * Property 4: Route Permission Enforcement
     *
     * For any registered route with a data-modifying HTTP method
     * (POST, PUT, PATCH, DELETE), if the route's middleware stack does NOT
     * include permission/role/can middleware, the analyzer SHALL produce a finding.
     * If the route DOES have permission/role/can middleware, no finding should
     * be produced. GET routes should never produce unprotected route findings.
     * Exempt routes (login, register, webhooks, etc.) should never produce findings.
     *
     * **Validates: Requirements 1.7, 5.1, 5.2**
     *
     * // Feature: comprehensive-erp-audit, Property 4: Route Permission Enforcement
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_4_route_permission_enforcement(): void
    {
        $this->forAll(
            Generators::elements('GET', 'POST', 'PUT', 'PATCH', 'DELETE'),
            Generators::elements(
                'none',
                'auth_only',
                'permission',
                'role',
                'can'
            ),
            Generators::elements(
                'products',
                'orders',
                'invoices/{id}',
                'customers',
                'employees',
                'reports/sales',
                'settings/general',
                'inventory/transfer',
                'accounting/journals',
                'assets/{id}/depreciate'
            ),
            Generators::bool() // whether to use exempt URI instead
        )->then(function (string $httpMethod, string $middlewareConfig, string $regularUri, bool $useExemptUri) {
            $exemptUris = [
                'login',
                'logout',
                'register',
                'password/reset',
                'forgot-password',
                'webhooks/payment',
                'api/webhooks/stripe',
                'oauth/callback',
                'auth/google',
                'sanctum/csrf-cookie',
            ];

            $uri = $useExemptUri
                ? $exemptUris[array_rand($exemptUris)]
                : $regularUri;

            $routeFileContent = $this->generateRouteFile($httpMethod, $uri, $middlewareConfig);
            $routeFilePath = $this->tempDir.'/routes/web.php';
            file_put_contents($routeFilePath, $routeFileContent);

            $analyzer = new RouteAnalyzer([$routeFilePath], $this->basePath);
            $findings = $analyzer->findUnprotectedRoutes();

            $isDataModifying = in_array($httpMethod, ['POST', 'PUT', 'PATCH', 'DELETE'], true);
            $isExempt = $useExemptUri;
            $hasPermissionMiddleware = in_array($middlewareConfig, ['permission', 'role', 'can'], true);

            if (! $isDataModifying) {
                // GET routes should never produce unprotected route findings
                $this->assertEmpty(
                    $findings,
                    'GET route should NEVER produce unprotected route findings. '
                        ."method={$httpMethod}, uri={$uri}, middleware={$middlewareConfig}"
                );
            } elseif ($isExempt) {
                // Exempt routes should never produce findings regardless of middleware
                $this->assertEmpty(
                    $findings,
                    'Exempt route should NEVER produce findings. '
                        ."method={$httpMethod}, uri={$uri}, middleware={$middlewareConfig}"
                );
            } elseif ($hasPermissionMiddleware) {
                // Data-modifying route WITH permission/role/can middleware → no finding
                $this->assertEmpty(
                    $findings,
                    "Route with {$middlewareConfig} middleware should NOT be flagged. "
                        ."method={$httpMethod}, uri={$uri}"
                );
            } else {
                // Data-modifying route WITHOUT permission middleware → MUST produce a finding
                $this->assertNotEmpty(
                    $findings,
                    'Data-modifying route WITHOUT permission middleware MUST be flagged. '
                        ."method={$httpMethod}, uri={$uri}, middleware={$middlewareConfig}"
                );

                $finding = $findings[0];
                $this->assertInstanceOf(AuditFinding::class, $finding);
                $this->assertSame('route', $finding->category);

                // Verify severity: no auth at all → Critical, auth only → Medium
                if ($middlewareConfig === 'none') {
                    $this->assertSame(
                        Severity::Critical,
                        $finding->severity,
                        'Route with NO middleware should be Critical severity. '
                            ."method={$httpMethod}, uri={$uri}"
                    );
                } elseif ($middlewareConfig === 'auth_only') {
                    $this->assertSame(
                        Severity::Medium,
                        $finding->severity,
                        'Route with auth-only middleware should be Medium severity. '
                            ."method={$httpMethod}, uri={$uri}"
                    );
                }

                // Verify finding metadata contains the method and URI
                $this->assertSame($httpMethod, $finding->metadata['method']);
                $this->assertStringContainsString($httpMethod, $finding->title);
            }

            @unlink($routeFilePath);
        });
    }

    // ── Route File Generators ───────────────────────────────────

    /**
     * Generate a route file with a single route definition.
     */
    private function generateRouteFile(string $method, string $uri, string $middlewareConfig): string
    {
        $laravelMethod = strtolower($method);
        $middlewareChain = $this->buildMiddlewareChain($middlewareConfig);
        $controllerAction = "function () { return 'ok'; }";

        $routeLine = "Route::{$laravelMethod}('{$uri}', {$controllerAction})";

        if ($middlewareChain !== '') {
            $routeLine .= "->middleware({$middlewareChain})";
        }

        $routeLine .= ';';

        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

{$routeLine}
PHP;
    }

    /**
     * Build the middleware argument string for a route definition.
     */
    private function buildMiddlewareChain(string $config): string
    {
        return match ($config) {
            'none' => '',
            'auth_only' => "'auth'",
            'permission' => "['auth', 'permission:inventory,create']",
            'role' => "['auth', 'role:admin,manager']",
            'can' => "['auth', 'can:manage-products']",
        };
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
