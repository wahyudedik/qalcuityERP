<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes multi-tenancy integrity across the ERP system:
 * - Models with tenant_id column vs BelongsToTenant trait usage
 * - EnforceTenantIsolation middleware whitelist completeness
 * - Raw queries (DB::select, DB::table, DB::raw) without tenant_id filtering
 * - Cache keys without tenant_id scoping
 * - Queue jobs without tenant_id context
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * rather than booting the full Laravel app.
 *
 * Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5
 */
class TenantIsolationAnalyzer implements AnalyzerInterface
{
    private string $modelPath;

    private string $controllerPath;

    private string $servicePath;

    private string $middlewarePath;

    private string $jobPath;

    private string $basePath;

    /**
     * Cache service files to scan for tenant-scoped cache keys.
     *
     * @var string[]
     */
    private array $cacheServiceFiles;

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

    /**
     * Cache operation patterns to check for tenant_id scoping.
     */
    private const CACHE_PATTERNS = [
        '/Cache\s*::\s*remember\s*\(/',
        '/Cache\s*::\s*put\s*\(/',
        '/Cache\s*::\s*get\s*\(/',
        '/Cache\s*::\s*has\s*\(/',
        '/Cache\s*::\s*forget\s*\(/',
        '/->remember\s*\(/',
    ];

    public function __construct(
        ?string $modelPath = null,
        ?string $controllerPath = null,
        ?string $servicePath = null,
        ?string $middlewarePath = null,
        ?string $jobPath = null,
        ?string $basePath = null,
        ?array $cacheServiceFiles = null,
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

        $this->modelPath = $modelPath ?? ($this->basePath.'/app/Models');
        $this->controllerPath = $controllerPath ?? ($this->basePath.'/app/Http/Controllers');
        $this->servicePath = $servicePath ?? ($this->basePath.'/app/Services');
        $this->middlewarePath = $middlewarePath ?? ($this->basePath.'/app/Http/Middleware');
        $this->jobPath = $jobPath ?? ($this->basePath.'/app/Jobs');

        $this->cacheServiceFiles = $cacheServiceFiles ?? [
            $this->basePath.'/app/Services/CacheService.php',
            $this->basePath.'/app/Services/DashboardCacheService.php',
            $this->basePath.'/app/Services/SettingsCacheService.php',
            $this->basePath.'/app/Services/QueryCacheService.php',
        ];
    }

    /**
     * Run the full tenant isolation analysis.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        array_push($findings, ...$this->checkModelTraitCoverage());
        array_push($findings, ...$this->checkMiddlewareWhitelist());
        array_push($findings, ...$this->checkRawQueries());
        array_push($findings, ...$this->checkCacheKeys());
        array_push($findings, ...$this->checkQueueJobs());

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'tenancy';
    }

    /**
     * Check that all models with tenant_id use the BelongsToTenant trait.
     *
     * Cross-references tenant_id in $fillable/$guarded with BelongsToTenant trait usage.
     *
     * Validates: Requirement 7.1
     *
     * @return AuditFinding[]
     */
    public function checkModelTraitCoverage(): array
    {
        $findings = [];
        $modelFiles = $this->discoverPhpFiles($this->modelPath);

        foreach ($modelFiles as $filePath) {
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

            if (! $this->modelReferencesTenantId($sourceCode)) {
                continue;
            }

            if ($this->usesTrait($sourceCode, 'BelongsToTenant')) {
                continue;
            }

            $shortClass = $this->shortClassName($className);
            $line = $this->findLineContaining($sourceCode, 'tenant_id');

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Critical,
                title: "Missing BelongsToTenant trait on {$shortClass}",
                description: "Model {$shortClass} references tenant_id but does not use the BelongsToTenant trait. "
                    .'Queries on this model are NOT automatically scoped by tenant, '
                    .'creating a potential data leakage vulnerability.',
                file: $this->relativePath($filePath),
                line: $line,
                recommendation: "Add `use BelongsToTenant;` to the {$shortClass} model class.",
                metadata: [
                    'model' => $className,
                    'check' => 'model_trait_coverage',
                ],
            );
        }

