<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes controller classes for common issues:
 * - Missing try-catch blocks in public methods
 * - Missing authorization checks (middleware or inline)
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * rather than booting the full Laravel app.
 */
class ControllerAnalyzer implements AnalyzerInterface
{
    /**
     * Base path to scan for controllers.
     */
    private string $controllerPath;

    /**
     * Project root path (for resolving class names to file paths).
     */
    private string $basePath;

    /**
     * Authorization patterns that indicate a method has authorization checks.
     * These are searched in the method body source code.
     */
    private const AUTH_PATTERNS = [
        '/\$this\s*->\s*authorize\s*\(/',
        '/abort_unless\s*\(/',
        '/abort_if\s*\(/',
        '/Gate\s*::\s*(allows|denies|check|authorize|inspect)\s*\(/',
        '/\$this\s*->\s*middleware\s*\(/',
        '/can\s*\(/',
        '/cannot\s*\(/',
        '/policy\s*\(/',
    ];

    /**
     * Patterns indicating try-catch error handling in method body.
     */
    private const ERROR_HANDLING_PATTERNS = [
        '/\btry\s*\{/',
    ];

    /**
     * Methods inherited from the base Controller or framework that should be skipped.
     */
    private const SKIP_METHODS = [
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__toString',
        '__invoke',
        '__clone',
        '__debugInfo',
        'middleware',
        'getMiddleware',
        'callAction',
        'authorize',
        'authorizeForUser',
        'authorizeResource',
        'validate',
        'validateWith',
        'validateWithBag',
        'authenticatedUser',
        'authenticatedUserId',
        'tenantId',
    ];

