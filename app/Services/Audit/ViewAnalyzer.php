<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes Blade templates for UI/UX issues:
 * - Missing responsive breakpoint usage
 * - Hardcoded pixel widths
 * - Missing form accessibility (labels, ARIA attributes, validation feedback)
 * - Tables without mobile-friendly overflow handling
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * to scan all .blade.php files under resources/views/.
 *
 * Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.7, 4.8
 */
class ViewAnalyzer implements AnalyzerInterface
{
    /**
     * Base path to scan for Blade views.
     */
    private string $viewPath;

    /**
     * Project root path (for relative path reporting).
     */
    private string $basePath;

    /**
     * Tailwind responsive breakpoint prefixes to look for.
     */
    private const RESPONSIVE_PREFIXES = ['sm:', 'md:', 'lg:', 'xl:', '2xl:'];

    /**
     * Regex to detect hardcoded pixel widths in style attributes and inline CSS.
     * Excludes common safe patterns like border-radius, font-size, line-height,
     * border-width, and very small values (1px, 2px) used for borders.
     */
    private const HARDCODED_PX_PATTERN = '/(?<!border-radius:\s*)(?<!font-size:\s*)(?<!line-height:\s*)(?<!border(?:-\w+)?:\s*)\b(\d{3,})px\b/';

    /**
     * Simpler pattern to find all Npx occurrences for initial scan.
     */
    private const ALL_PX_PATTERN = '/\b(\d+)px\b/';

    /**
     * Pixel values that are safe to ignore (borders, small decorative values).
     */
    private const SAFE_PX_THRESHOLD = 4;

    /**
     * Patterns that indicate a table wrapper has overflow handling.
     */
    private const TABLE_OVERFLOW_PATTERNS = [
        'overflow-x-auto',
        'overflow-x-scroll',
        'overflow-auto',
        'overflow-scroll',
        'table-responsive',
        'overflow-x: auto',
        'overflow-x: scroll',
        'overflow: auto',
    ];

    public function __construct(?string $viewPath = null, ?string $basePath = null)
    {
        if ($basePath !== null) {
            $this->basePath = $basePath;
        } else {
            try {
                $this->basePath = base_path();
            } catch (\Throwable) {
                $this->basePath = getcwd();
            }
        }
        $this->viewPath = $viewPath ?? ($this->basePath.'/resources/views');
    }

    /**
     * Run the full analysis across all Blade templates.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];
        $bladeFiles = $this->discoverBladeFiles();

        foreach ($bladeFiles as $filePath) {
            $responsiveFindings = $this->checkResponsiveness($filePath);
            array_push($findings, ...$responsiveFindings);

            $pixelFindings = $this->findHardcodedPixels($filePath);
            array_push($findings, ...$pixelFindings);

            $accessibilityFindings = $this->checkFormAccessibility($filePath);
            array_push($findings, ...$accessibilityFindings);

            $tableFindings = $this->checkTableMobile($filePath);
            array_push($findings, ...$tableFindings);
        }

        return $findings;
    }

    /**
     * Check if a Blade template uses responsive breakpoints.
     *
     * Layout files and files with grid/flex layouts should use
     * responsive Tailwind breakpoint classes (sm:, md:, lg:, xl:).
     *
     * @param  string  $viewPath  Absolute path to the Blade file
     * @return AuditFinding[]
     */
    public function checkResponsiveness(string $viewPath): array
    {
        $findings = [];

        $content = @file_get_contents($viewPath);
        if ($content === false) {
            return $findings;
        }

        // Only check files that have layout-related classes (grid, flex, columns)
        // These are the files most likely to need responsive breakpoints
        $hasLayoutClasses = preg_match('/\b(grid|flex|columns|col-span|grid-cols|w-\d|max-w-)\b/', $content);
        if (! $hasLayoutClasses) {
            return $findings;
        }

        // Check if the file uses any responsive breakpoint prefixes
        $hasResponsiveClasses = false;
        foreach (self::RESPONSIVE_PREFIXES as $prefix) {
            if (str_contains($content, $prefix)) {
                $hasResponsiveClasses = true;
                break;
            }
        }

        if (! $hasResponsiveClasses) {
            $relativePath = $this->relativePath($viewPath);
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "No responsive breakpoints in {$relativePath}",
                description: 'Blade template uses layout classes (grid/flex) but has no responsive '
                    .'breakpoint prefixes (sm:, md:, lg:, xl:). This may cause layout issues on smaller screens.',
                file: $relativePath,
                line: null,
                recommendation: 'Add responsive breakpoint classes (e.g., sm:grid-cols-2, md:flex-row) '
                    .'to ensure proper layout on mobile and tablet devices.',
                metadata: [
                    'check' => 'responsiveness',
                ],
            );
        }

