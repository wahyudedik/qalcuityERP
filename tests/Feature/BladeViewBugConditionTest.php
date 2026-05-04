<?php

namespace Tests\Feature;

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Bug Condition Exploration Test — Blade View Bug Pattern Detection
 *
 * EXPECTED: These tests MUST FAIL on unfixed code.
 * Failure confirms the bugs exist in resources/views/.
 *
 * When these tests PASS after the fix is applied, it confirms all bug patterns
 * have been resolved.
 *
 * Scans for five categories of bug patterns:
 *   Category 1: @foreach($var as ...) without ?? [] guard
 *   Category 2: x-data="..." with direct Blade {{ }} interpolation without @js()
 *   Category 3: Chained Eloquent access $model->relation->property without ?->
 *   Category 4: <canvas id="X"> with duplicate static IDs within a single view file
 *   Category 5: Bootstrap CSS classes in a Tailwind-only project
 *
 * Validates: Requirements 1.1, 1.2, 1.4, 1.5, 1.9, 1.10, 1.13, 1.14, 1.15
 */
class BladeViewBugConditionTest extends TestCase
{
    use TestTrait;

    private string $viewsPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve views path relative to project root (works in both CLI and artisan test)
        $projectRoot = $this->resolveProjectRoot();
        $this->viewsPath = $projectRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }

    // ── Helper: resolve project root ─────────────────────────────

    /**
     * Resolve the project root directory.
     * Works whether running via `php artisan test` or `vendor/bin/phpunit`.
     */
    private function resolveProjectRoot(): string
    {
        // When running via artisan, getcwd() is the project root
        $cwd = getcwd();

        // Verify it looks like a Laravel project root
        if (is_dir($cwd . '/resources/views')) {
            return $cwd;
        }

        // Fallback: walk up from __DIR__ (tests/Feature/) to find project root
        $dir = __DIR__;
        for ($i = 0; $i < 5; $i++) {
            $dir = dirname($dir);
            if (is_dir($dir . '/resources/views')) {
                return $dir;
            }
        }

        return $cwd;
    }

    // ── Helper: file discovery ────────────────────────────────────

    /**
     * Recursively find all *.blade.php files in resources/views/.
     *
     * @return string[] Array of absolute file paths
     */
    private function getAllBladeFiles(): array
    {
        if (!is_dir($this->viewsPath)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->viewsPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);
        return $files;
    }

    // ── Helper: pattern scanner ───────────────────────────────────

    /**
     * Scan file content for a regex pattern and return violation strings.
     *
     * @param  string $pattern  PCRE regex pattern
     * @param  string $content  File content
     * @param  string $filePath Absolute path (used in violation message)
     * @return string[]         Array of "filePath:lineNumber: matched_line" strings
     */
    private function scanForPattern(string $pattern, string $content, string $filePath): array
    {
        $violations = [];
        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            if (preg_match($pattern, $line)) {
                $relPath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                $relPath = str_replace('\\', '/', $relPath);
                $violations[] = sprintf(
                    '%s:%d: %s',
                    $relPath,
                    $lineNumber + 1,
                    trim($line)
                );
            }
        }

        return $violations;
    }

    // ── Test 1: @foreach without ?? [] null guard ─────────────────

    /**
     * @test
     * Category 1: @foreach($var as ...) without ?? [] guard
     *
     * WILL FAIL on unfixed code because multiple views use @foreach on variables
     * that may be null (no ?? [] guard), which throws:
     *   "foreach() argument must be of type array|object, null given"
     *
     * Counterexample: Any view containing @foreach($collection as $item) without
     * $collection ?? [] on the same line.
     *
     * Validates: Requirements 1.9
     */
    public function test_no_foreach_without_null_guard(): void
    {
        $bladeFiles = $this->getAllBladeFiles();

        $this->assertNotEmpty(
            $bladeFiles,
            "No blade files found in {$this->viewsPath}. Check the path."
        );

        $violations = [];

        foreach ($bladeFiles as $filePath) {
            $content = file_get_contents($filePath);

            // Find @foreach($var as ...) lines
            $lines = explode("\n", $content);
            foreach ($lines as $lineNumber => $line) {
                // Match @foreach($var as ...) — variable must start with $
                if (preg_match('/@foreach\(\$\w+\s+as/i', $line)) {
                    // Check if the same line has a null guard: ?? [] or ??[]
                    if (!str_contains($line, '?? []') && !str_contains($line, '??[]')) {
                        $relPath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                        $relPath = str_replace('\\', '/', $relPath);
                        $violations[] = sprintf(
                            '%s:%d: %s',
                            $relPath,
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
            "Found {$count} @foreach without ?? [] null guard:\n" . implode("\n", $violations)
        );
    }

    // ── Test 2: x-data with direct Blade interpolation ───────────

    /**
     * @test
     * Category 2: x-data="..." with direct Blade {{ }} interpolation without @js()
     *
     * WILL FAIL on unfixed code because views use x-data="{{ $phpVar }}" which
     * can produce invalid JavaScript when $phpVar contains arrays, objects, or
     * strings with special characters.
     *
     * Counterexample: Any view containing x-data="{ key: {{ $var }} }" without @js().
     *
     * Validates: Requirements 1.5
     */
    public function test_no_xdata_with_direct_blade_interpolation(): void
    {
        $bladeFiles = $this->getAllBladeFiles();

        $violations = [];

        foreach ($bladeFiles as $filePath) {
            $content = file_get_contents($filePath);
            $found = $this->scanForPattern(
                '/x-data="[^"]*\{\{[^"]*\}\}[^"]*"/',
                $content,
                $filePath
            );
            $violations = array_merge($violations, $found);
        }

        $count = count($violations);
        $this->assertEmpty(
            $violations,
            "Found {$count} x-data with direct Blade {{ }} interpolation (should use @js()):\n"
                . implode("\n", $violations)
        );
    }

    // ── Test 3: Chained Eloquent access without null-safe ─────────

    /**
     * @test
     * Category 3: Chained Eloquent access $model->relation->property without ?->
     *
     * WILL FAIL on unfixed code because views use $model->relation->property
     * without null-safe operator, which throws:
     *   "Attempt to read property on null"
     * when the relation is null (deleted record, optional relation, etc.)
     *
     * Counterexample: $invoice->customer->name (should be $invoice->customer?->name)
     *
     * Validates: Requirements 1.10
     */
    public function test_no_chained_eloquent_without_null_safe(): void
    {
        $bladeFiles = $this->getAllBladeFiles();

        // Carbon date/time methods — these are called on Carbon objects (date fields),
        // not on Eloquent relations. Patterns like $model->created_at->format() are safe.
        $carbonMethods = [
            'format',
            'diffForHumans',
            'diffInDays',
            'diffInHours',
            'diffInMinutes',
            'diffInSeconds',
            'diffInWeeks',
            'diffInMonths',
            'diffInYears',
            'isPast',
            'isFuture',
            'isToday',
            'isYesterday',
            'isTomorrow',
            'lt',
            'lte',
            'gt',
            'gte',
            'eq',
            'ne',
            'addDays',
            'addHours',
            'addMinutes',
            'addMonths',
            'addYears',
            'subDays',
            'subHours',
            'subMinutes',
            'subMonths',
            'subYears',
            'startOfDay',
            'endOfDay',
            'startOfMonth',
            'endOfMonth',
            'translatedFormat',
            'toDateString',
            'toDateTimeString',
            'toTimeString',
            'toIso8601String',
            'toRfc2822String',
            'timestamp',
            'unix',
            'locale',
            'setLocale',
            'setTimezone',
            'timezone',
            'year',
            'month',
            'day',
            'hour',
            'minute',
            'second',
            'dayOfWeek',
            'dayOfYear',
            'weekOfYear',
            'daysInMonth',
            'copy',
            'clone',
        ];

        // Eloquent Collection / Laravel Collection methods — these are called on
        // relation collections, not on individual Eloquent model properties.
        // Patterns like $model->relation->count() are safe (relation is a Collection).
        $collectionMethods = [
            'count',
            'sum',
            'avg',
            'min',
            'max',
            'first',
            'last',
            'isEmpty',
            'isNotEmpty',
            'contains',
            'has',
            'get',
            'filter',
            'map',
            'each',
            'reduce',
            'pluck',
            'keys',
            'values',
            'sortBy',
            'sortByDesc',
            'sortKeys',
            'sortKeysDesc',
            'groupBy',
            'keyBy',
            'unique',
            'flatten',
            'collapse',
            'take',
            'skip',
            'slice',
            'chunk',
            'split',
            'where',
            'whereIn',
            'whereNotIn',
            'whereBetween',
            'push',
            'put',
            'pull',
            'forget',
            'prepend',
            'append',
            'merge',
            'union',
            'intersect',
            'diff',
            'toArray',
            'toJson',
            'all',
            'lists',
            'load',
            'loadMissing',
            'loadCount',
            'loadSum',
            'loadAvg',
            'fresh',
            'refresh',
        ];

        // Date/timestamp field name patterns — these are Carbon objects, not Eloquent relations.
        // A field matching these patterns will be a Carbon instance, so ->method() on it is safe.
        $dateFieldPatterns = [
            '/_at$/',       // created_at, updated_at, deleted_at, locked_at, etc.
            '/_date$/',     // start_date, end_date, birth_date, etc.
            '/_time$/',     // start_time, end_time, etc.
            '/^date$/',     // just "date"
            '/^next_run$/', // scheduler next_run field
        ];

        $violations = [];

        foreach ($bladeFiles as $filePath) {
            $content = file_get_contents($filePath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                // Match $var->prop->prop (two-level chained access)
                if (!preg_match('/\$\w+->\w+->\w+/', $line)) {
                    continue;
                }

                // Exclude lines that already use null-safe operator ?->
                if (str_contains($line, '?->')) {
                    continue;
                }

                // Exclude $errors->X->get/first/has patterns (Laravel ViewErrorBag)
                if (preg_match('/\$errors->\w+->/', $line)) {
                    continue;
                }

                // Check each chained access on the line
                $isViolation = false;
                if (preg_match_all('/\$\w+->(\w+)->(\w+)/', $line, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $relationName = $match[1]; // first property (potential relation or date field)
                        $propertyName = $match[2]; // second property (what we're accessing on it)

                        // Skip if the first property is a date/timestamp field (Carbon object)
                        $isDateField = false;
                        foreach ($dateFieldPatterns as $pattern) {
                            if (preg_match($pattern, $relationName)) {
                                $isDateField = true;
                                break;
                            }
                        }
                        if ($isDateField) {
                            continue;
                        }

                        // Skip if the second property is a Carbon method
                        // (means the first property is a Carbon date field)
                        if (in_array($propertyName, $carbonMethods)) {
                            continue;
                        }

                        // Skip if the second property is a Collection method
                        // (means the first property is a relation collection)
                        if (in_array($propertyName, $collectionMethods)) {
                            continue;
                        }

                        // Skip pivot accessor — $model->pivot->field is a special
                        // Eloquent accessor that is always present on pivot models
                        if ($relationName === 'pivot') {
                            continue;
                        }

                        // This looks like a real Eloquent relation chain without null-safe
                        $isViolation = true;
                        break;
                    }
                }

                if ($isViolation) {
                    $relPath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                    $relPath = str_replace('\\', '/', $relPath);
                    $violations[] = sprintf(
                        '%s:%d: %s',
                        $relPath,
                        $lineNumber + 1,
                        trim($line)
                    );
                }
            }
        }

        $count = count($violations);
        $this->assertEmpty(
            $violations,
            "Found {$count} chained Eloquent access without null-safe operator (?->):\n"
                . implode("\n", $violations)
        );
    }

    // ── Test 4: Duplicate canvas IDs within a single view file ────

    /**
     * @test
     * Category 4: <canvas id="X"> with duplicate static IDs within a single view file
     *
     * WILL FAIL on unfixed code because views define multiple <canvas> elements
     * with the same static ID, causing:
     *   "Canvas is already in use. Chart with ID 0 must be destroyed before reuse."
     *
     * Counterexample: A view file containing two <canvas id="projectionChart"> elements.
     *
     * Validates: Requirements 1.14
     */
    public function test_no_duplicate_canvas_ids(): void
    {
        $bladeFiles = $this->getAllBladeFiles();

        $violations = [];

        foreach ($bladeFiles as $filePath) {
            $content = file_get_contents($filePath);

            // Extract all canvas id="..." values (static IDs only — no {{ }})
            preg_match_all('/<canvas[^>]+id="([^"{}]+)"/', $content, $matches);

            if (empty($matches[1])) {
                continue;
            }

            $ids = $matches[1];
            $idCounts = array_count_values($ids);
            $duplicates = array_filter($idCounts, fn($count) => $count > 1);

            if (!empty($duplicates)) {
                $relPath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                $relPath = str_replace('\\', '/', $relPath);
                foreach (array_keys($duplicates) as $dupId) {
                    $violations[] = sprintf(
                        '%s: duplicate canvas id="%s" appears %d times',
                        $relPath,
                        $dupId,
                        $idCounts[$dupId]
                    );
                }
            }
        }

        $count = count($violations);
        $this->assertEmpty(
            $violations,
            "Found {$count} duplicate canvas IDs within single view files:\n"
                . implode("\n", $violations)
        );
    }

    // ── Test 5: Bootstrap CSS classes in Tailwind project ─────────

    /**
     * @test
     * Category 5: Bootstrap CSS classes in a Tailwind-only project
     *
     * WILL FAIL on unfixed code because resources/views/inventory/reports.blade.php
     * uses Bootstrap classes (btn btn-success, card, d-flex, col-md-6, text-primary,
     * text-success, text-danger, text-muted, progress) which are not in the Tailwind
     * build and cause unstyled/broken UI.
     *
     * Counterexample:
     *   inventory/reports.blade.php:10: <button class="btn btn-success" ...>
     *   inventory/reports.blade.php:22: <div class="card">
     *   inventory/reports.blade.php:8:  <div class="d-flex justify-content-between ...">
     *   inventory/reports.blade.php:28: <div class="col-md-6 mb-4">
     *   inventory/reports.blade.php:24: <h3 class="text-primary">
     *
     * Validates: Requirements 1.15
     */
    public function test_no_bootstrap_classes_in_tailwind_project(): void
    {
        $bladeFiles = $this->getAllBladeFiles();

        $violations = [];

        // Bootstrap classes that should not appear in a Tailwind project
        $bootstrapPattern = '/\b(btn|card|d-flex|col-md-|text-primary|text-success|text-danger|text-muted|progress)\b/';

        // Custom CSS class name prefixes/patterns that are NOT Bootstrap classes.
        // These are compound class names used in this project's custom CSS that happen
        // to contain Bootstrap keywords as part of their name (e.g. "mob-card-title",
        // "card-hover", "session-btn"). They must be excluded to avoid false positives.
        $customClassPrefixes = [
            // Mobile UI custom classes
            'mob-card',
            'mob-stat-card',
            'mob-action-card',
            'mob-pick-card',
            'mob-progress',
            'mob-item-card',
            'mob-back-btn',
            'mob-stepper-btn',
            'mob-confirm-btn',
            'mob-finish-btn',
            'mob-submit-btn',
            // Landing page / UI component custom classes
            'card-hover',
            'card-opname',
            'card-picking',
            'card-transfer',
            'card-farm',
            'card-type-btn',
            'lead-card',
            'swipe-card',
            'target-card',
            'item-card',
            'print-card',
            'voucher-card',
            'summary-card',
            'bg-dark-card',
            // Button custom classes
            'session-btn',
            'hint-btn',
            'tab-btn',
            'period-btn',
            'picker-btn',
            'cat-btn',
            'pay-btn',
            'rail-btn',
            'active-btn',
            'disabled-btn',
            // Progress custom classes (PDF templates and mobile UI)
            'progress-fill',
            'progress-track',
            'progress-wrap',
            'progress-section',
            'progress-row',
            'progress-label',
            'progress-count',
            'wizard-progress-container',
            'progress-bar', // custom PDF progress bar class (not Bootstrap)
            // Icon classes that contain keywords
            'fa-credit-card',
        ];

        foreach ($bladeFiles as $filePath) {
            $content = file_get_contents($filePath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                // Only scan inside static class="..." attributes to avoid false positives.
                // Exclude Alpine.js :class="..." and x-bind:class="..." bindings since
                // those contain JavaScript expressions, not CSS class names.
                // Also exclude vendor files (e.g. Bootstrap pagination template).
                $relPathCheck = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                $relPathCheck = str_replace('\\', '/', $relPathCheck);
                if (str_starts_with($relPathCheck, 'vendor/')) {
                    continue;
                }

                // Strip Alpine.js :class and x-bind:class attributes before scanning
                $lineToScan = preg_replace('/(?:x-bind:class|:class)="[^"]*"/', '', $line);
                $lineToScan = preg_replace("/(?:x-bind:class|:class)='[^']*'/", '', $lineToScan);

                if (
                    !preg_match('/class="[^"]*' . substr($bootstrapPattern, 1, -1) . '[^"]*"/', $lineToScan)
                    && !preg_match('/class=\'[^\']*' . substr($bootstrapPattern, 1, -1) . '[^\']*\'/', $lineToScan)
                ) {
                    continue;
                }

                // Extract all class attribute values from this line
                preg_match_all('/class="([^"]*)"/', $line, $doubleQuoteMatches);
                preg_match_all("/class='([^']*)'/", $line, $singleQuoteMatches);
                $allClassStrings = array_merge(
                    $doubleQuoteMatches[1] ?? [],
                    $singleQuoteMatches[1] ?? []
                );

                // Check if the matched Bootstrap keyword is part of a custom class name
                // or a PHP variable expression (e.g. $card['bg'], $project->progress)
                $isRealViolation = false;
                foreach ($allClassStrings as $classStr) {
                    // Skip if the class string contains PHP variable expressions
                    // (e.g. {{ $card['bg'] }}, {{ $project->progress }}, {{ $c['btn'] }})
                    // These are dynamic Tailwind classes, not Bootstrap classes
                    if (preg_match('/\{\{[^}]*\b(btn|card|progress)\b[^}]*\}\}/', $classStr)) {
                        continue;
                    }

                    // Split into individual class tokens
                    $tokens = preg_split('/\s+/', trim($classStr));
                    foreach ($tokens as $token) {
                        // Skip empty tokens and PHP template expressions
                        if (empty($token) || str_starts_with($token, '{{') || str_starts_with($token, '@')) {
                            continue;
                        }

                        // Check if this token matches the Bootstrap pattern
                        if (!preg_match('/\b(btn|card|d-flex|col-md-|text-primary|text-success|text-danger|text-muted|progress)\b/', $token)) {
                            continue;
                        }

                        // Check if this token is a known custom class prefix (not Bootstrap)
                        $isCustomClass = false;
                        foreach ($customClassPrefixes as $prefix) {
                            if (str_starts_with($token, $prefix) || $token === $prefix) {
                                $isCustomClass = true;
                                break;
                            }
                        }

                        if (!$isCustomClass) {
                            $isRealViolation = true;
                            break;
                        }
                    }

                    if ($isRealViolation) {
                        break;
                    }
                }

                if ($isRealViolation) {
                    $relPath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
                    $relPath = str_replace('\\', '/', $relPath);
                    $violations[] = sprintf(
                        '%s:%d: %s',
                        $relPath,
                        $lineNumber + 1,
                        trim($line)
                    );
                }
            }
        }

        $count = count($violations);
        $this->assertEmpty(
            $violations,
            "Found {$count} Bootstrap CSS classes in Tailwind project:\n"
                . implode("\n", $violations)
        );
    }
}
