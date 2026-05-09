<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes PHP source files for common query-related issues:
 * - N+1 query patterns (relationship access in loops without eager loading)
 * - Unbounded queries (->get()/::all() without pagination in controller methods)
 * - Raw queries (DB::select, DB::table, DB::raw) missing tenant_id filtering
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * rather than booting the full Laravel app.
 */
class QueryAnalyzer implements AnalyzerInterface
{
    /**
     * Base path to scan for controllers.
     */
    private string $controllerPath;

    /**
     * Base path to scan for services.
     */
    private string $servicePath;

    /**
     * Project root path (for resolving relative paths).
     */
    private string $basePath;

    /**
     * Patterns indicating bounded query usage (pagination, chunking, limiting).
     */
    private const BOUNDED_PATTERNS = [
        'paginate(',
        'simplePaginate(',
        'cursorPaginate(',
        'chunk(',
        'chunkById(',
        'cursor(',
        'limit(',
        'take(',
        'first(',
        'firstOrFail(',
        'find(',
        'findOrFail(',
        'count(',
        'sum(',
        'avg(',
        'min(',
        'max(',
        'exists(',
        'doesntExist(',
        'pluck(',
        'value(',
    ];

    /**
     * Patterns indicating eager loading is present.
     */
    private const EAGER_LOADING_PATTERNS = [
        '/->with\s*\(/',
        '/->load\s*\(/',
        '/->loadMissing\s*\(/',
        '/::with\s*\(/',
    ];

    /**
     * Raw query patterns that need tenant_id filtering.
     */
    private const RAW_QUERY_PATTERNS = [
        '/DB\s*::\s*select\s*\(/',
        '/DB\s*::\s*table\s*\(/',
        '/DB\s*::\s*raw\s*\(/',
        '/DB\s*::\s*statement\s*\(/',
        '/DB\s*::\s*insert\s*\(/',
        '/DB\s*::\s*update\s*\(/',
        '/DB\s*::\s*delete\s*\(/',
    ];

    public function __construct(
        ?string $controllerPath = null,
        ?string $servicePath = null,
        ?string $basePath = null,
    ) {
        if ($basePath !== null) {
            $this->basePath = $basePath;
        } else {
            try {
                $this->basePath = base_path();
            } catch (\Throwable) {
                $this->basePath = getcwd();
            }
        }
        $this->controllerPath = $controllerPath ?? ($this->basePath.'/app/Http/Controllers');
        $this->servicePath = $servicePath ?? ($this->basePath.'/app/Services');
    }

