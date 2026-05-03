<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\TenantIsolationAnalyzer;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-Based Tests for TenantIsolationAnalyzer.
 *
 * Feature: comprehensive-erp-audit
 *
 * These tests generate random fixture files (models, middleware, cache services,
 * queue jobs) in a temporary directory, run the TenantIsolationAnalyzer against
 * them, and verify that the analyzer correctly detects (or does not flag)
 * various tenant isolation issues.
 */
class TenantIsolationAnalyzerPropertyTest extends TestCase
{
    use TestTrait;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/tenant_isolation_prop_test_' . uniqid();
        mkdir($this->tempDir . '/models', 0777, true);
        mkdir($this->tempDir . '/controllers', 0777, true);
        mkdir($this->tempDir . '/services', 0777, true);
        mkdir($this->tempDir . '/middleware', 0777, true);
        mkdir($this->tempDir . '/jobs', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Property 3: Tenant Isolation Middleware Whitelist Completeness ──

    /**
     * Property 3: Tenant Isolation Middleware Whitelist Completeness
     *
     * For any Eloquent model that has a tenant_id column and is used in
     * route model binding, the model class SHALL appear in the
     * EnforceTenantIsolation middleware's $tenantModels array, preventing
     * cross-tenant access via URL manipulation.
     *
     * When a model with tenant_id IS in the whitelist, no finding is produced.
     * When a model with tenant_id is NOT in the whitelist, a finding MUST be produced.
     * When a model does NOT have tenant_id, no finding is produced regardless of whitelist.
     *
     * **Validates: Requirements 5.4, 7.3**
     *
     * // Feature: comprehensive-erp-audit, Property 3: Tenant Isolation Middleware Whitelist Completeness
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_3_tenant_isolation_middleware_whitelist_completeness(): void
    {
        $this->forAll(
            Generators::bool(),  // hasTenantId — whether model has tenant_id in $fillable
            Generators::bool(),  // isInWhitelist — whether model appears in middleware whitelist
            Generators::elements(
                'Invoice',
                'Product',
                'Customer',
                'Warehouse',
                'SalesOrder',
                'PurchaseOrder',
                'Employee',
                'Asset',
                'Budget',
                'Project'
            ) // modelName — base model name
        )->then(function (bool $hasTenantId, bool $isInWhitelist, string $modelName) {
            $uniqueModel = $modelName . '_MW' . uniqid();

            // Clean the temp directories for this iteration
            $this->cleanDirectory($this->tempDir . '/models');
            $this->cleanDirectory($this->tempDir . '/middleware');

            // Generate model stub
            $modelSource = $this->generateModelStub($uniqueModel, $hasTenantId, true);
            file_put_contents(
                $this->tempDir . '/models/' . $uniqueModel . '.php',
                $modelSource
            );

            // Generate middleware stub with or without this model in the whitelist
            $whitelistModels = $isInWhitelist
                ? [$uniqueModel]
                : ['SomeOtherModel'];

            $middlewareSource = $this->generateMiddlewareStub($whitelistModels);
            file_put_contents(
                $this->tempDir . '/middleware/EnforceTenantIsolation.php',
                $middlewareSource
            );

            $analyzer = new TenantIsolationAnalyzer(
                modelPath: $this->tempDir . '/models',
                controllerPath: $this->tempDir . '/controllers',
                servicePath: $this->tempDir . '/services',
                middlewarePath: $this->tempDir . '/middleware',
                jobPath: $this->tempDir . '/jobs',
                basePath: $this->tempDir,
                cacheServiceFiles: [],
            );

            $findings = $analyzer->checkMiddlewareWhitelist();

            // Filter findings for our specific model
            $modelFindings = array_filter(
                $findings,
                fn(AuditFinding $f) => str_contains($f->title, $uniqueModel)
                    && ($f->metadata['check'] ?? '') === 'middleware_whitelist'
            );

            if (!$hasTenantId) {
                // Model without tenant_id → should never be flagged
                $this->assertEmpty(
                    $modelFindings,
                    "Model without tenant_id should NOT be flagged for middleware whitelist. "
                        . "model={$uniqueModel}, isInWhitelist=" . ($isInWhitelist ? 'true' : 'false')
                );
            } elseif ($isInWhitelist) {
                // Model with tenant_id AND in whitelist → compliant, no finding
                $this->assertEmpty(
                    $modelFindings,
                    "Model with tenant_id that IS in the whitelist should NOT be flagged. "
                        . "model={$uniqueModel}"
                );
            } else {
                // Model with tenant_id but NOT in whitelist → MUST produce a finding
                $this->assertNotEmpty(
                    $modelFindings,
                    "Model with tenant_id that is NOT in the whitelist MUST be flagged. "
                        . "model={$uniqueModel}"
                );

                $finding = reset($modelFindings);
                $this->assertInstanceOf(AuditFinding::class, $finding);
                $this->assertSame(Severity::High, $finding->severity);
                $this->assertSame('tenancy', $finding->category);
                $this->assertStringContainsString($uniqueModel, $finding->title);
                $this->assertStringContainsString('middleware_whitelist', $finding->metadata['check']);
            }
        });
    }

    // ── Property 15: Cache Key Tenant Scoping ───────────────────

    /**
     * Property 15: Cache Key Tenant Scoping
     *
     * For any cache key generated by a service method that stores
     * tenant-specific data, the cache key string SHALL contain the
     * tenant_id value, preventing cross-tenant cache pollution.
     *
     * When a cache operation includes tenant_id in the key or surrounding
     * context, no finding is produced. When tenant_id is absent, a finding
     * MUST be produced.
     *
     * **Validates: Requirements 7.5**
     *
     * // Feature: comprehensive-erp-audit, Property 15: Cache Key Tenant Scoping
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_15_cache_key_tenant_scoping(): void
    {
        $this->forAll(
            Generators::elements(
                'Cache::remember',
                'Cache::put',
                'Cache::get'
            ), // cacheOperation — the type of cache call
            Generators::bool(),  // hasTenantIdInKey — whether tenant_id is in the cache key
            Generators::elements(
                'DashboardCache',
                'SettingsCache',
                'QueryCache',
                'ReportCache',
                'ProductCache',
                'InventoryCache',
                'SalesCache',
                'UserCache',
                'ConfigCache',
                'AnalyticsCache'
            ) // serviceName — base service class name
        )->then(function (string $cacheOperation, bool $hasTenantIdInKey, string $serviceName) {
            $uniqueService = $serviceName . 'Service_CK' . uniqid();
            $cacheFilePath = $this->tempDir . '/services/' . $uniqueService . '.php';

            $source = $this->generateCacheServiceStub(
                $uniqueService,
                $cacheOperation,
                $hasTenantIdInKey
            );
            file_put_contents($cacheFilePath, $source);

            $analyzer = new TenantIsolationAnalyzer(
                modelPath: $this->tempDir . '/models',
                controllerPath: $this->tempDir . '/controllers',
                servicePath: $this->tempDir . '/services',
                middlewarePath: $this->tempDir . '/middleware',
                jobPath: $this->tempDir . '/jobs',
                basePath: $this->tempDir,
                cacheServiceFiles: [$cacheFilePath],
            );

            $findings = $analyzer->checkCacheKeys();

            // Filter findings for our specific service
            $cacheFindings = array_filter(
                $findings,
                fn(AuditFinding $f) => str_contains($f->title, $uniqueService)
                    && ($f->metadata['check'] ?? '') === 'cache_key_tenant'
            );

            if ($hasTenantIdInKey) {
                // Cache key includes tenant_id → compliant, no finding
                $this->assertEmpty(
                    $cacheFindings,
                    "Cache operation with tenant_id in key should NOT be flagged. "
                        . "service={$uniqueService}, op={$cacheOperation}"
                );
            } else {
                // Cache key missing tenant_id → MUST produce a finding
                $this->assertNotEmpty(
                    $cacheFindings,
                    "Cache operation WITHOUT tenant_id in key MUST be flagged. "
                        . "service={$uniqueService}, op={$cacheOperation}"
                );

                $finding = reset($cacheFindings);
                $this->assertInstanceOf(AuditFinding::class, $finding);
                $this->assertSame(Severity::Medium, $finding->severity);
                $this->assertSame('tenancy', $finding->category);
                $this->assertStringContainsString($uniqueService, $finding->title);
                $this->assertSame('cache_key_tenant', $finding->metadata['check']);
            }

            @unlink($cacheFilePath);
        });
    }

    // ── Property 14: Queue Job Tenant Context ───────────────────

    /**
     * Property 14: Queue Job Tenant Context
     *
     * For any queue job class that processes tenant-scoped data (accesses
     * models using BelongsToTenant), the job constructor SHALL accept a
     * tenant_id parameter, and all queries within the job SHALL be scoped
     * to that tenant_id.
     *
     * When a job accesses tenant-scoped data WITHOUT tenant_id in its
     * constructor, a finding MUST be produced. When the job has tenant_id
     * or does not access tenant-scoped data, no finding is produced.
     *
     * **Validates: Requirements 7.4**
     *
     * // Feature: comprehensive-erp-audit, Property 14: Queue Job Tenant Context
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_14_queue_job_tenant_context(): void
    {
        $this->forAll(
            Generators::bool(),  // accessesTenantData — whether job uses tenant-scoped models
            Generators::bool(),  // hasTenantIdParam — whether constructor accepts tenant_id
            Generators::elements(
                'ProcessReport',
                'SyncInventory',
                'GenerateInvoice',
                'CalculatePayroll',
                'UpdateStock',
                'SendNotification',
                'ExportData',
                'ImportRecords',
                'RecalculateCosts',
                'ArchiveRecords'
            ), // jobName — base job class name
            Generators::elements(
                'Invoice',
                'Product',
                'SalesOrder',
                'Employee',
                'Customer',
                'Warehouse',
                'Asset',
                'PurchaseOrder'
            ) // tenantModel — the tenant-scoped model accessed by the job
        )->then(function (bool $accessesTenantData, bool $hasTenantIdParam, string $jobName, string $tenantModel) {
            $uniqueJob = $jobName . '_QJ' . uniqid();

            // Clean the jobs directory for this iteration
            $this->cleanDirectory($this->tempDir . '/jobs');

            $source = $this->generateQueueJobStub(
                $uniqueJob,
                $accessesTenantData,
                $hasTenantIdParam,
                $tenantModel
            );
            file_put_contents(
                $this->tempDir . '/jobs/' . $uniqueJob . '.php',
                $source
            );

            $analyzer = new TenantIsolationAnalyzer(
                modelPath: $this->tempDir . '/models',
                controllerPath: $this->tempDir . '/controllers',
                servicePath: $this->tempDir . '/services',
                middlewarePath: $this->tempDir . '/middleware',
                jobPath: $this->tempDir . '/jobs',
                basePath: $this->tempDir,
                cacheServiceFiles: [],
            );

            $findings = $analyzer->checkQueueJobs();

            // Filter findings for our specific job
            $jobFindings = array_filter(
                $findings,
                fn(AuditFinding $f) => str_contains($f->title, $uniqueJob)
                    && ($f->metadata['check'] ?? '') === 'queue_job_tenant'
            );

            if (!$accessesTenantData) {
                // Job does not access tenant-scoped data → should never be flagged
                $this->assertEmpty(
                    $jobFindings,
                    "Job that does NOT access tenant-scoped data should NOT be flagged. "
                        . "job={$uniqueJob}, hasTenantIdParam=" . ($hasTenantIdParam ? 'true' : 'false')
                );
            } elseif ($hasTenantIdParam) {
                // Job accesses tenant data AND has tenant_id param → compliant
                $this->assertEmpty(
                    $jobFindings,
                    "Job with tenant_id parameter should NOT be flagged. "
                        . "job={$uniqueJob}"
                );
            } else {
                // Job accesses tenant data WITHOUT tenant_id param → MUST flag
                $this->assertNotEmpty(
                    $jobFindings,
                    "Job accessing tenant-scoped data WITHOUT tenant_id MUST be flagged. "
                        . "job={$uniqueJob}, model={$tenantModel}"
                );

                $finding = reset($jobFindings);
                $this->assertInstanceOf(AuditFinding::class, $finding);
                $this->assertSame(Severity::High, $finding->severity);
                $this->assertSame('tenancy', $finding->category);
                $this->assertStringContainsString($uniqueJob, $finding->title);
                $this->assertSame('queue_job_tenant', $finding->metadata['check']);
            }
        });
    }

    // ── Stub Generators ─────────────────────────────────────────

    /**
     * Generate a model stub with optional tenant_id and BelongsToTenant trait.
     */
    private function generateModelStub(string $className, bool $hasTenantId, bool $hasTrait): string
    {
        $traitUse = $hasTrait ? "    use \\App\\Traits\\BelongsToTenant;\n" : '';

        $fillableFields = ["'name'", "'status'"];
        if ($hasTenantId) {
            $fillableFields[] = "'tenant_id'";
        }
        $fillable = implode(', ', $fillableFields);

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
{$traitUse}
    protected \$fillable = [{$fillable}];
}
PHP;
    }