        return $findings;
    }

    /**
     * Detect hardcoded pixel widths in a Blade template.
     *
     * Looks for patterns like `width: 300px`, `style="width:500px"`,
     * or inline `Npx` values where N >= a threshold (to skip small
     * border/decoration values like 1px, 2px).
     *
     * @param  string  $viewPath  Absolute path to the Blade file
     * @return AuditFinding[]
     */
    public function findHardcodedPixels(string $viewPath): array
    {
        $findings = [];

        $content = @file_get_contents($viewPath);
        if ($content === false) {
            return $findings;
        }

        $lines = explode("\n", $content);
        $relativePath = $this->relativePath($viewPath);
        $flaggedLines = [];

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;

            // Find all Npx occurrences
            if (preg_match_all(self::ALL_PX_PATTERN, $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $pixelValue = (int) $match[1];

                    // Skip small values commonly used for borders, outlines, shadows
                    if ($pixelValue <= self::SAFE_PX_THRESHOLD) {
                        continue;
                    }

                    // Skip if it's inside a CSS property that commonly uses px
                    // (font-size, border-radius, line-height, box-shadow, border)
                    if ($this->isSafePxContext($line, $match[0])) {
                        continue;
                    }

                    $flaggedLines[] = [
                        'line' => $lineNumber,
                        'value' => $match[0],
                        'context' => trim($line),
                    ];
                }
            }
        }

        if (! empty($flaggedLines)) {
            // Group by file — report one finding per file with all occurrences
            $lineNumbers = array_column($flaggedLines, 'line');
            $values = array_unique(array_column($flaggedLines, 'value'));
            $count = count($flaggedLines);

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Low,
                title: "Hardcoded pixel widths in {$relativePath}",
                description: "Found {$count} hardcoded pixel value(s) ({$this->summarizeValues($values)}) "
                    .'on line(s) '.implode(', ', array_unique($lineNumbers)).'. '
                    .'Hardcoded pixel widths can break responsive layouts on different screen sizes.',
                file: $relativePath,
                line: $flaggedLines[0]['line'],
                recommendation: 'Replace hardcoded pixel widths with responsive Tailwind classes '
                    .'(e.g., w-full, max-w-lg, w-1/2) or responsive utility classes.',
                metadata: [
                    'check' => 'hardcoded_pixels',
                    'occurrences' => $flaggedLines,
                ],
            );
        }

        return $findings;
    }

    /**
     * Check form accessibility in a Blade template.
     *
     * Verifies that:
     * - <input> elements have associated <label> or aria-label/aria-labelledby
     * - <select> elements have associated labels
     * - <textarea> elements have associated labels
     * - Forms have validation feedback patterns
     *
     * @param  string  $viewPath  Absolute path to the Blade file
     * @return AuditFinding[]
     */
    public function checkFormAccessibility(string $viewPath): array
    {
        $findings = [];

        $content = @file_get_contents($viewPath);
        if ($content === false) {
            return $findings;
        }

        // Only check files that contain form inputs
        if (! preg_match('/<(input|select|textarea)\b/i', $content)) {
            return $findings;
        }

        $relativePath = $this->relativePath($viewPath);
        $issues = [];

        // Check for inputs without labels or ARIA attributes
        $unlabeledInputs = $this->findUnlabeledInputs($content);
        if (! empty($unlabeledInputs)) {
            $issues[] = [
                'type' => 'missing_labels',
                'count' => count($unlabeledInputs),
                'details' => $unlabeledInputs,
            ];
        }

        // Check for missing validation feedback patterns
        $hasValidationFeedback = $this->hasValidationFeedback($content);
        $hasFormTag = preg_match('/<form\b/i', $content);
        if ($hasFormTag && ! $hasValidationFeedback) {
            $issues[] = [
                'type' => 'missing_validation_feedback',
                'count' => 1,
                'details' => [],
            ];
        }

        if (! empty($issues)) {
            $descriptions = [];
            foreach ($issues as $issue) {
                if ($issue['type'] === 'missing_labels') {
                    $descriptions[] = "{$issue['count']} input(s) without associated label or ARIA attributes";
                }
                if ($issue['type'] === 'missing_validation_feedback') {
                    $descriptions[] = 'Form lacks validation error feedback (no @error or $errors usage)';
                }
            }

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Form accessibility issues in {$relativePath}",
                description: 'Found form accessibility issues: '.implode('; ', $descriptions).'. '
                    .'Forms should have proper label associations and validation feedback for accessibility.',
                file: $relativePath,
                line: null,
                recommendation: 'Add <label for="..."> elements or aria-label/aria-labelledby attributes '
                    .'to all form inputs. Add @error directives or $errors->has() checks for validation feedback.',
                metadata: [
                    'check' => 'form_accessibility',
                    'issues' => $issues,
                ],
            );
        }

        return $findings;
    }

    /**
     * Check if data tables have mobile-friendly overflow handling.
     *
     * Tables should be wrapped in a container with overflow-x-auto
     * or similar class to enable horizontal scrolling on mobile.
     *
     * @param  string  $viewPath  Absolute path to the Blade file
     * @return AuditFinding[]
     */
    public function checkTableMobile(string $viewPath): array
    {
        $findings = [];

        $content = @file_get_contents($viewPath);
        if ($content === false) {
            return $findings;
        }

        // Only check files that contain <table> tags
        if (! preg_match('/<table\b/i', $content)) {
            return $findings;
        }

        $relativePath = $this->relativePath($viewPath);
        $tablesWithoutOverflow = $this->findTablesWithoutOverflow($content);

        if (! empty($tablesWithoutOverflow)) {
            $count = count($tablesWithoutOverflow);
            $lineNumbers = array_column($tablesWithoutOverflow, 'line');

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Table(s) without mobile overflow in {$relativePath}",
                description: "Found {$count} <table> element(s) on line(s) ".implode(', ', $lineNumbers)
                    .' without a scrollable overflow container. '
                    .'Tables without overflow-x-auto will be cut off on mobile screens.',
                file: $relativePath,
                line: $tablesWithoutOverflow[0]['line'],
                recommendation: 'Wrap each <table> in a <div class="overflow-x-auto"> container '
                    .'to enable horizontal scrolling on smaller screens.',
                metadata: [
                    'check' => 'table_mobile',
                    'tables' => $tablesWithoutOverflow,
                ],
            );
        }

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'ui';
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Recursively discover all .blade.php files under the views directory.
     *
     * @return string[]
     */
    private function discoverBladeFiles(): array
    {
        $files = [];

        if (! is_dir($this->viewPath)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->viewPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Check if a pixel value is in a safe CSS context (font-size, border-radius, etc.).
     */
    private function isSafePxContext(string $line, string $pxValue): bool
    {
        // Safe CSS property patterns where px is commonly acceptable
        $safePatterns = [
            '/font-size\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/border-radius\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/line-height\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/box-shadow\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/text-shadow\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/border\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/border-\w+\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/outline\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/letter-spacing\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
            '/gap\s*:\s*[^;]*'.preg_quote($pxValue, '/').'/',
        ];

        foreach ($safePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Summarize pixel values for the finding description.
     */
    private function summarizeValues(array $values): string
    {
        if (count($values) <= 5) {
            return implode(', ', $values);
        }

        $first = array_slice($values, 0, 5);
        $remaining = count($values) - 5;

        return implode(', ', $first)." and {$remaining} more";
    }

    /**
     * Find <input>, <select>, <textarea> elements without associated labels or ARIA attributes.
     *
     * An input is considered labeled if:
     * - It has an aria-label attribute
     * - It has an aria-labelledby attribute
     * - It has a placeholder (partial accessibility, but common)
     * - It has type="hidden" or type="submit" or type="button" (don't need labels)
     * - There's a <label> with a matching for="" attribute in the file
     * - It's wrapped inside a <label> element
     *
     * @return array Array of ['line' => int, 'tag' => string] for unlabeled inputs
     */
    private function findUnlabeledInputs(string $content): array
    {
        $unlabeled = [];
        $lines = explode("\n", $content);

        // Collect all label for="" values in the file
        $labelForIds = [];
        if (preg_match_all('/\blabel\b[^>]*\bfor\s*=\s*["\']([^"\']+)["\']/i', $content, $labelMatches)) {
            $labelForIds = $labelMatches[1];
        }

        // Types that don't need labels
        $exemptTypes = ['hidden', 'submit', 'button', 'reset', 'image'];

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;

            // Match <input>, <select>, <textarea> tags
            if (preg_match_all('/<(input|select|textarea)\b([^>]*)>/i', $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $tag = strtolower($match[1]);
                    $attrs = $match[2];

                    // Skip exempt input types
                    if ($tag === 'input') {
                        if (preg_match('/\btype\s*=\s*["\'](\w+)["\']/i', $attrs, $typeMatch)) {
                            if (in_array(strtolower($typeMatch[1]), $exemptTypes, true)) {
                                continue;
                            }
                        }
                    }

                    // Check for aria-label or aria-labelledby
                    if (preg_match('/\baria-label\s*=/i', $attrs) || preg_match('/\baria-labelledby\s*=/i', $attrs)) {
                        continue;
                    }

                    // Check for id that matches a label for=""
                    if (preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $attrs, $idMatch)) {
                        if (in_array($idMatch[1], $labelForIds, true)) {
                            continue;
                        }
                    }

                    // Check if the input is inside a <label> tag on the same or nearby lines
                    if ($this->isInsideLabelTag($lines, $index)) {
                        continue;
                    }

                    $unlabeled[] = [
                        'line' => $lineNumber,
                        'tag' => $tag,
                    ];
                }
            }
        }

        return $unlabeled;
    }

    /**
     * Check if a line's input element is inside a <label> tag.
     * Looks at surrounding lines (up to 5 lines before) for an opening <label>.
     */
    private function isInsideLabelTag(array $lines, int $currentIndex): bool
    {
        // Look backwards up to 5 lines for an unclosed <label>
        $labelDepth = 0;
        $start = max(0, $currentIndex - 5);

        for ($i = $currentIndex; $i >= $start; $i--) {
            $line = $lines[$i];
            // Count closing </label> tags
            $labelDepth -= substr_count(strtolower($line), '</label');
            // Count opening <label tags
            $labelDepth += preg_match_all('/<label\b/i', $line);

            if ($labelDepth > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the content has validation feedback patterns.
     *
     * Looks for Laravel Blade @error directives, $errors usage,
     * or Alpine.js validation patterns.
     */
    private function hasValidationFeedback(string $content): bool
    {
        $patterns = [
            '/@error\b/',
            '/\$errors\s*->/',
            '/\$errors\b/',
            '/x-show\s*=\s*["\'].*error/i',
            '/validation-error/i',
            '/invalid-feedback/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find <table> elements that are not wrapped in an overflow container.
     *
     * Checks if the <table> tag or its parent container (within 3 lines above)
     * has overflow-x-auto or similar overflow handling class.
     *
     * @return array Array of ['line' => int] for tables without overflow
     */
    private function findTablesWithoutOverflow(string $content): array
    {
        $tablesWithoutOverflow = [];
        $lines = explode("\n", $content);

        foreach ($lines as $index => $line) {
            if (! preg_match('/<table\b/i', $line)) {
                continue;
            }

            $lineNumber = $index + 1;

            // Check the table tag itself and surrounding context (3 lines above)
            $contextStart = max(0, $index - 3);
            $context = implode("\n", array_slice($lines, $contextStart, $index - $contextStart + 1));

            $hasOverflow = false;
            foreach (self::TABLE_OVERFLOW_PATTERNS as $pattern) {
                if (str_contains($context, $pattern)) {
                    $hasOverflow = true;
                    break;
                }
            }

            if (! $hasOverflow) {
                $tablesWithoutOverflow[] = [
                    'line' => $lineNumber,
                ];
            }
        }

        return $tablesWithoutOverflow;
    }

    /**
     * Convert an absolute path to a relative path from the project root.
     */
    private function relativePath(string $absolutePath): string
    {
        $basePath = $this->basePath.'/';
        if (str_starts_with($absolutePath, $basePath)) {
            return substr($absolutePath, strlen($basePath));
        }

        return $absolutePath;
    }
}