    /**
     * Run the full analysis across controllers and services.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        // Scan controllers for unbounded queries and N+1 patterns
        $controllerFiles = $this->discoverPhpFiles($this->controllerPath);
        foreach ($controllerFiles as $filePath) {
            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            if ($this->isAbstractOrInterfaceOrTrait($sourceCode)) {
                continue;
            }

            array_push($findings, ...$this->detectUnboundedQueries($sourceCode, $className, $filePath));
            array_push($findings, ...$this->detectNPlusOnePatterns($sourceCode, $className, $filePath));
            array_push($findings, ...$this->detectRawQueriesWithoutTenantId($sourceCode, $className, $filePath));
        }

        // Scan services for raw queries without tenant_id and N+1 patterns
        $serviceFiles = $this->discoverPhpFiles($this->servicePath);
        foreach ($serviceFiles as $filePath) {
            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            if ($this->isAbstractOrInterfaceOrTrait($sourceCode)) {
                continue;
            }

            array_push($findings, ...$this->detectRawQueriesWithoutTenantId($sourceCode, $className, $filePath));
            array_push($findings, ...$this->detectNPlusOnePatterns($sourceCode, $className, $filePath));
        }

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'query';
    }

    /**
     * Detect unbounded queries in controller methods.
     *
     * Looks for ->get() or ::all() calls in public methods that don't
     * have pagination, chunking, or limiting nearby.
     *
     * @param  string  $sourceCode  Full file source code
     * @param  string  $className  Fully-qualified class name
     * @param  string  $filePath  Absolute file path
     * @return AuditFinding[]
     */
    public function detectUnboundedQueries(string $sourceCode, string $className, string $filePath): array
    {
        $findings = [];
        $methods = $this->extractPublicMethods($sourceCode);

        foreach ($methods as $method) {
            $methodName = $method['name'];
            $methodBody = $method['body'];

            // Only check methods that look like they return list views (index, list, etc.)
            // or any method that uses ->get() / ::all()
            if (! $this->hasUnboundedQueryCall($methodBody)) {
                continue;
            }

            // Check if the method also has bounded patterns
            if ($this->hasBoundedPattern($methodBody)) {
                continue;
            }

            $shortClass = $this->shortClassName($className);

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Unbounded query in {$shortClass}::{$methodName}()",
                description: "Method {$methodName}() in {$shortClass} uses ->get() or ::all() without "
                    .'pagination, chunking, or result limiting. This can cause memory exhaustion '
                    .'on large datasets.',
                file: $this->relativePath($filePath),
                line: $method['line'],
                recommendation: 'Use ->paginate(), ->simplePaginate(), ->chunk(), ->cursor(), or ->limit() '
                    .'to bound the query results.',
                metadata: [
                    'controller' => $className,
                    'method' => $methodName,
                    'check' => 'unbounded_query',
                ],
            );
        }