    /**
     * Generate an EnforceTenantIsolation middleware stub with the given models in the whitelist.
     *
     * @param string[] $modelNames Short model class names to include in the whitelist
     */
    private function generateMiddlewareStub(array $modelNames): string
    {
        $entries = array_map(
            fn(string $name) => "            \\App\\Models\\{$name}::class,",
            $modelNames
        );
        $whitelistBody = implode("\n", $entries);

        return <<<PHP
<?php

namespace App\Http\Middleware;

class EnforceTenantIsolation
{
    protected \$tenantModels = [
{$whitelistBody}
    ];

    public function handle(\$request, \$next)
    {
        return \$next(\$request);
    }
}
PHP;
    }

    /**
     * Generate a cache service stub with a cache operation that may or may not
     * include tenant_id in the cache key.
     */
    private function generateCacheServiceStub(
        string $className,
        string $cacheOperation,
        bool $hasTenantIdInKey,
    ): string {
        if ($hasTenantIdInKey) {
            // When tenant_id IS in the key, include $tenantId parameter and use it in the key
            $methodBody = $this->buildCacheMethodBody($cacheOperation, true);

            return <<<PHP
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class {$className}
{
    public function getData(int \$scopeId)
    {
        \$tenant_id = \$scopeId;
{$methodBody}
    }
}
PHP;
        }

        // When tenant_id is NOT in the key, avoid any tenant_id references
        $methodBody = $this->buildCacheMethodBody($cacheOperation, false);

        return <<<PHP
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class {$className}
{
    public function getData(int \$userId = 0)
    {
{$methodBody}
    }
}
PHP;
    }

