<?php

namespace Tests\Feature;

use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Bug Condition Exploration Test — Inactive Buttons Detection
 *
 * EXPECTED: This test MUST FAIL on unfixed code.
 * Failure confirms the bug exists: non-functional buttons/links in Blade templates.
 *
 * When this test PASSES after the fix is applied, it confirms all inactive button
 * instances have been resolved.
 *
 * Scans for two categories of bug patterns:
 *   Category 1: href="#" without Alpine.js handler (@click, x-on:click) and without wire:click
 *   Category 2: onclick containing alert() with placeholder messages (implement|coming soon|belum|akan tersedia)
 *
 * Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5
 */
class InactiveButtonsBugConditionTest extends TestCase
{
    use TestTrait;

    private string $viewsPath;

    /**
     * Blade files known to contain href="#" without functional handler.
     */
    private array $hrefHashFiles = [
        'agriculture/dashboard.blade.php',
        'hotel/rooms/availability.blade.php',
        'hotel/check-in/pre-arrival.blade.php',
        'helpdesk/show.blade.php',
        'procurement/supplier-performance-detail.blade.php',
        'printing/job-detail.blade.php',
        'suppliers/sourcing-dashboard.blade.php',
        'mobile/optimized-dashboard.blade.php',
        'pages/about/careers.blade.php',
    ];