        return $findings;
    }

    /**
     * Detect N+1 query patterns.
     *
     * Looks for patterns where a collection is fetched and then
     * relationship properties are accessed in a foreach loop
     * without prior eager loading (->with() or ->load()).
     *
     * @param  string  $sourceCode  Full file source code
     * @param  string  $className  Fully-qualified class name
     * @param  string  $filePath  Absolute file path
     * @return AuditFinding[]
     */
    public function detectNPlusOnePatterns(string $sourceCode, string $className, string $filePath): array
    {
        $findings = [];
        $methods = $this->extractPublicMethods($sourceCode);

        foreach ($methods as $method) {
            $methodName = $method['name'];
            $methodBody = $method['body'];

            // Check if method has eager loading already
            $hasEagerLoading = false;
            foreach (self::EAGER_LOADING_PATTERNS as $pattern) {
                if (preg_match($pattern, $methodBody)) {
                    $hasEagerLoading = true;
                    break;
                }
            }

            if ($hasEagerLoading) {
                continue;
            }

            // Look for collection fetch followed by foreach with relationship access
            if (! $this->hasCollectionFetchInLoop($methodBody)) {
                continue;
            }

            $shortClass = $this->shortClassName($className);

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Potential N+1 query in {$shortClass}::{$methodName}()",
                description: "Method {$methodName}() in {$shortClass} appears to fetch a collection "
                    .'and access relationship properties in a loop without eager loading '
                    .'(->with() or ->load()). This causes N+1 database queries.',
                file: $this->relativePath($filePath),
                line: $method['line'],
                recommendation: "Add ->with(['relationship_name']) to the query to eager load relationships, "
                    .'or use ->load() on the collection before the loop.',
                metadata: [
                    'controller' => $className,
                    'method' => $methodName,
                    'check' => 'n_plus_one',
                ],
            );
        }

        return $findings;
    }

    /**
     * Detect raw queries missing tenant_id filtering.
     *
     * Scans for DB::select(), DB::table(), DB::raw(), DB::statement()
     * calls and checks if tenant_id appears in the query context.
     *
     * @param  string  $sourceCode  Full file source code
     * @param  string  $className  Fully-qualified class name
     * @param  string  $filePath  Absolute file path
     * @return AuditFinding[]
     */
    public function detectRawQueriesWithoutTenantId(string $sourceCode, string $className, string $filePath): array
    {
        $findings = [];
        $lines = explode("\n", $sourceCode);

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            foreach (self::RAW_QUERY_PATTERNS as $pattern) {
                if (! preg_match($pattern, $line)) {
                    continue;
                }

                // Extract a context window around the raw query (current line + next 10 lines)
                $contextEnd = min($i + 10, count($lines) - 1);
                $context = implode("\n", array_slice($lines, $i, $contextEnd - $i + 1));

                // Also look backwards for chained calls (e.g., DB::table(...)->where('tenant_id', ...))
                $contextStart = max($i - 3, 0);
                $fullContext = implode("\n", array_slice($lines, $contextStart, $contextEnd - $contextStart + 1));

                // Check if tenant_id appears in the context
                if ($this->hasTenantIdInContext($fullContext)) {
                    continue;
                }

                // Determine the raw query type for reporting
                $queryType = $this->identifyRawQueryType($line);
                $shortClass = $this->shortClassName($className);
                $methodName = $this->findEnclosingMethod($lines, $i);

                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Raw query without tenant_id in {$shortClass}".($methodName ? "::{$methodName}()" : ''),
                    description: "A {$queryType} call in {$shortClass} does not appear to filter by tenant_id. "
                        ."Raw queries bypass Eloquent's BelongsToTenant global scope, so tenant_id "
                        .'must be explicitly included to prevent cross-tenant data leakage.',
                    file: $this->relativePath($filePath),
                    line: $i + 1,
                    recommendation: 'Add a WHERE tenant_id = ? clause to the raw query, or refactor to use '
                        .'Eloquent models with the BelongsToTenant trait.',
                    metadata: [
                        'class' => $className,
                        'method' => $methodName,
                        'query_type' => $queryType,
                        'check' => 'raw_query_tenant',
                    ],
                );

                // Only report once per line (avoid duplicate findings for same line)
                break;
            }
        }

        return $findings;
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Recursively discover all PHP files under a directory.
     *
     * @return string[]
     */
    private function discoverPhpFiles(string $directory): array
    {
        $files = [];

        if (! is_dir($directory)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
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
     * Resolve a fully-qualified class name from PHP source code.
     */
    private function resolveClassName(string $sourceCode): ?string
    {
        $namespace = null;
        $className = null;

        if (preg_match('/^\s*namespace\s+([^;]+)\s*;/m', $sourceCode, $matches)) {
            $namespace = trim($matches[1]);
        }

        if (preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $sourceCode, $matches)) {
            $className = $matches[1];
        }

        if ($className === null) {
            return null;
        }

        return $namespace ? "{$namespace}\\{$className}" : $className;
    }

    /**
     * Check if the source code defines an abstract class, interface, or trait.
     */
    private function isAbstractOrInterfaceOrTrait(string $sourceCode): bool
    {
        return (bool) preg_match('/^\s*(?:abstract\s+class|interface|trait)\s+/m', $sourceCode);
    }

    /**
     * Check if a method body contains unbounded query calls (->get() or ::all()).
     */
    private function hasUnboundedQueryCall(string $methodBody): bool
    {
        // Match ->get() — but not ->getKey(), ->getAttribute(), etc.
        if (preg_match('/->get\s*\(\s*\)/', $methodBody)) {
            return true;
        }

        // Match ::all()
        if (preg_match('/::all\s*\(\s*\)/', $methodBody)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a method body contains bounded query patterns.
     */
    private function hasBoundedPattern(string $methodBody): bool
    {
        foreach (self::BOUNDED_PATTERNS as $pattern) {
            if (str_contains($methodBody, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a method body has a collection fetch followed by a foreach loop
     * that accesses relationship-like properties (->relationship pattern).
     *
     * Detects patterns like:
     *   $items = Model::all();
     *   foreach ($items as $item) {
     *       $item->relationship->property;
     *   }
     */
    private function hasCollectionFetchInLoop(string $methodBody): bool
    {
        // Look for a variable assignment with ->get() or ::all()
        $hasCollectionFetch = preg_match(
            '/\$(\w+)\s*=\s*[^;]*(?:->get\s*\(|::all\s*\()/s',
            $methodBody,
            $fetchMatch
        );

        if (! $hasCollectionFetch) {
            return false;
        }

        $collectionVar = $fetchMatch[1];

        // Look for a foreach loop iterating over that variable
        // and accessing a chained relationship (e.g., $item->relation->field or $item->relation)
        $foreachPattern = '/foreach\s*\(\s*\$'.preg_quote($collectionVar, '/').'\s+as\s+\$(\w+)\s*\)/';
        if (! preg_match($foreachPattern, $methodBody, $foreachMatch)) {
            return false;
        }

        $itemVar = $foreachMatch[1];

        // Check if the loop body accesses a chained property (potential relationship)
        // Pattern: $item->something->something (two levels of property access)
        $relationAccessPattern = '/\$'.preg_quote($itemVar, '/').'->\w+->\w+/';

        return (bool) preg_match($relationAccessPattern, $methodBody);
    }

    /**
     * Check if tenant_id appears in the context around a raw query.
     */
    private function hasTenantIdInContext(string $context): bool
    {
        // Check for tenant_id in various forms
        $patterns = [
            '/tenant_id/',
            '/tenantId/',
            '/tenant\.id/',
            '/\$tid/',
            '/\$tenantId/',
            '/auth\(\)\s*->\s*user\(\)\s*->\s*tenant_id/',
            '/\$this\s*->\s*tid\s*\(/',
            '/->where\s*\(\s*[\'"]tenant_id[\'"]/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Identify the type of raw query from a source line.
     */
    private function identifyRawQueryType(string $line): string
    {
        if (preg_match('/DB\s*::\s*select/', $line)) {
            return 'DB::select()';
        }
        if (preg_match('/DB\s*::\s*table/', $line)) {
            return 'DB::table()';
        }
        if (preg_match('/DB\s*::\s*raw/', $line)) {
            return 'DB::raw()';
        }
        if (preg_match('/DB\s*::\s*statement/', $line)) {
            return 'DB::statement()';
        }
        if (preg_match('/DB\s*::\s*insert/', $line)) {
            return 'DB::insert()';
        }
        if (preg_match('/DB\s*::\s*update/', $line)) {
            return 'DB::update()';
        }
        if (preg_match('/DB\s*::\s*delete/', $line)) {
            return 'DB::delete()';
        }

        return 'DB::*()';
    }

    /**
     * Find the method name that encloses a given line number.
     */
    private function findEnclosingMethod(array $lines, int $targetLine): ?string
    {
        for ($i = $targetLine; $i >= 0; $i--) {
            if (preg_match('/^\s*(?:public|protected|private)\s+(?:static\s+)?function\s+(\w+)\s*\(/', $lines[$i], $matches)) {
                return $matches[1];
            }
        }

        return null;
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

            if (preg_match('/^\s*public\s+(?:static\s+)?function\s+(\w+)\s*\(/', $line, $matches)) {
                $methodName = $matches[1];
                $startLine = $i + 1; // 1-indexed

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

            $stripped = $this->stripStringsAndComments($line);

            $braceCount += substr_count($stripped, '{');
            $braceCount -= substr_count($stripped, '}');

            if (! $foundOpenBrace && $braceCount > 0) {
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
     */
    private function stripStringsAndComments(string $line): string
    {
        $line = preg_replace('/\/\/.*$/', '', $line);
        $line = preg_replace('/#.*$/', '', $line);
        $line = preg_replace('/\'(?:[^\'\\\\]|\\\\.)*\'/', '""', $line);
        $line = preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $line);

        return $line;
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
        $basePath = $this->basePath.'/';
        if (str_starts_with($absolutePath, $basePath)) {
            return substr($absolutePath, strlen($basePath));
        }

        return $absolutePath;
    }
}
