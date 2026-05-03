<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\ModelAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ModelAnalyzer.
 *
 * Uses temporary model fixture files to test detection of:
 * - Missing BelongsToTenant trait on models with tenant_id
 * - Overly permissive mass assignment ($guarded = [])
 * - Missing referenced model classes in relationships
 * - Missing SoftDeletes on critical business entities
 *
 * Validates: Requirements 1.3, 7.1, 10.3, 10.7
 */
class ModelAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir() . '/model_analyzer_test_' . uniqid();
        mkdir($this->fixtureDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_model(): void
    {
        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $this->assertSame('model', $analyzer->category());
    }

    // ── Tenant Trait Detection ────────────────────────────────────

    public function test_detects_missing_belongs_to_tenant_trait(): void
    {
        $this->writeFixture('Order.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'total',
    ];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tenantFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing BelongsToTenant')
        );

        $this->assertNotEmpty($tenantFindings, 'Should detect missing BelongsToTenant trait');

        $finding = reset($tenantFindings);
        $this->assertSame('model', $finding->category);
        $this->assertSame(Severity::Critical, $finding->severity);
        $this->assertSame('tenant_trait', $finding->metadata['check']);
    }

    public function test_does_not_flag_model_with_belongs_to_tenant_trait(): void
    {
        $this->writeFixture('Product.php', <<<'PHP'
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'price',
    ];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tenantFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing BelongsToTenant')
        );

        $this->assertEmpty($tenantFindings, 'Should not flag model with BelongsToTenant trait');
    }

    public function test_does_not_flag_model_without_tenant_id(): void
    {
        $this->writeFixture('Setting.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tenantFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing BelongsToTenant')
        );

        $this->assertEmpty($tenantFindings, 'Should not flag model without tenant_id');
    }

    public function test_detects_tenant_id_in_guarded_array(): void
    {
        $this->writeFixture('Report.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = ['tenant_id'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tenantFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing BelongsToTenant')
        );

        $this->assertNotEmpty($tenantFindings, 'Should detect tenant_id in $guarded without BelongsToTenant');
    }

    // ── Mass Assignment Detection ─────────────────────────────────

    public function test_detects_empty_guarded_array(): void
    {
        $this->writeFixture('Unsafe.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unsafe extends Model
{
    protected $guarded = [];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $massFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'mass assignment')
        );

        $this->assertNotEmpty($massFindings, 'Should detect $guarded = []');

        $finding = reset($massFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertSame('mass_assignment', $finding->metadata['check']);
    }

    public function test_does_not_flag_model_with_fillable(): void
    {
        $this->writeFixture('Safe.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Safe extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $massFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'mass assignment')
        );

        $this->assertEmpty($massFindings, 'Should not flag model with explicit $fillable');
    }

    public function test_does_not_flag_model_with_non_empty_guarded(): void
    {
        $this->writeFixture('Guarded.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guarded extends Model
{
    protected $guarded = ['id', 'created_at'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $massFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'mass assignment')
        );

        $this->assertEmpty($massFindings, 'Should not flag model with non-empty $guarded');
    }

    // ── Relationship Detection ────────────────────────────────────

    public function test_detects_missing_referenced_model_class(): void
    {
        $this->writeFixture('Order.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(NonExistentModel::class);
    }
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir, $this->fixtureDir);
        $findings = $analyzer->analyze();

        $relFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing referenced model')
        );

        $this->assertNotEmpty($relFindings, 'Should detect missing referenced model class');

        $finding = reset($relFindings);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertSame('relationship', $finding->metadata['check']);
        $this->assertSame('NonExistentModel', $finding->metadata['referenced_class']);
    }

    public function test_does_not_flag_existing_referenced_model(): void
    {
        // Create both models
        $this->writeFixture('Order.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
PHP);

        $this->writeFixture('OrderItem.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id'];
}
PHP);

        // Use fixtureDir as basePath so model resolution works
        // The analyzer resolves App\Models\X to basePath/app/Models/X.php
        // We need to create the proper directory structure
        $modelsDir = $this->fixtureDir . '/app/Models';
        mkdir($modelsDir, 0777, true);

        // Copy fixtures to proper location
        copy($this->fixtureDir . '/Order.php', $modelsDir . '/Order.php');
        copy($this->fixtureDir . '/OrderItem.php', $modelsDir . '/OrderItem.php');

        $analyzer = new ModelAnalyzer($modelsDir, $this->fixtureDir);
        $findings = $analyzer->analyze();

        $relFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing referenced model')
        );

        $this->assertEmpty($relFindings, 'Should not flag existing referenced model');
    }

    public function test_skips_morph_to_relationships(): void
    {
        $this->writeFixture('Comment.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['body'];

    public function commentable()
    {
        return $this->morphTo();
    }
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $relFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing referenced model')
        );

        $this->assertEmpty($relFindings, 'Should skip morphTo relationships');
    }

    // ── SoftDeletes Detection ─────────────────────────────────────

    public function test_detects_missing_soft_deletes_on_critical_entity(): void
    {
        $this->writeFixture('Asset.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['name', 'value'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $softFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing SoftDeletes')
        );

        $this->assertNotEmpty($softFindings, 'Should detect missing SoftDeletes on Asset');

        $finding = reset($softFindings);
        $this->assertSame(Severity::High, $finding->severity);
        $this->assertSame('soft_deletes', $finding->metadata['check']);
    }

    public function test_does_not_flag_critical_entity_with_soft_deletes(): void
    {
        $this->writeFixture('Invoice.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = ['number', 'total'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $softFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing SoftDeletes')
        );

        $this->assertEmpty($softFindings, 'Should not flag Invoice with SoftDeletes');
    }

    public function test_does_not_flag_non_critical_entity_without_soft_deletes(): void
    {
        $this->writeFixture('Setting.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $softFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing SoftDeletes')
        );

        $this->assertEmpty($softFindings, 'Should not flag non-critical entity');
    }

    public function test_detects_all_critical_entities_without_soft_deletes(): void
    {
        $criticalEntities = [
            'Invoice',
            'SalesOrder',
            'PurchaseOrder',
            'JournalEntry',
            'Employee',
            'Customer',
            'Product',
            'PayrollRun',
            'Asset',
        ];

        foreach ($criticalEntities as $entity) {
            $this->writeFixture("{$entity}.php", <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$entity} extends Model
{
    protected \$fillable = ['name'];
}
PHP);
        }

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $softFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing SoftDeletes')
        );

        $this->assertCount(
            count($criticalEntities),
            $softFindings,
            'Should detect missing SoftDeletes on all critical entities'
        );
    }

    // ── Skipping Abstract/Interface/Trait ─────────────────────────

    public function test_skips_abstract_classes(): void
    {
        $this->writeFixture('BaseModel.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    protected $guarded = [];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip abstract classes');
    }

    public function test_skips_interfaces(): void
    {
        $this->writeFixture('ModelInterface.php', <<<'PHP'
<?php

namespace App\Models;

interface ModelInterface
{
    public function tenant();
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip interfaces');
    }

    public function test_skips_traits(): void
    {
        $this->writeFixture('HasTenant.php', <<<'PHP'
<?php

namespace App\Models;

trait HasTenant
{
    protected $fillable = ['tenant_id'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertEmpty($findings, 'Should skip traits');
    }

    // ── Empty/Nonexistent Directory ───────────────────────────────

    public function test_returns_empty_for_empty_directory(): void
    {
        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    public function test_returns_empty_for_nonexistent_directory(): void
    {
        $analyzer = new ModelAnalyzer('/nonexistent/path');
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    // ── Finding Structure ─────────────────────────────────────────

    public function test_findings_have_correct_structure(): void
    {
        $this->writeFixture('Invoice.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['tenant_id', 'number'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings);

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertSame('model', $finding->category);
            $this->assertInstanceOf(Severity::class, $finding->severity);
            $this->assertNotEmpty($finding->title);
            $this->assertNotEmpty($finding->description);
            $this->assertNotNull($finding->recommendation);
            $this->assertIsArray($finding->metadata);
        }
    }

    // ── analyzeModel with specific class ──────────────────────────

    public function test_analyze_model_with_class_name(): void
    {
        // Create proper directory structure for class resolution
        $modelsDir = $this->fixtureDir . '/app/Models';
        mkdir($modelsDir, 0777, true);

        file_put_contents($modelsDir . '/TestModel.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $guarded = [];
}
PHP);

        $analyzer = new ModelAnalyzer($modelsDir, $this->fixtureDir);
        $findings = $analyzer->analyzeModel('App\\Models\\TestModel');

        $this->assertNotEmpty($findings, 'Should find issues via analyzeModel()');
    }

    // ── Multiple Issues on Same Model ─────────────────────────────

    public function test_detects_multiple_issues_on_same_model(): void
    {
        $this->writeFixture('Employee.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(NonExistentDepartment::class);
    }
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir, $this->fixtureDir);
        $findings = $analyzer->analyze();

        // Should detect: missing SoftDeletes (critical entity) + $guarded = [] + missing referenced model
        $softFindings = array_filter($findings, fn($f) => $f->metadata['check'] === 'soft_deletes');
        $massFindings = array_filter($findings, fn($f) => $f->metadata['check'] === 'mass_assignment');
        $relFindings = array_filter($findings, fn($f) => $f->metadata['check'] === 'relationship');

        $this->assertNotEmpty($softFindings, 'Should detect missing SoftDeletes');
        $this->assertNotEmpty($massFindings, 'Should detect $guarded = []');
        $this->assertNotEmpty($relFindings, 'Should detect missing referenced model');
    }

    // ── Trait Detection with Multiple Traits ──────────────────────

    public function test_detects_belongs_to_tenant_in_multi_trait_use(): void
    {
        $this->writeFixture('Product.php', <<<'PHP'
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant, SoftDeletes, AuditsChanges;

    protected $fillable = ['tenant_id', 'name', 'price'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tenantFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing BelongsToTenant')
        );

        $this->assertEmpty($tenantFindings, 'Should detect BelongsToTenant in multi-trait use statement');
    }

    public function test_detects_soft_deletes_with_fully_qualified_trait(): void
    {
        $this->writeFixture('Customer.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['name'];
}
PHP);

        $analyzer = new ModelAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $softFindings = array_filter(
            $findings,
            fn(AuditFinding $f) => str_contains($f->title, 'Missing SoftDeletes')
        );

        $this->assertEmpty($softFindings, 'Should detect SoftDeletes with fully qualified name');
    }

    // ── Helpers ──────────────────────────────────────────────────

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