    /**
     * Blade files known to contain onclick with alert() placeholder.
     */
    private array $alertPlaceholderFiles = [
        'healthcare/emr/dashboard.blade.php',
        'healthcare/laboratory/reports.blade.php',
        'hotel/fb/minibar/index.blade.php',
        'hotel/rates/index.blade.php',
        'analytics/scheduled-reports.blade.php',
        'automation/workflows/show.blade.php',
        'integrations/dashboard.blade.php',
        'manufacturing/mix-design-versions.blade.php',
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
     * Check if a line containing href="#" has a functional handler.
     *
     * A functional handler is one of:
     * - Alpine.js @click or x-on:click on the same element
     * - wire:click (Livewire) on the same element
     * - An onclick that is NOT an alert() placeholder
     *
     * We check the surrounding context (the full element) not just the single line.
     */
    private function elementHasFunctionalHandler(string $content, int $hrefLineIndex): bool
    {
        $lines = explode("\n", $content);

        // Find the opening tag that contains this href="#"
        $elementStart = $hrefLineIndex;
        $elementEnd = $hrefLineIndex;

        // Walk backwards to find the start of the element (line with opening <a or <button)
        for ($i = $hrefLineIndex; $i >= max(0, $hrefLineIndex - 10); $i--) {
            if (preg_match('/<(a|button)\b/i', $lines[$i])) {
                $elementStart = $i;
                break;
            }
        }

        // Walk forwards to find the closing > of the opening tag
        for ($i = $hrefLineIndex; $i <= min(count($lines) - 1, $hrefLineIndex + 10); $i++) {
            if (str_contains($lines[$i], '>')) {
                $elementEnd = $i;
                break;
            }
        }

        // Combine the element lines
        $elementHtml = implode(' ', array_slice($lines, $elementStart, $elementEnd - $elementStart + 1));

        // Check for Alpine.js handlers
        if (preg_match('/@click|x-on:click/i', $elementHtml)) {
            return true;
        }

        // Check for Livewire handlers
        if (preg_match('/wire:click/i', $elementHtml)) {
            return true;
        }

        // Check for onclick that is NOT an alert placeholder
        if (preg_match('/onclick="([^"]*)"/', $elementHtml, $onclickMatch)) {
            $onclickContent = $onclickMatch[1];
            // If onclick contains alert() with placeholder messages, it's NOT functional
            if (
                preg_match('/alert\s*\(/i', $onclickContent) &&
                preg_match('/implement|coming soon|belum|akan tersedia/i', $onclickContent)
            ) {
                return false;
            }
            // If onclick calls a function, check if that function is a placeholder
            if (preg_match('/(\w+)\(\)/', $onclickContent, $funcMatch)) {
                $funcName = $funcMatch[1];
                if ($this->isFunctionPlaceholder($content, $funcName)) {
                    return false;
                }
            }
            // Otherwise it's a functional onclick
            return true;
        }

        // Check for onclick with single quotes
        if (preg_match("/onclick='([^']*)'/", $elementHtml, $onclickMatch)) {
            $onclickContent = $onclickMatch[1];
            if (
                preg_match('/alert\s*\(/i', $onclickContent) &&
                preg_match('/implement|coming soon|belum|akan tersedia/i', $onclickContent)
            ) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Check if a JavaScript function defined in the file is just a placeholder
     * (only contains an alert with placeholder message).
     */
    private function isFunctionPlaceholder(string $content, string $funcName): bool
    {
        // Match function definition and its body
        if (preg_match(
            '/function\s+' . preg_quote($funcName, '/') . '\s*\([^)]*\)\s*\{([^}]*)\}/s',
            $content,
            $funcBody
        )) {
            $body = trim($funcBody[1]);
            // Check if the function body is essentially just an alert placeholder
            if (
                preg_match('/alert\s*\(/i', $body) &&
                preg_match('/implement|coming soon|belum|akan tersedia|to be implemented/i', $body)
            ) {
                return true;
            }
        }

        return false;
    }

    // ── Test 1: href="#" without functional handler ───────────────

    /**
     * @test
     *
     * Property 1 (Part A): No href="#" links without functional handlers
     *
     * WILL FAIL on unfixed code because multiple views contain links with href="#"
     * that have no Alpine.js handler, no wire:click, and no functional onclick.
     * These links only scroll to the top of the page without performing any action.
     *
     * Counterexamples expected:
     * - agriculture/dashboard.blade.php: "Detect Pests", "Manage Irrigation", "Market Prices"
     * - hotel/rooms/availability.blade.php: "New Reservation"
     * - hotel/check-in/pre-arrival.blade.php: "Terms & Conditions", "Cancellation Policy", "Privacy Policy"
     * - helpdesk/show.blade.php: KB article links
     * - procurement/supplier-performance-detail.blade.php: PO number links
     * - printing/job-detail.blade.php: "Print Job Ticket", "Start Press Run"
     * - suppliers/sourcing-dashboard.blade.php: "View All"
     * - mobile/optimized-dashboard.blade.php: "Lihat Semua"
     * - pages/about/careers.blade.php: "Apply Now"
     *
     * **Validates: Requirements 1.1, 1.3, 1.4, 1.5**
     */
    public function test_no_href_hash_without_functional_handler(): void
    {
        $violations = [];

        foreach ($this->hrefHashFiles as $relativeFile) {
            $fullPath = $this->getFullPath($relativeFile);

            if (!file_exists($fullPath)) {
                continue;
            }

            $content = file_get_contents($fullPath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                // Match href="#" (with possible whitespace variations)
                if (!preg_match('/href\s*=\s*["\']#["\']/', $line)) {
                    continue;
                }

                // Check if this element has a functional handler
                if (!$this->elementHasFunctionalHandler($content, $lineNumber)) {
                    $violations[] = sprintf(
                        '%s:%d: %s',
                        $relativeFile,
                        $lineNumber + 1,
                        trim($line)
                    );
                }
            }
        }

        $count = count($violations);
        $this->assertEmpty(
            $violations,
            "Found {$count} href=\"#\" link(s) without functional handler (bug condition detected):\n"
                . implode("\n", array_slice($violations, 0, 50))
        );
    }

    // ── Test 2: alert() placeholder in onclick or script functions ─

    /**
     * @test
     *
     * Property 1 (Part B): No alert() placeholders in button handlers
     *
     * WILL FAIL on unfixed code because multiple views contain onclick handlers
     * or script functions that only display alert() messages like "coming soon",
     * "to be implemented", "belum tersedia", etc. instead of performing real actions.
     *
     * Counterexamples expected:
     * - healthcare/emr/dashboard.blade.php: openNewVisitModal(), openLabOrderModal()
     * - healthcare/laboratory/reports.blade.php: "Export PDF", "Export Excel"
     * - hotel/fb/minibar/index.blade.php: "Record Consumption"
     * - hotel/rates/index.blade.php: "Edit Rate"
     * - analytics/scheduled-reports.blade.php: "Toggle Schedule", "Delete Schedule"
     * - automation/workflows/show.blade.php: "Add Action"
     * - integrations/dashboard.blade.php: "Test Payment Gateway", "View Integration Logs"
     * - manufacturing/mix-design-versions.blade.php: "Compare Versions"
     *
     * **Validates: Requirements 1.2**
     */
    public function test_no_alert_placeholder_in_handlers(): void
    {
        $violations = [];

        // Pattern to match alert() calls with placeholder messages
        // Includes: implement, coming soon, belum, akan tersedia, to be implemented
        // Also catches alerts that describe future functionality (will show, will be)
        $placeholderPattern = '/implement|coming soon|belum|akan tersedia|to be implemented|will show|will be/i';

        foreach ($this->alertPlaceholderFiles as $relativeFile) {
            $fullPath = $this->getFullPath($relativeFile);

            if (!file_exists($fullPath)) {
                continue;
            }

            $content = file_get_contents($fullPath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                // Match alert() calls
                if (!preg_match('/alert\s*\(/', $line)) {
                    continue;
                }

                // Check if the alert message contains placeholder text
                if (preg_match($placeholderPattern, $line)) {
                    // Exclude legitimate alerts (error handling, API responses)
                    $isLegitimateAlert = false;

                    // Check if this is inside a catch block or error handler
                    $contextStart = max(0, $lineNumber - 5);
                    $context = implode("\n", array_slice($lines, $contextStart, $lineNumber - $contextStart));
                    if (
                        preg_match('/catch\s*\(|\.catch\(/', $context) &&
                        preg_match('/error\.message|err\.message/i', $line)
                    ) {
                        $isLegitimateAlert = true;
                    }

                    if (!$isLegitimateAlert) {
                        $violations[] = sprintf(
                            '%s:%d: %s',
                            $relativeFile,
                            $lineNumber + 1,
                            trim($line)
                        );
                    }
                }
            }
        }

        $count = count($violations);
        $this->assertEmpty(
            $violations,
            "Found {$count} alert() placeholder(s) in button handlers (bug condition detected):\n"
                . implode("\n", array_slice($violations, 0, 50))
        );
    }

    // ── Property-Based Test: Random file sampling ─────────────────

    /**
     * @test
     *
     * Property 1 (Combined): Bug Condition — Tombol Non-Fungsional Terdeteksi
     *
     * Uses property-based testing to randomly sample from the identified bug files
     * and verify that NONE of them contain the bug condition patterns.
     *
     * This test generates random selections from the known buggy files and asserts
     * that no bug patterns exist. On unfixed code, this WILL FAIL because the
     * patterns are present.
     *
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5**
     */
    public function test_property_no_inactive_buttons_in_blade_files(): void
    {
        $allBugFiles = array_unique(array_merge($this->hrefHashFiles, $this->alertPlaceholderFiles));
        $placeholderPattern = '/implement|coming soon|belum|akan tersedia|to be implemented|will show|will be/i';

        $this
            ->limitTo(10)
            ->forAll(
                Generators::elements(...$allBugFiles)
            )
            ->then(function (string $relativeFile) use ($placeholderPattern) {
                $fullPath = $this->getFullPath($relativeFile);

                if (!file_exists($fullPath)) {
                    return; // Skip non-existent files
                }

                $content = file_get_contents($fullPath);
                $lines = explode("\n", $content);
                $violations = [];

                foreach ($lines as $lineNumber => $line) {
                    // Check for href="#" without functional handler
                    if (preg_match('/href\s*=\s*["\']#["\']/', $line)) {
                        if (!$this->elementHasFunctionalHandler($content, $lineNumber)) {
                            $violations[] = sprintf(
                                '%s:%d: href="#" without handler: %s',
                                $relativeFile,
                                $lineNumber + 1,
                                trim($line)
                            );
                        }
                    }

                    // Check for alert() placeholder
                    if (preg_match('/alert\s*\(/', $line) && preg_match($placeholderPattern, $line)) {
                        // Exclude error handler alerts
                        $contextStart = max(0, $lineNumber - 5);
                        $context = implode("\n", array_slice($lines, $contextStart, $lineNumber - $contextStart));
                        if (!(preg_match('/catch\s*\(|\.catch\(/', $context) &&
                            preg_match('/error\.message|err\.message/i', $line))) {
                            $violations[] = sprintf(
                                '%s:%d: alert() placeholder: %s',
                                $relativeFile,
                                $lineNumber + 1,
                                trim($line)
                            );
                        }
                    }
                }

                $this->assertEmpty(
                    $violations,
                    "Bug condition found in {$relativeFile}:\n" . implode("\n", $violations)
                );
            });
    }
}