    public function __construct(?string $controllerPath = null, ?string $basePath = null)
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
        $this->controllerPath = $controllerPath ?? ($this->basePath . '/app/Http/Controllers');
    }

    /**
     * Run the full analysis across all controllers.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];
        $controllerFiles = $this->discoverControllerFiles();

        foreach ($controllerFiles as $filePath) {
            $controllerFindings = $this->analyzeFile($filePath);
            array_push($findings, ...$controllerFindings);
        }

        return $findings;
    }

    /**
     * Analyze a single controller class by its fully-qualified class name.
     *
     * @param string $controllerClass Fully-qualified class name
     * @return AuditFinding[]
     */
    public function analyzeController(string $controllerClass): array
    {
        $filePath = $this->resolveFilePath($controllerClass);
        if ($filePath === null || !file_exists($filePath)) {
            return [];
        }

        return $this->analyzeFile($filePath);
    }

    /**
     * Analyze a single controller file by its file path.
     *
     * @param string $filePath Absolute path to the controller PHP file
     * @return AuditFinding[]
     */
    private function analyzeFile(string $filePath): array
    {
        $findings = [];

        $sourceCode = @file_get_contents($filePath);
        if ($sourceCode === false) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Low,
                title: "Unreadable controller file",
                description: "Could not read controller file: {$filePath}.",
                file: $this->relativePath($filePath),
                line: null,
                recommendation: "Check file permissions.",
                metadata: ['file' => $filePath],
            );
            return $findings;
        }

        // Resolve the class name from the file for reporting
        $className = $this->resolveClassName($filePath);
        if ($className === null) {
            return $findings;
        }

        // Check if this is an abstract class or interface — skip those
        if ($this->isAbstractOrInterface($sourceCode)) {
            return $findings;
        }

        $methods = $this->extractPublicMethods($sourceCode);

        foreach ($methods as $method) {
            $methodName = $method['name'];

            if (in_array($methodName, self::SKIP_METHODS, true)) {
                continue;
            }

            $methodBody = $method['body'];

            // Check error handling
            $errorFinding = $this->checkErrorHandling($methodName, $methodBody, $className, $filePath, $method['line']);
            if ($errorFinding !== null) {
                $findings[] = $errorFinding;
            }

            // Check authorization presence
            $authFinding = $this->checkAuthorizationPresence($methodName, $methodBody, $className, $filePath, $method['line']);
            if ($authFinding !== null) {
                $findings[] = $authFinding;
            }
        }

        return $findings;
    }

    /**
     * Check if a public method has try-catch error handling.
     *
     * Methods that only return views or simple responses without
     * database writes are given lower severity.
     */
    public function checkErrorHandling(
        string $methodName,
        string $methodBody,
        string $controllerClass,
        string $filePath,
        int $line
    ): ?AuditFinding {
        // Check if method has try-catch
        foreach (self::ERROR_HANDLING_PATTERNS as $pattern) {
            if (preg_match($pattern, $methodBody)) {
                return null; // Has error handling
            }
        }

        // Determine severity based on whether the method modifies data
        $isDataModifying = $this->isDataModifyingMethod($methodName, $methodBody);
        $severity = $isDataModifying ? Severity::Medium : Severity::Low;

        $shortClass = $this->shortClassName($controllerClass);

        return new AuditFinding(
            category: $this->category(),
            severity: $severity,
            title: "Missing try-catch in {$shortClass}::{$methodName}()",
            description: "Public method {$methodName}() in {$shortClass} lacks try-catch error handling. "
                . ($isDataModifying
                    ? "This method appears to modify data, so unhandled exceptions could leave the system in an inconsistent state."
                    : "Consider wrapping database or external service calls in try-catch blocks."),
            file: $this->relativePath($filePath),
            line: $line,
            recommendation: "Wrap the method body in a try-catch block and return an appropriate error response.",
            metadata: [
                'controller' => $controllerClass,
                'method' => $methodName,
                'data_modifying' => $isDataModifying,
            ],
        );
    }

    /**
     * Check if a public method has authorization checks.
     *
     * Looks for inline authorization patterns (abort_unless, authorize, Gate::, etc.)
     * in the method body. Constructor middleware is also considered.
     */
    public function checkAuthorizationPresence(
        string $methodName,
        string $methodBody,
        string $controllerClass,
        string $filePath,
        int $line
    ): ?AuditFinding {
        // Only flag data-modifying methods (store, update, destroy, etc.)
        // Read-only methods (index, show, view) are lower priority
        $isDataModifying = $this->isDataModifyingMethod($methodName, $methodBody);
        if (!$isDataModifying) {
            return null;
        }

        // Check for inline authorization patterns in method body
        foreach (self::AUTH_PATTERNS as $pattern) {
            if (preg_match($pattern, $methodBody)) {
                return null; // Has authorization
            }
        }

        // Check if the controller constructor sets up middleware
        $sourceCode = @file_get_contents($filePath);
        if ($sourceCode !== false && $this->hasConstructorMiddleware($sourceCode)) {
            return null; // Has middleware-based authorization in constructor
        }

        $shortClass = $this->shortClassName($controllerClass);

        return new AuditFinding(
            category: $this->category(),
            severity: Severity::High,
            title: "Missing authorization in {$shortClass}::{$methodName}()",
            description: "Data-modifying method {$methodName}() in {$shortClass} has no visible authorization check. "
                . "No abort_unless/abort_if, \$this->authorize(), Gate:: check, or constructor middleware was found. "
                . "Note: Route-level middleware may provide authorization — verify in routes/web.php.",
            file: $this->relativePath($filePath),
            line: $line,
            recommendation: "Add authorization via route middleware (permission: or role:), "
                . "inline \$this->authorize(), or abort_unless() checks.",
            metadata: [
                'controller' => $controllerClass,
                'method' => $methodName,
            ],
        );
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'controller';
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Recursively discover all PHP files under the controllers directory.
     *
     * @return string[]
     */
    private function discoverControllerFiles(): array
    {
        $files = [];

        if (!is_dir($this->controllerPath)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->controllerPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Resolve a fully-qualified class name from a PHP file path.
     * Parses the namespace and class declarations from the file.
     */
    private function resolveClassName(string $filePath): ?string
    {
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $namespace = null;
        $className = null;

        // Extract namespace
        if (preg_match('/^\s*namespace\s+([^;]+)\s*;/m', $content, $matches)) {
            $namespace = trim($matches[1]);
        }

        // Extract class name (skip abstract classes and interfaces)
        if (preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $content, $matches)) {
            $className = $matches[1];
        }

        if ($className === null) {
            return null;
        }

        return $namespace ? "{$namespace}\\{$className}" : $className;
    }

    /**
     * Resolve a file path from a fully-qualified class name.
     * Assumes PSR-4 autoloading with App\ mapped to app/.
     */
    private function resolveFilePath(string $className): ?string
    {
        // Convert App\Http\Controllers\FooController to app/Http/Controllers/FooController.php
        if (str_starts_with($className, 'App\\')) {
            $relativePath = str_replace('\\', '/', substr($className, 4));
            return $this->basePath . "/app/{$relativePath}.php";
        }

        return null;
    }

    /**
     * Check if the source code defines an abstract class or interface.
     */
    private function isAbstractOrInterface(string $sourceCode): bool
    {
        return (bool) preg_match('/^\s*(?:abstract\s+class|interface)\s+/m', $sourceCode);
    }

    /**
     * Extract public methods from source code with their bodies and line numbers.
     *
     * Returns an array of ['name' => string, 'body' => string, 'line' => int].
     */
    private function extractPublicMethods(string $sourceCode): array
    {
        $methods = [];
        $lines = explode("\n", $sourceCode);
        $totalLines = count($lines);

        for ($i = 0; $i < $totalLines; $i++) {
            $line = $lines[$i];

            // Match public function declarations
            if (preg_match('/^\s*public\s+(?:static\s+)?function\s+(\w+)\s*\(/', $line, $matches)) {
                $methodName = $matches[1];
                $startLine = $i + 1; // 1-indexed

                // Find the method body by tracking braces
                $body = $this->extractMethodBody($lines, $i);

                if ($body !== null) {
                    $methods[] = [
                        'name' => $methodName,
                        'body' => $body,
                        'line' => $startLine,
                    ];
                }
            }
        }

        return $methods;
    }

    /**
     * Extract the body of a method starting from the function declaration line.
     * Tracks opening/closing braces to find the complete method body.
     */
    private function extractMethodBody(array $lines, int $startIndex): ?string
    {
        $totalLines = count($lines);
        $braceCount = 0;
        $foundOpenBrace = false;
        $bodyLines = [];

        for ($i = $startIndex; $i < $totalLines; $i++) {
            $line = $lines[$i];
            $bodyLines[] = $line;

            // Count braces (ignoring those in strings/comments for simplicity)
            $stripped = $this->stripStringsAndComments($line);

            $braceCount += substr_count($stripped, '{');
            $braceCount -= substr_count($stripped, '}');

            if (!$foundOpenBrace && $braceCount > 0) {
                $foundOpenBrace = true;
            }

            if ($foundOpenBrace && $braceCount <= 0) {
                return implode("\n", $bodyLines);
            }
        }

        return null;
    }

    /**
     * Strip string literals and comments from a line for brace counting.
     * This is a simplified approach — handles most common cases.
     */
    private function stripStringsAndComments(string $line): string
    {
        // Remove single-line comments
        $line = preg_replace('/\/\/.*$/', '', $line);
        $line = preg_replace('/#.*$/', '', $line);

        // Remove string literals (simplified — handles most cases)
        $line = preg_replace('/\'(?:[^\'\\\\]|\\\\.)*\'/', '""', $line);
        $line = preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $line);

        return $line;
    }

    /**
     * Determine if a method is likely data-modifying based on its name and body.
     */
    private function isDataModifyingMethod(string $methodName, string $methodBody): bool
    {
        // Method names that typically modify data
        $modifyingNames = [
            'store',
            'create',
            'save',
            'update',
            'destroy',
            'delete',
            'remove',
            'post',
            'put',
            'patch',
            'import',
            'upload',
            'process',
            'execute',
            'approve',
            'reject',
            'cancel',
            'close',
            'lock',
            'unlock',
            'activate',
            'deactivate',
            'archive',
            'restore',
            'seed',
            'sync',
            'toggle',
            'assign',
            'revoke',
            'reset',
            'checkout',
            'refund',
            'transfer',
            'bulkUpdate',
            'bulkDelete',
            'bulkUndo',
            'massUpdate',
        ];

        // Check method name
        foreach ($modifyingNames as $name) {
            if (stripos($methodName, $name) !== false) {
                return true;
            }
        }

        // Check method body for data-modifying patterns
        $modifyingPatterns = [
            '/->create\s*\(/',
            '/->update\s*\(/',
            '/->delete\s*\(/',
            '/->save\s*\(/',
            '/->forceDelete\s*\(/',
            '/->destroy\s*\(/',
            '/::create\s*\(/',
            '/DB\s*::\s*(?:insert|update|delete|statement)\s*\(/',
        ];

        foreach ($modifyingPatterns as $pattern) {
            if (preg_match($pattern, $methodBody)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the controller has middleware defined in its constructor.
     */
    private function hasConstructorMiddleware(string $sourceCode): bool
    {
        // Look for middleware calls in constructor
        if (preg_match('/function\s+__construct\s*\([^)]*\)[^{]*\{/s', $sourceCode, $matches, PREG_OFFSET_CAPTURE)) {
            $constructorStart = $matches[0][1];
            // Extract constructor body
            $constructorBody = $this->extractBodyFromOffset($sourceCode, $constructorStart);
            if ($constructorBody !== null) {
                return (bool) preg_match('/\$this\s*->\s*middleware\s*\(/', $constructorBody);
            }
        }

        return false;
    }

    /**
     * Extract a method body starting from a given offset in the source code.
     */
    private function extractBodyFromOffset(string $sourceCode, int $offset): ?string
    {
        $length = strlen($sourceCode);
        $braceCount = 0;
        $foundOpenBrace = false;
        $start = $offset;

        for ($i = $offset; $i < $length; $i++) {
            $char = $sourceCode[$i];

            if ($char === '{') {
                $braceCount++;
                if (!$foundOpenBrace) {
                    $foundOpenBrace = true;
                    $start = $i;
                }
            } elseif ($char === '}') {
                $braceCount--;
                if ($foundOpenBrace && $braceCount === 0) {
                    return substr($sourceCode, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }

    /**
     * Get the short class name (without namespace).
     */
    private function shortClassName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Convert an absolute path to a relative path from the project root.
     */
    private function relativePath(string $absolutePath): string
    {
        $basePath = $this->basePath . '/';
        if (str_starts_with($absolutePath, $basePath)) {
            return substr($absolutePath, strlen($basePath));
        }

        return $absolutePath;
    }
}