    /**
     * Build the method body for a cache service stub.
     */
    private function buildCacheMethodBody(string $cacheOperation, bool $hasTenantIdInKey): string
    {
        if ($hasTenantIdInKey) {
            $keyExpr = '"data_cache:{$tenant_id}:items"';
        } else {
            $keyExpr = '"data_cache:items:global"';
        }

        return match ($cacheOperation) {
            'Cache::remember' => "        return Cache::remember({$keyExpr}, 3600, function () {\n            return [];\n        });",
            'Cache::put'      => "        Cache::put({$keyExpr}, ['value' => true], 3600);",
            'Cache::get'      => "        return Cache::get({$keyExpr});",
            default           => "        return Cache::remember({$keyExpr}, 3600, fn() => []);",
        };
    }

    /**
     * Generate a queue job stub that may or may not access tenant-scoped data
     * and may or may not accept tenant_id in its constructor.
     */
    private function generateQueueJobStub(
        string $className,
        bool $accessesTenantData,
        bool $hasTenantIdParam,
        string $tenantModel,
    ): string {
        $constructorParams = $hasTenantIdParam
            ? "        public readonly int \$tenantId,\n        public readonly string \$type,"
            : "        public readonly string \$type,";

        if ($accessesTenantData) {
            $handleBody = "        \$items = {$tenantModel}::where('status', 'active')->get();";
        } else {
            $handleBody = "        // Process non-tenant data\n        logger('Processing job');";
        }

        return <<<PHP
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class {$className} implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
{$constructorParams}
    ) {}

    public function handle(): void
    {
{$handleBody}
    }
}
PHP;
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Remove all files in a directory (but keep the directory itself).
     */
    private function cleanDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Recursively remove a directory and all its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
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