        return $findings;
    }

    /**
     * Check that the EnforceTenantIsolation middleware whitelist includes all
     * models with tenant_id.
     *
     * Reads the middleware file, extracts the $tenantModels array, then compares
     * against all models that have tenant_id in $fillable/$guarded.
     *
     * Validates: Requirement 7.3
     *
     * @return AuditFinding[]
     */
    public function checkMiddlewareWhitelist(): array
    {
        $findings = [];

        $middlewareFile = $this->middlewarePath.'/EnforceTenantIsolation.php';
        if (! file_exists($middlewareFile)) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Critical,
                title: 'Missing EnforceTenantIsolation middleware',
                description: 'The EnforceTenantIsolation middleware file was not found at the expected path.',
                file: null,
                line: null,
                recommendation: 'Create the EnforceTenantIsolation middleware to enforce tenant isolation on route model bindings.',
                metadata: ['check' => 'middleware_whitelist'],
            );

            return $findings;
        }

        $middlewareSource = @file_get_contents($middlewareFile);
        if ($middlewareSource === false) {
            return $findings;
        }

        // Extract model class names from the $tenantModels array
        $whitelistedModels = $this->extractWhitelistedModels($middlewareSource);

        // Find all models with tenant_id
        $modelFiles = $this->discoverPhpFiles($this->modelPath);
        foreach ($modelFiles as $filePath) {
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

            if (! $this->modelReferencesTenantId($sourceCode)) {
                continue;
            }

            $shortClass = $this->shortClassName($className);
            $fullClass = 'App\\Models\\'.$shortClass;

            // Check if this model is in the whitelist
            if (in_array($shortClass, $whitelistedModels, true) || in_array($fullClass, $whitelistedModels, true)) {
                continue;
            }

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: "Model {$shortClass} missing from EnforceTenantIsolation whitelist",
                description: "Model {$shortClass} has a tenant_id column but is not listed in the "
                    ."EnforceTenantIsolation middleware's \$tenantModels array. If this model is used "
                    ."in route model binding, users could access other tenants' data via URL manipulation.",
                file: $this->relativePath($filePath),
                line: null,
                recommendation: "Add \\App\\Models\\{$shortClass}::class to the \$tenantModels array in EnforceTenantIsolation middleware.",
                metadata: [
                    'model' => $fullClass,
                    'check' => 'middleware_whitelist',
                ],
            );
        }

        return $findings;
    }

    /**
     * Check for raw queries (DB::raw/DB::select/DB::table) without tenant_id filtering.
     *
     * Scans controllers and services for raw query patterns and verifies
     * tenant_id appears in the surrounding context.
     *
     * Validates: Requirement 7.2
     *
     * @return AuditFinding[]
     */
    public function checkRawQueries(): array
    {
        $findings = [];

        $scanPaths = [
            $this->controllerPath,
            $this->servicePath,
        ];

        foreach ($scanPaths as $scanPath) {
            $files = $this->discoverPhpFiles($scanPath);
            foreach ($files as $filePath) {
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
            }
        }

        return $findings;
    }

    /**
     * Check that cache keys include tenant_id to prevent cross-tenant cache pollution.
     *
     * Scans cache service files for Cache::put/Cache::get/Cache::remember patterns
     * and checks if the cache key includes tenant_id.
     *
     * Validates: Requirement 7.5
     *
     * @return AuditFinding[]
     */
    public function checkCacheKeys(): array
    {
        $findings = [];

        foreach ($this->cacheServiceFiles as $filePath) {
            if (! file_exists($filePath)) {
                continue;
            }

            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            array_push($findings, ...$this->detectCacheKeysWithoutTenantId($sourceCode, $className, $filePath));
        }

        return $findings;
    }

    /**
     * Check that queue jobs accept and use tenant_id context.
     *
     * Scans job files under app/Jobs/ for classes that access tenant-scoped models
     * but don't accept tenant_id in their constructor.
     *
     * Validates: Requirement 7.4
     *
     * @return AuditFinding[]
     */
    public function checkQueueJobs(): array
    {
        $findings = [];
        $jobFiles = $this->discoverPhpFiles($this->jobPath);

        foreach ($jobFiles as $filePath) {
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

            // Only check classes that implement ShouldQueue
            if (! $this->implementsShouldQueue($sourceCode)) {
                continue;
            }

            // Check if the job accesses tenant-scoped models
            if (! $this->accessesTenantScopedData($sourceCode)) {
                continue;
            }

            // Check if the constructor accepts tenant_id
            if ($this->constructorAcceptsTenantId($sourceCode)) {
                continue;
            }

            $shortClass = $this->shortClassName($className);

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: "Queue job {$shortClass} missing tenant_id context",
                description: "Queue job {$shortClass} accesses tenant-scoped data but does not accept "
                    .'tenant_id in its constructor. Queue jobs run outside the HTTP request context, '
                    .'so the BelongsToTenant global scope may not be active. Without explicit tenant_id, '
                    .'the job could process data from the wrong tenant or fail silently.',
                file: $this->relativePath($filePath),
                line: null,
                recommendation: "Add a \$tenantId parameter to the {$shortClass} constructor and use it "
                    .'to scope all queries within the job.',
                metadata: [
                    'job' => $className,
                    'check' => 'queue_job_tenant',
                ],
            );
        }

        return $findings;
    }

    // ── Raw Query Detection ──────────────────────────────────────

    /**
     * Detect raw queries missing tenant_id filtering in a source file.
     *
     * @param  string  $sourceCode  Full file source code
     * @param  string  $className  Fully-qualified class name
     * @param  string  $filePath  Absolute file path
     * @return AuditFinding[]
     */
    private function detectRawQueriesWithoutTenantId(string $sourceCode, string $className, string $filePath): array
    {
        $findings = [];
        $lines = explode("\n", $sourceCode);

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            foreach (self::RAW_QUERY_PATTERNS as $pattern) {
                if (! preg_match($pattern, $line)) {
                    continue;
                }

                // Extract a context window around the raw query (backward 3 + forward 10 lines)
                $contextStart = max($i - 3, 0);
                $contextEnd = min($i + 10, count($lines) - 1);
                $fullContext = implode("\n", array_slice($lines, $contextStart, $contextEnd - $contextStart + 1));

                if ($this->hasTenantIdInContext($fullContext)) {
                    continue;
                }

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

                // Only report once per line
                break;
            }
        }

        return $findings;
    }

    // ── Cache Key Detection ──────────────────────────────────────

    /**
     * Detect cache operations where the cache key does not include tenant_id.
     *
     * Scans for Cache::remember, Cache::put, Cache::get patterns and checks
     * if the key argument or surrounding context includes tenant_id.
     *
     * @param  string  $sourceCode  Full file source code
     * @param  string  $className  Fully-qualified class name
     * @param  string  $filePath  Absolute file path
     * @return AuditFinding[]
     */
    private function detectCacheKeysWithoutTenantId(string $sourceCode, string $className, string $filePath): array
    {
        $findings = [];
        $lines = explode("\n", $sourceCode);

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            $matchedCacheOp = false;
            foreach (self::CACHE_PATTERNS as $pattern) {
                if (preg_match($pattern, $line)) {
                    $matchedCacheOp = true;
                    break;
                }
            }

            if (! $matchedCacheOp) {
                continue;
            }

            // Extract context window: backward 5 + forward 5 lines
            $contextStart = max($i - 5, 0);
            $contextEnd = min($i + 5, count($lines) - 1);
            $fullContext = implode("\n", array_slice($lines, $contextStart, $contextEnd - $contextStart + 1));

            // Check if tenant_id appears in the cache key or surrounding context
            if ($this->hasTenantIdInContext($fullContext)) {
                continue;
            }

            $shortClass = $this->shortClassName($className);
            $methodName = $this->findEnclosingMethod($lines, $i);

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Cache key without tenant_id in {$shortClass}".($methodName ? "::{$methodName}()" : ''),
                description: "A cache operation in {$shortClass} does not appear to include tenant_id in the "
                    .'cache key. Without tenant_id scoping, cached data from one tenant could be served '
                    .'to another tenant, causing data leakage.',
                file: $this->relativePath($filePath),
                line: $i + 1,
                recommendation: 'Include tenant_id in the cache key to ensure tenant isolation, '
                    .'e.g., "cache_key:{tenant_id}:rest_of_key".',
                metadata: [
                    'class' => $className,
                    'method' => $methodName,
                    'check' => 'cache_key_tenant',
                ],
            );
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
     * Check if the model source code references tenant_id.
     */
    private function modelReferencesTenantId(string $sourceCode): bool
    {
        // Check $fillable array for 'tenant_id'
        if (preg_match('/\$fillable\s*=\s*\[([^\]]*)\]/s', $sourceCode, $matches)) {
            if (preg_match('/[\'"]tenant_id[\'"]/', $matches[1])) {
                return true;
            }
        }

        // Check $guarded array for 'tenant_id'
        if (preg_match('/\$guarded\s*=\s*\[([^\]]*)\]/s', $sourceCode, $matches)) {
            if (preg_match('/[\'"]tenant_id[\'"]/', $matches[1])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the source code uses a specific trait (inside the class body).
     */
    private function usesTrait(string $sourceCode, string $traitName): bool
    {
        // Find the class declaration
        if (! preg_match('/^\s*(?:final\s+)?class\s+\w+/m', $sourceCode, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }

        $classStart = $matches[0][1];
        $afterClass = substr($sourceCode, $classStart);

        // Look for the trait use after the class declaration
        $pattern = '/\buse\s+[^;]*\b'.preg_quote($traitName, '/').'\b[^;]*;/';

        return (bool) preg_match($pattern, $afterClass);
    }

    /**
     * Extract model short names from the EnforceTenantIsolation middleware's $tenantModels array.
     *
     * @return string[] Array of short model class names (e.g., ['Product', 'Warehouse', ...])
     */
    private function extractWhitelistedModels(string $middlewareSource): array
    {
        $models = [];

        // Match patterns like \App\Models\Product::class or App\Models\Product::class
        if (preg_match_all('/\\\\?App\\\\Models\\\\(\w+)::class/', $middlewareSource, $matches)) {
            $models = $matches[1];
        }

        return $models;
    }

    /**
     * Check if tenant_id appears in the context around a query/cache operation.
     */
    private function hasTenantIdInContext(string $context): bool
    {
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
     * Check if a class implements ShouldQueue (is a queue job).
     */
    private function implementsShouldQueue(string $sourceCode): bool
    {
        return (bool) preg_match('/\bimplements\s+[^{]*\bShouldQueue\b/', $sourceCode);
    }

    /**
     * Check if a job class accesses tenant-scoped data.
     *
     * Looks for references to models that typically have tenant_id,
     * or direct tenant_id references in the source.
     */
    private function accessesTenantScopedData(string $sourceCode): bool
    {
        // Check for use of BelongsToTenant models (common model references)
        $tenantModelPatterns = [
            '/\bwhere\s*\(\s*[\'"]tenant_id[\'"]/',
            '/->tenant_id/',
            '/\$this->tenantId/',
            '/BelongsToTenant/',
        ];

        foreach ($tenantModelPatterns as $pattern) {
            if (preg_match($pattern, $sourceCode)) {
                return true;
            }
        }

        // Check for common tenant-scoped model class references in use statements
        // (models that are known to have tenant_id)
        $commonTenantModels = [
            'Product',
            'Customer',
            'Supplier',
            'Invoice',
            'SalesOrder',
            'PurchaseOrder',
            'Employee',
            'JournalEntry',
            'Asset',
            'Warehouse',
            'Quotation',
            'DeliveryOrder',
            'PayrollRun',
            'Attendance',
            'Budget',
            'Project',
            'Contract',
            'ErpNotification',
        ];

        foreach ($commonTenantModels as $model) {
            // Check if the model is used in the class body (not just imported)
            if (preg_match('/\b'.preg_quote($model, '/').'\s*::/', $sourceCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a job's constructor accepts a tenant_id parameter.
     */
    private function constructorAcceptsTenantId(string $sourceCode): bool
    {
        // Find the constructor
        if (! preg_match('/function\s+__construct\s*\(([^)]*)\)/s', $sourceCode, $matches)) {
            return false;
        }

        $constructorParams = $matches[1];

        // Check for tenantId or tenant_id parameter
        return (bool) preg_match('/\$tenantId|\$tenant_id/i', $constructorParams);
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
     * Find the line number where a string first appears in the source.
     */
    private function findLineContaining(string $sourceCode, string $needle): ?int
    {
        $lines = explode("\n", $sourceCode);
        foreach ($lines as $i => $line) {
            if (str_contains($line, $needle)) {
                return $i + 1;
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
        $basePath = $this->basePath.'/';
        if (str_starts_with($absolutePath, $basePath)) {
            return substr($absolutePath, strlen($basePath));
        }

        return $absolutePath;
    }
}
