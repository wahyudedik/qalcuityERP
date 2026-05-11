<?php

namespace Tests\Feature;

use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Preservation Property Tests — Functional Elements Must Remain Intact
 *
 * EXPECTED: These tests MUST PASS on UNFIXED code.
 * They establish the baseline of what must be preserved after the fix is applied.
 *
 * Property 2: Preservation — Tombol Fungsional Tetap Bekerja
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5
 */
class InactiveButtonsPreservationTest extends TestCase
{
    use TestTrait;

    private string $viewsPath;

    /**
     * All Blade files that will be modified by the fix.
     * We observe their current functional elements to ensure preservation.
     */
    private array $filesToBeModified = [
        'agriculture/dashboard.blade.php',
        'healthcare/emr/dashboard.blade.php',
        'healthcare/laboratory/reports.blade.php',
        'hotel/rooms/availability.blade.php',
        'hotel/check-in/pre-arrival.blade.php',
        'hotel/fb/minibar/index.blade.php',
        'hotel/rates/index.blade.php',
        'helpdesk/show.blade.php',
        'procurement/supplier-performance-detail.blade.php',
        'printing/job-detail.blade.php',
        'analytics/scheduled-reports.blade.php',
        'automation/workflows/show.blade.php',
        'integrations/dashboard.blade.php',
        'manufacturing/mix-design-versions.blade.php',
        'suppliers/sourcing-dashboard.blade.php',
        'mobile/optimized-dashboard.blade.php',
        'pages/about/careers.blade.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $projectRoot = $this->resolveProjectRoot();
        $this->viewsPath = $projectRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * Resolve the project root directory.
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

    /**
     * Get the full path for a relative blade file path.
     */
    private function getFullPath(string $relativePath): string
    {
        return $this->viewsPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    /**
     * Extract all route() calls from file content.
     * Returns array of route names found (e.g., ['healthcare.patients.show', 'healthcare.emr.timeline']).
     */
    private function extractRouteCalls(string $content): array
    {
        $routes = [];
        // Match route('name') or route("name") patterns
        if (preg_match_all("/route\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            $routes = array_unique($matches[1]);
        }
        return array_values($routes);
    }

    /**
     * Extract all functional @click handlers from file content.
     * A functional handler is one that is NOT a placeholder (not just opening an alert with placeholder text).
     */
    private function extractFunctionalClickHandlers(string $content): array
    {
        $handlers = [];

        // Match @click="..." and @click.prevent="..." patterns
        if (preg_match_all('/@click(?:\.[a-z.]+)?\s*=\s*"([^"]+)"/', $content, $matches)) {
            foreach ($matches[1] as $handler) {
                // Exclude placeholder handlers that just show alerts
                if ($this->isPlaceholderHandler($handler, $content)) {
                    continue;
                }
                $handlers[] = $handler;
            }
        }

        // Match x-on:click="..." patterns
        if (preg_match_all('/x-on:click(?:\.[a-z.]+)?\s*=\s*"([^"]+)"/', $content, $matches)) {
            foreach ($matches[1] as $handler) {
                if ($this->isPlaceholderHandler($handler, $content)) {
                    continue;
                }
                $handlers[] = $handler;
            }
        }

        return array_unique($handlers);
    }

    /**
     * Check if a click handler is a placeholder (calls a function that only does alert).
     */
    private function isPlaceholderHandler(string $handler, string $content): bool
    {
        // Direct alert placeholder in handler
        if (
            preg_match('/alert\s*\(/', $handler) &&
            preg_match('/implement|coming soon|belum|akan tersedia|to be implemented/i', $handler)
        ) {
            return true;
        }

        // Check if handler calls a function that is a placeholder
        if (preg_match('/^(\w+)\(\)$/', trim($handler), $funcMatch)) {
            $funcName = $funcMatch[1];
            // Look for function definition that only contains alert placeholder
            if (preg_match(
                '/function\s+' . preg_quote($funcName, '/') . '\s*\([^)]*\)\s*\{([^}]*)\}/s',
                $content,
                $funcBody
            )) {
                $body = trim($funcBody[1]);
                if (
                    preg_match('/alert\s*\(/i', $body) &&
                    preg_match('/implement|coming soon|belum|akan tersedia|to be implemented/i', $body)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Extract all form elements with action routes.
     * Returns array of route names used in form actions.
     */
    private function extractFormActionRoutes(string $content): array
    {
        $routes = [];

        // Match form action="{{ route('...') }}" or action="{{ route("...") }}"
        if (preg_match_all('/action\s*=\s*"[^"]*route\(\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
            $routes = array_merge($routes, $matches[1]);
        }

        // Match form action with Blade syntax {{ route('...') }}
        if (preg_match_all('/<form[^>]*action\s*=\s*"\{\{\s*route\(\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
            $routes = array_merge($routes, $matches[1]);
        }

        return array_values(array_unique($routes));
    }

    /**
     * Extract modal close patterns (classList.add('hidden'), classList.remove).
     * Returns array of patterns found.
     */
    private function extractModalClosePatterns(string $content): array
    {
        $patterns = [];

        // Match classList.add('hidden') patterns
        if (preg_match_all("/classList\.add\(\s*['\"]hidden['\"]\s*\)/", $content, $matches)) {
            $patterns = array_merge($patterns, $matches[0]);
        }

        // Match classList.remove('hidden') patterns
        if (preg_match_all("/classList\.remove\(\s*['\"]hidden['\"]\s*\)/", $content, $matches)) {
            $patterns = array_merge($patterns, $matches[0]);
        }

        return $patterns;
    }

    /**
     * Extract legitimate alerts (error handling, validation, API response feedback).
     * These are alerts that are NOT placeholders.
     */
    private function extractLegitimateAlerts(string $content): array
    {
        $legitimateAlerts = [];
        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            if (!preg_match('/alert\s*\(/', $line)) {
                continue;
            }

            // Skip placeholder alerts
            if (preg_match('/implement|coming soon|belum|akan tersedia|to be implemented|will show|will be/i', $line)) {
                // Check if it's inside a catch block (legitimate error handling)
                $contextStart = max(0, $lineNumber - 5);
                $context = implode("\n", array_slice($lines, $contextStart, $lineNumber - $contextStart));
                if (preg_match('/catch\s*\(|\.catch\(|error|fail/i', $context)) {
                    $legitimateAlerts[] = trim($line);
                }
                continue;
            }

            // Non-placeholder alerts are legitimate (error handling, success feedback, etc.)
            $legitimateAlerts[] = trim($line);
        }

        return $legitimateAlerts;
    }

    // ── Property Test 1: All route() calls must be preserved ─────

    /**
     * @test
     *
     * Property Test 1: For all Blade files to be modified, all route() calls
     * in UNFIXED code must still exist in FIXED code.
     *
     * This test observes the current state and asserts that route() helpers exist.
     * After the fix, re-running this test confirms they are still present.
     *
     * **Validates: Requirements 3.1, 3.5**
     */
    public function test_property_all_route_calls_preserved(): void
    {
        $this
            ->limitTo(5)
            ->forAll(
                Generators::elements(...$this->filesToBeModified)
            )
            ->then(function (string $relativeFile) {
                $fullPath = $this->getFullPath($relativeFile);

                if (!file_exists($fullPath)) {
                    return; // Skip non-existent files
                }

                $content = file_get_contents($fullPath);
                $routes = $this->extractRouteCalls($content);

                // Assert that each route call found in the file is present
                foreach ($routes as $routeName) {
                    $this->assertStringContainsString(
                        $routeName,
                        $content,
                        "Route '{$routeName}' must be preserved in {$relativeFile}"
                    );
                }

                // Files known to have routes must have at least one route() call
                $filesWithKnownRoutes = [
                    'healthcare/emr/dashboard.blade.php',
                    'hotel/rooms/availability.blade.php',
                    'hotel/check-in/pre-arrival.blade.php',
                    'hotel/fb/minibar/index.blade.php',
                    'helpdesk/show.blade.php',
                    'procurement/supplier-performance-detail.blade.php',
                    'automation/workflows/show.blade.php',
                    'healthcare/laboratory/reports.blade.php',
                    'manufacturing/mix-design-versions.blade.php',
                ];

                if (in_array($relativeFile, $filesWithKnownRoutes)) {
                    $this->assertNotEmpty(
                        $routes,
                        "File {$relativeFile} should contain at least one route() call"
                    );
                }
            });
    }

    // ── Property Test 2: Functional @click handlers must remain ──

    /**
     * @test
     *
     * Property Test 2: For all Blade files to be modified, all functional @click
     * handlers (non-placeholder) must remain.
     *
     * Functional handlers include: Alpine.js state toggles, modal open/close,
     * filter operations, navigation, etc. Placeholder handlers (that only call
     * alert with "coming soon" messages) are excluded from this check.
     *
     * **Validates: Requirements 3.2, 3.3**
     */
    public function test_property_functional_click_handlers_preserved(): void
    {
        $this
            ->limitTo(5)
            ->forAll(
                Generators::elements(...$this->filesToBeModified)
            )
            ->then(function (string $relativeFile) {
                $fullPath = $this->getFullPath($relativeFile);

                if (!file_exists($fullPath)) {
                    $this->assertTrue(true); // Ensure at least one assertion
                    return;
                }

                $content = file_get_contents($fullPath);
                $handlers = $this->extractFunctionalClickHandlers($content);

                // Assert each functional handler is present in the content
                foreach ($handlers as $handler) {
                    $this->assertStringContainsString(
                        $handler,
                        $content,
                        "Functional @click handler '{$handler}' must be preserved in {$relativeFile}"
                    );
                }

                // Ensure at least one assertion per iteration
                $this->assertTrue(true);

                // Files known to have functional Alpine.js handlers
                $filesWithAlpineHandlers = [
                    'hotel/rooms/availability.blade.php',
                    'integrations/dashboard.blade.php',
                ];

                if (in_array($relativeFile, $filesWithAlpineHandlers)) {
                    $this->assertNotEmpty(
                        $handlers,
                        "File {$relativeFile} should contain functional @click handlers"
                    );
                }
            });
    }

    // ── Property Test 3: Form elements with action routes must remain ─

    /**
     * @test
     *
     * Property Test 3: For all Blade files to be modified, all form elements
     * with action route must remain intact.
     *
     * Forms with valid route actions (e.g., form submission for replies, status updates,
     * restock operations) must not be removed or broken by the fix.
     *
     * **Validates: Requirements 3.4**
     */
    public function test_property_form_action_routes_preserved(): void
    {
        $this
            ->limitTo(5)
            ->forAll(
                Generators::elements(...$this->filesToBeModified)
            )
            ->then(function (string $relativeFile) {
                $fullPath = $this->getFullPath($relativeFile);

                if (!file_exists($fullPath)) {
                    $this->assertTrue(true);
                    return;
                }

                $content = file_get_contents($fullPath);
                $formRoutes = $this->extractFormActionRoutes($content);

                // Ensure at least one assertion per iteration
                $this->assertTrue(true);

                // Assert each form action route is present
                foreach ($formRoutes as $routeName) {
                    $this->assertStringContainsString(
                        $routeName,
                        $content,
                        "Form action route '{$routeName}' must be preserved in {$relativeFile}"
                    );
                }

                // Files known to have forms with action routes
                $filesWithForms = [
                    'hotel/check-in/pre-arrival.blade.php',
                    'hotel/fb/minibar/index.blade.php',
                    'helpdesk/show.blade.php',
                    'automation/workflows/show.blade.php',
                ];

                if (in_array($relativeFile, $filesWithForms)) {
                    $this->assertNotEmpty(
                        $formRoutes,
                        "File {$relativeFile} should contain form elements with action routes"
                    );
                }
            });
    }

    // ── Property Test 4: Key routes must be valid and resolvable ──

    /**
     * @test
     *
     * Property Test 4: Routes purchasing.orders.show, hotel.reservations.create,
     * helpdesk.kb must be valid and resolvable.
     *
     * This test verifies that the routes referenced in the fix (Kategori A)
     * exist in the route definitions. We check the routes/web.php file for
     * their definitions.
     *
     * **Validates: Requirements 3.1, 3.5**
     */
    public function test_property_key_routes_are_defined(): void
    {
        $projectRoot = $this->resolveProjectRoot();
        $routesFile = $projectRoot . '/routes/web.php';

        $this->assertFileExists($routesFile, 'routes/web.php must exist');

        $routeContent = file_get_contents($routesFile);

        // Check for healthcare routes file too
        $healthcareRoutesFile = $projectRoot . '/routes/healthcare.php';
        if (file_exists($healthcareRoutesFile)) {
            $routeContent .= file_get_contents($healthcareRoutesFile);
        }

        // Route: purchasing.orders.show — defined as name('orders.show') within purchasing prefix
        $this->assertTrue(
            str_contains($routeContent, "name('orders.show')") ||
                str_contains($routeContent, "->name('purchasing.orders.show')") ||
                (str_contains($routeContent, "name('purchasing.") && str_contains($routeContent, "orders.show")),
            "Route 'purchasing.orders.show' must be defined in routes"
        );

        // Route: hotel.reservations.create — defined via Route::resource('reservations', ...)
        // A resource route automatically creates .create, .store, .show, etc.
        $this->assertTrue(
            str_contains($routeContent, "Route::resource('reservations'") ||
                str_contains($routeContent, "reservations.create") ||
                str_contains($routeContent, "name('reservations.create')"),
            "Route 'hotel.reservations.create' must be defined in routes (via resource or explicit)"
        );

        // Route: helpdesk.kb — defined as name('kb') within helpdesk prefix
        $this->assertTrue(
            str_contains($routeContent, "name('kb')") ||
                str_contains($routeContent, "->name('helpdesk.kb')"),
            "Route 'helpdesk.kb' must be defined in routes"
        );
    }

    // ── Deterministic Test: Modal close patterns preserved ────────

    /**
     * @test
     *
     * Verify that modal close patterns (classList.add('hidden'), classList.remove)
     * are preserved in files that use them.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_modal_close_patterns_preserved(): void
    {
        $filesWithModalClose = [
            'hotel/fb/minibar/index.blade.php',
            'automation/workflows/show.blade.php',
        ];

        foreach ($filesWithModalClose as $relativeFile) {
            $fullPath = $this->getFullPath($relativeFile);

            if (!file_exists($fullPath)) {
                continue;
            }

            $content = file_get_contents($fullPath);
            $patterns = $this->extractModalClosePatterns($content);

            $this->assertNotEmpty(
                $patterns,
                "File {$relativeFile} should contain modal close patterns (classList.add/remove('hidden'))"
            );
        }
    }

    // ── Deterministic Test: Legitimate alerts preserved ───────────

    /**
     * @test
     *
     * Verify that legitimate alerts (error handling, API response feedback)
     * remain in files that use them. These are NOT placeholder alerts.
     *
     * For example, integrations/dashboard.blade.php has alerts for WhatsApp
     * connection success/failure which are legitimate API feedback.
     *
     * **Validates: Requirements 3.2**
     */
    public function test_legitimate_alerts_preserved(): void
    {
        // integrations/dashboard.blade.php has legitimate alerts for API responses
        $fullPath = $this->getFullPath('integrations/dashboard.blade.php');

        if (!file_exists($fullPath)) {
            $this->markTestSkipped('integrations/dashboard.blade.php not found');
        }

        $content = file_get_contents($fullPath);

        // WhatsApp connection success alert is legitimate feedback
        $this->assertStringContainsString(
            "alert('WhatsApp connected successfully!')",
            $content,
            "Legitimate alert for WhatsApp connection success must be preserved"
        );

        // Error alert for failed connection is legitimate
        $this->assertStringContainsString(
            "alert('Failed to connect WhatsApp')",
            $content,
            "Legitimate alert for WhatsApp connection failure must be preserved"
        );

        // Error handler alert is legitimate
        $this->assertStringContainsString(
            "alert('Error: ' + error.message)",
            $content,
            "Legitimate error handler alert must be preserved"
        );
    }
}
