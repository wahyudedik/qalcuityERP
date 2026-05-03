<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\TenantIsolationAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TenantIsolationAnalyzer.
 *
 * Uses temporary fixture files to test detection of:
 * - Missing BelongsToTenant trait on models with tenant_id
 * - Models missing from EnforceTenantIsolation middleware whitelist
 * - Raw queries without tenant_id filtering
 * - Cache keys without tenant_id scoping
 * - Queue jobs without tenant_id context
 *
 * Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5
 */
class TenantIsolationAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir() . '/tenant_isolation_test_' . uniqid();
        mkdir($this->fixtureDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_tenancy(): void
    {
        $analyzer = $this->makeAnalyzer();
        $this->assertSame('tenancy', $analyzer->category());
    }

    // ── Model Trait Coverage (Requirement 7.1) ────────────────────

    public function test_detects_model_with_tenant_id_missing_belongs_to_tenant(): void
    {
        $this->writeFixture('models/Order.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['tenant_id', 'customer_id', 'total'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModelTraitCoverage();

        $this->assertNotEmpty($findings);
        $finding = $findings[0];
        $this->assertSame('tenancy', $finding->category);
        $this->assertSame(Severity::Critical, $finding->severity);
        $this->assertStringContainsString('Missing BelongsToTenant', $finding->title);
        $this->assertSame('model_trait_coverage', $finding->metadata['check']);
    }

    public function test_does_not_flag_model_with_belongs_to_tenant_trait(): void
    {
        $this->writeFixture('models/Product.php', <<<'PHP'
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'price'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModelTraitCoverage();

        $this->assertEmpty($findings);
    }

    public function test_does_not_flag_model_without_tenant_id(): void
    {
        $this->writeFixture('models/Setting.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModelTraitCoverage();

        $this->assertEmpty($findings);
    }

    public function test_detects_tenant_id_in_guarded_array(): void
    {
        $this->writeFixture('models/Report.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = ['tenant_id'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModelTraitCoverage();

        $this->assertNotEmpty($findings);
        $this->assertStringContainsString('Missing BelongsToTenant', $findings[0]->title);
    }

    public function test_skips_abstract_classes_in_model_trait_coverage(): void
    {
        $this->writeFixture('models/BaseModel.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    protected $fillable = ['tenant_id', 'name'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModelTraitCoverage();

        $this->assertEmpty($findings);
    }

    // ── Middleware Whitelist (Requirement 7.3) ─────────────────────

    public function test_detects_model_missing_from_middleware_whitelist(): void
    {
        // Create a model with tenant_id
        $this->writeFixture('models/Invoice.php', <<<'PHP'
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'number', 'total'];
}
PHP);

        // Create middleware that does NOT include Invoice
        $this->writeFixture('middleware/EnforceTenantIsolation.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

class EnforceTenantIsolation
{
    public function handle($request, $next)
    {
        $tenantModels = [
            \App\Models\Product::class,
            \App\Models\Customer::class,
        ];

        return $next($request);
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkMiddlewareWhitelist();

        $missingFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Invoice')
        );

        $this->assertNotEmpty($missingFindings);
        $finding = reset($missingFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertSame('middleware_whitelist', $finding->metadata['check']);
    }

    public function test_does_not_flag_model_in_middleware_whitelist(): void
    {
        $this->writeFixture('models/Product.php', <<<'PHP'
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name'];
}
PHP);

        $this->writeFixture('middleware/EnforceTenantIsolation.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

class EnforceTenantIsolation
{
    public function handle($request, $next)
    {
        $tenantModels = [
            \App\Models\Product::class,
        ];

        return $next($request);
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkMiddlewareWhitelist();

        $productFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Product')
        );

        $this->assertEmpty($productFindings);
    }

    public function test_reports_missing_middleware_file(): void
    {
        // Don't create middleware file — it should report missing
        $this->writeFixture('models/Product.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['tenant_id', 'name'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkMiddlewareWhitelist();

        $missingMiddleware = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing EnforceTenantIsolation')
        );

        $this->assertNotEmpty($missingMiddleware);
        $finding = reset($missingMiddleware);
        $this->assertSame(Severity::Critical, $finding->severity);
    }

    // ── Raw Queries (Requirement 7.2) ─────────────────────────────

    public function test_detects_raw_query_without_tenant_id(): void
    {
        $this->writeFixture('controllers/ReportController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ReportController
{
    public function salesSummary()
    {
        $results = DB::select('SELECT * FROM sales_orders WHERE status = ?', ['completed']);
        return view('reports.sales', compact('results'));
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRawQueries();

        $rawFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => $f->metadata['check'] === 'raw_query_tenant'
        );

        $this->assertNotEmpty($rawFindings);
        $finding = reset($rawFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertStringContainsString('DB::select()', $finding->metadata['query_type']);
    }

    public function test_does_not_flag_raw_query_with_tenant_id(): void
    {
        $this->writeFixture('controllers/ReportController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class ReportController
{
    public function salesSummary()
    {
        $tenantId = auth()->user()->tenant_id;
        $results = DB::select('SELECT * FROM sales_orders WHERE tenant_id = ? AND status = ?', [$tenantId, 'completed']);
        return view('reports.sales', compact('results'));
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRawQueries();

        $this->assertEmpty($findings);
    }

    public function test_detects_db_table_without_tenant_id(): void
    {
        $this->writeFixture('services/StatsService.php', <<<'PHP'
<?php

namespace App\Services;

class StatsService
{
    public function getStats()
    {
        return DB::table('invoices')->where('status', 'paid')->sum('total');
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRawQueries();

        $this->assertNotEmpty($findings);
        $this->assertStringContainsString('DB::table()', $findings[0]->metadata['query_type']);
    }

    public function test_does_not_flag_db_table_with_tenant_id_where(): void
    {
        $this->writeFixture('services/StatsService.php', <<<'PHP'
<?php

namespace App\Services;

class StatsService
{
    public function getStats(int $tenantId)
    {
        return DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->sum('total');
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkRawQueries();

        $this->assertEmpty($findings);
    }

    // ── Cache Keys (Requirement 7.5) ──────────────────────────────

    public function test_detects_cache_key_without_tenant_id(): void
    {
        $cacheFile = $this->fixtureDir . '/cache_services/DashboardCacheService.php';
        mkdir(dirname($cacheFile), 0777, true);
        file_put_contents($cacheFile, <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    public static function getStats(string $cacheKey, callable $callback, int $ttl = 300)
    {
        return Cache::remember($cacheKey, $ttl, $callback);
    }
}
PHP);

        $analyzer = new TenantIsolationAnalyzer(
            modelPath: $this->fixtureDir . '/models',
            controllerPath: $this->fixtureDir . '/controllers',
            servicePath: $this->fixtureDir . '/services',
            middlewarePath: $this->fixtureDir . '/middleware',
            jobPath: $this->fixtureDir . '/jobs',
            basePath: $this->fixtureDir,
            cacheServiceFiles: [$cacheFile],
        );

        $findings = $analyzer->checkCacheKeys();

        $cacheFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => $f->metadata['check'] === 'cache_key_tenant'
        );

        $this->assertNotEmpty($cacheFindings);
        $finding = reset($cacheFindings);
        $this->assertSame(Severity::Medium, $finding->severity);
    }

    public function test_does_not_flag_cache_key_with_tenant_id(): void
    {
        $cacheFile = $this->fixtureDir . '/cache_services/QueryCacheService.php';
        mkdir(dirname($cacheFile), 0777, true);
        file_put_contents($cacheFile, <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QueryCacheService
{
    public function getProductsList(int $tenantId, array $filters = [])
    {
        $cacheKey = "products_list:{$tenantId}:" . md5(json_encode($filters));
        return Cache::remember($cacheKey, 3600, function () use ($tenantId) {
            return [];
        });
    }
}
PHP);

        $analyzer = new TenantIsolationAnalyzer(
            modelPath: $this->fixtureDir . '/models',
            controllerPath: $this->fixtureDir . '/controllers',
            servicePath: $this->fixtureDir . '/services',
            middlewarePath: $this->fixtureDir . '/middleware',
            jobPath: $this->fixtureDir . '/jobs',
            basePath: $this->fixtureDir,
            cacheServiceFiles: [$cacheFile],
        );

        $findings = $analyzer->checkCacheKeys();

        $this->assertEmpty($findings);
    }

    public function test_detects_cache_put_without_tenant_id(): void
    {
        $cacheFile = $this->fixtureDir . '/cache_services/CacheService.php';
        mkdir(dirname($cacheFile), 0777, true);
        file_put_contents($cacheFile, <<<'PHP'
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function setMarketplaceSyncStatus(string $type, string $sku, int $marketplaceId, array $data)
    {
        $key = "sync:{$type}:sku:{$sku}:mp:{$marketplaceId}";
        Cache::put($key, $data, 600);
    }
}
PHP);

        $analyzer = new TenantIsolationAnalyzer(
            modelPath: $this->fixtureDir . '/models',
            controllerPath: $this->fixtureDir . '/controllers',
            servicePath: $this->fixtureDir . '/services',
            middlewarePath: $this->fixtureDir . '/middleware',
            jobPath: $this->fixtureDir . '/jobs',
            basePath: $this->fixtureDir,
            cacheServiceFiles: [$cacheFile],
        );

        $findings = $analyzer->checkCacheKeys();

        $this->assertNotEmpty($findings);
        $this->assertSame('cache_key_tenant', $findings[0]->metadata['check']);
    }

    // ── Queue Jobs (Requirement 7.4) ──────────────────────────────

    public function test_detects_queue_job_without_tenant_id(): void
    {
        $this->writeFixture('jobs/ProcessReport.php', <<<'PHP'
<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $reportType,
    ) {}

    public function handle(): void
    {
        $invoices = Invoice::where('status', 'paid')->get();
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkQueueJobs();

        $jobFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => $f->metadata['check'] === 'queue_job_tenant'
        );

        $this->assertNotEmpty($jobFindings);
        $finding = reset($jobFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertStringContainsString('ProcessReport', $finding->title);
    }

    public function test_does_not_flag_queue_job_with_tenant_id(): void
    {
        $this->writeFixture('jobs/GenerateReport.php', <<<'PHP'
<?php

namespace App\Jobs;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $tenantId,
        public readonly string $reportType,
    ) {}

    public function handle(): void
    {
        $orders = SalesOrder::where('tenant_id', $this->tenantId)->get();
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkQueueJobs();

        $this->assertEmpty($findings);
    }

    public function test_does_not_flag_job_that_does_not_access_tenant_data(): void
    {
        $this->writeFixture('jobs/SendEmail.php', <<<'PHP'
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $subject,
    ) {}

    public function handle(): void
    {
        // Send email - no tenant-scoped data access
        mail($this->email, $this->subject, 'Hello');
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkQueueJobs();

        $this->assertEmpty($findings);
    }

    public function test_does_not_flag_non_queue_class(): void
    {
        $this->writeFixture('jobs/HelperClass.php', <<<'PHP'
<?php

namespace App\Jobs;

use App\Models\Invoice;

class HelperClass
{
    public function process(): void
    {
        $invoices = Invoice::all();
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkQueueJobs();

        $this->assertEmpty($findings);
    }

    public function test_detects_job_with_tenant_scoped_model_static_call(): void
    {
        $this->writeFixture('jobs/SyncStock.php', <<<'PHP'
<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $productId,
    ) {}

    public function handle(): void
    {
        $product = Product::find($this->productId);
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkQueueJobs();

        $this->assertNotEmpty($findings);
        $this->assertStringContainsString('SyncStock', $findings[0]->title);
    }

    // ── Full analyze() Integration ────────────────────────────────

    public function test_analyze_aggregates_all_checks(): void
    {
        // Model with tenant_id but no BelongsToTenant
        $this->writeFixture('models/Order.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['tenant_id', 'total'];
}
PHP);

        // Middleware missing Order
        $this->writeFixture('middleware/EnforceTenantIsolation.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

class EnforceTenantIsolation
{
    public function handle($request, $next)
    {
        $tenantModels = [
            \App\Models\Product::class,
        ];
        return $next($request);
    }
}
PHP);

        // Controller with raw query without tenant_id
        $this->writeFixture('controllers/StatsController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

class StatsController
{
    public function index()
    {
        return DB::table('orders')->sum('total');
    }
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->analyze();

        // Should have findings from multiple checks
        $checks = array_unique(array_map(fn($f) => $f->metadata['check'], $findings));
        $this->assertContains('model_trait_coverage', $checks);
        $this->assertContains('middleware_whitelist', $checks);
        $this->assertContains('raw_query_tenant', $checks);
    }

    // ── Finding Structure ─────────────────────────────────────────

    public function test_findings_have_correct_structure(): void
    {
        $this->writeFixture('models/Order.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['tenant_id', 'total'];
}
PHP);

        $analyzer = $this->makeAnalyzer();
        $findings = $analyzer->checkModelTraitCoverage();

        $this->assertNotEmpty($findings);

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertSame('tenancy', $finding->category);
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
        $analyzer = $this->makeAnalyzer();

        $this->assertSame([], $analyzer->checkModelTraitCoverage());
        $this->assertSame([], $analyzer->checkRawQueries());
        $this->assertSame([], $analyzer->checkQueueJobs());
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function makeAnalyzer(): TenantIsolationAnalyzer
    {
        return new TenantIsolationAnalyzer(
            modelPath: $this->fixtureDir . '/models',
            controllerPath: $this->fixtureDir . '/controllers',
            servicePath: $this->fixtureDir . '/services',
            middlewarePath: $this->fixtureDir . '/middleware',
            jobPath: $this->fixtureDir . '/jobs',
            basePath: $this->fixtureDir,
            cacheServiceFiles: [],
        );
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
