<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\ModelAnalyzer;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-Based Tests for ModelAnalyzer.
 *
 * Feature: comprehensive-erp-audit
 *
 * These tests generate random model class stubs as temporary PHP files,
 * run the ModelAnalyzer against them, and verify that the analyzer
 * correctly detects (or does not flag) various issues.
 */
class ModelAnalyzerPropertyTest extends TestCase
{
    use TestTrait;

    private string $tempDir;
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a unique temporary directory for model stubs
        $this->tempDir = sys_get_temp_dir() . '/model_analyzer_test_' . uniqid();
        mkdir($this->tempDir . '/app/Models', 0777, true);
        $this->basePath = $this->tempDir;
    }

    protected function tearDown(): void
    {
        // Clean up all temporary files
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Property 2: Tenant Isolation Trait Coverage ──────────────

    /**
     * Property 2: Tenant Isolation Trait Coverage
     *
     * For any Eloquent model whose $fillable array contains 'tenant_id',
     * the ModelAnalyzer SHALL flag the model if it does NOT use the
     * BelongsToTenant trait. Models without tenant_id should never be flagged.
     *
     * **Validates: Requirements 7.1, 7.6**
     *
     * // Feature: comprehensive-erp-audit, Property 2: Tenant Isolation Trait Coverage
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_2_tenant_isolation_trait_coverage(): void
    {
        $this->forAll(
            Generators::bool(),  // hasTenantId — whether model has tenant_id in $fillable
            Generators::bool(),  // hasTrait — whether model uses BelongsToTenant trait
            Generators::elements(
                'Order',
                'Payment',
                'Report',
                'Setting',
                'Widget',
                'Account',
                'Ledger',
                'Entry',
                'Record',
                'Config'
            ) // className — random model name
        )->then(function (bool $hasTenantId, bool $hasTrait, string $className) {
            // Make class name unique to avoid collisions across iterations
            $uniqueClass = $className . '_T' . uniqid();
            $filePath = $this->basePath . '/app/Models/' . $uniqueClass . '.php';

            $source = $this->generateTenantModelStub($uniqueClass, $hasTenantId, $hasTrait);
            file_put_contents($filePath, $source);

            $analyzer = new ModelAnalyzer(
                $this->basePath . '/app/Models',
                $this->basePath
            );

            $finding = $analyzer->checkTenantTrait(
                'App\\Models\\' . $uniqueClass,
                $source,
                $filePath
            );

            if (!$hasTenantId) {
                // No tenant_id → should never produce a finding
                $this->assertNull(
                    $finding,
                    "Model without tenant_id should NOT be flagged. "
                        . "class={$uniqueClass}, hasTenantId=false, hasTrait=" . ($hasTrait ? 'true' : 'false')
                );
            } elseif ($hasTrait) {
                // Has tenant_id AND has trait → compliant, no finding
                $this->assertNull(
                    $finding,
                    "Model with tenant_id AND BelongsToTenant trait should NOT be flagged. "
                        . "class={$uniqueClass}"
                );
            } else {
                // Has tenant_id but missing trait → MUST produce a finding
                $this->assertInstanceOf(
                    AuditFinding::class,
                    $finding,
                    "Model with tenant_id but WITHOUT BelongsToTenant trait MUST be flagged. "
                        . "class={$uniqueClass}"
                );
                $this->assertSame(Severity::Critical, $finding->severity);
                $this->assertStringContainsString('BelongsToTenant', $finding->title);
            }

            @unlink($filePath);
        });
    }

    // ── Property 16: Mass Assignment Protection ─────────────────

    /**
     * Property 16: Mass Assignment Protection
     *
     * For any Eloquent model, the ModelAnalyzer SHALL flag models that use
     * $guarded = [] (empty guarded array). Models with explicit $fillable
     * or non-empty $guarded should NOT be flagged.
     *
     * **Validates: Requirements 10.3**
     *
     * // Feature: comprehensive-erp-audit, Property 16: Mass Assignment Protection
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_16_mass_assignment_protection(): void
    {
        $this->forAll(
            Generators::elements(
                'fillable_explicit',    // $fillable = ['name', 'email']
                'guarded_empty',        // $guarded = []  ← vulnerability
                'guarded_specific',     // $guarded = ['id', 'created_at']
                'fillable_and_guarded', // both defined (fillable takes precedence)
                'neither'               // neither $fillable nor $guarded defined
            ), // massAssignmentConfig
            Generators::elements(
                'Item',
                'Category',
                'Tag',
                'Note',
                'Profile',
                'Address',
                'Comment',
                'Rating',
                'Ticket',
                'Log'
            ) // className
        )->then(function (string $config, string $className) {
            $uniqueClass = $className . '_M' . uniqid();
            $filePath = $this->basePath . '/app/Models/' . $uniqueClass . '.php';

            $source = $this->generateMassAssignmentModelStub($uniqueClass, $config);
            file_put_contents($filePath, $source);

            $analyzer = new ModelAnalyzer(
                $this->basePath . '/app/Models',
                $this->basePath
            );

            $finding = $analyzer->checkMassAssignment(
                'App\\Models\\' . $uniqueClass,
                $source,
                $filePath
            );

            if ($config === 'guarded_empty') {
                // $guarded = [] → MUST be flagged
                $this->assertInstanceOf(
                    AuditFinding::class,
                    $finding,
                    "Model with \$guarded = [] MUST be flagged as a vulnerability. "
                        . "class={$uniqueClass}, config={$config}"
                );
                $this->assertSame(Severity::High, $finding->severity);
                $this->assertStringContainsString('mass assignment', strtolower($finding->title));
            } else {
                // All other configs → should NOT be flagged
                $this->assertNull(
                    $finding,
                    "Model with config '{$config}' should NOT be flagged for mass assignment. "
                        . "class={$uniqueClass}"
                );
            }

            @unlink($filePath);
        });
    }

    // ── Property 1: Model Relationship Integrity ────────────────

    /**
     * Property 1: Model Relationship Integrity
     *
     * For any Eloquent model relationship definition, the ModelAnalyzer SHALL
     * detect when the referenced model class does NOT exist in the project.
     * When the referenced model DOES exist, no finding should be produced.
     *
     * **Validates: Requirements 1.3, 10.4**
     *
     * // Feature: comprehensive-erp-audit, Property 1: Model Relationship Integrity
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_1_model_relationship_integrity(): void
    {
        $this->forAll(
            Generators::elements('hasMany', 'hasOne', 'belongsTo', 'belongsToMany'), // relType
            Generators::bool(), // referencedModelExists — whether the referenced model file exists
            Generators::elements(
                'Alpha',
                'Beta',
                'Gamma',
                'Delta',
                'Epsilon',
                'Zeta',
                'Eta',
                'Theta',
                'Iota',
                'Kappa'
            ), // ownerName — the model that defines the relationship
            Generators::elements(
                'Lambda',
                'Mu',
                'Nu',
                'Xi',
                'Omicron',
                'Pi',
                'Rho',
                'Sigma',
                'Tau',
                'Upsilon'
            )  // targetName — the referenced model
        )->then(function (string $relType, bool $referencedModelExists, string $ownerName, string $targetName) {
            $uniqueOwner = $ownerName . '_R' . uniqid();
            $uniqueTarget = $targetName . '_R' . uniqid();

            $ownerPath = $this->basePath . '/app/Models/' . $uniqueOwner . '.php';
            $targetPath = $this->basePath . '/app/Models/' . $uniqueTarget . '.php';

            // Generate the owner model with a relationship to the target
            $ownerSource = $this->generateRelationshipModelStub(
                $uniqueOwner,
                $uniqueTarget,
                $relType
            );
            file_put_contents($ownerPath, $ownerSource);

            // Conditionally create the target model file
            if ($referencedModelExists) {
                $targetSource = $this->generateMinimalModelStub($uniqueTarget);
                file_put_contents($targetPath, $targetSource);
            }

            $analyzer = new ModelAnalyzer(
                $this->basePath . '/app/Models',
                $this->basePath
            );

            $findings = $analyzer->checkRelationships(
                'App\\Models\\' . $uniqueOwner,
                $ownerSource,
                $ownerPath
            );

            if ($referencedModelExists) {
                // Referenced model exists → no findings expected
                $this->assertEmpty(
                    $findings,
                    "No relationship findings expected when referenced model exists. "
                        . "owner={$uniqueOwner}, target={$uniqueTarget}, relType={$relType}"
                );
            } else {
                // Referenced model missing → MUST produce a finding
                $this->assertNotEmpty(
                    $findings,
                    "Missing referenced model MUST produce a finding. "
                        . "owner={$uniqueOwner}, target={$uniqueTarget}, relType={$relType}"
                );

                $finding = $findings[0];
                $this->assertInstanceOf(AuditFinding::class, $finding);
                $this->assertSame(Severity::Medium, $finding->severity);
                $this->assertStringContainsString($uniqueTarget, $finding->description);
            }

            if (file_exists($ownerPath)) {
                unlink($ownerPath);
            }
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
        });
    }

    // ── Property 5: Soft Deletes on Critical Business Entities ──

    /**
     * Property 5: Soft Deletes on Critical Business Entities
     *
     * For any model classified as a critical business entity (Invoice,
     * SalesOrder, PurchaseOrder, JournalEntry, Employee, Customer, Product,
     * PayrollRun, Asset), the ModelAnalyzer SHALL flag the model if it does
     * NOT use the SoftDeletes trait. Non-critical models should never be
     * flagged regardless of SoftDeletes usage.
     *
     * **Validates: Requirements 6.5, 10.7**
     *
     * // Feature: comprehensive-erp-audit, Property 5: Soft Deletes on Critical Business Entities
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_5_soft_deletes_on_critical_entities(): void
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

        $nonCriticalEntities = [
            'Setting',
            'Widget',
            'Tag',
            'Comment',
            'Notification',
            'LogEntry',
            'Preference',
            'Token',
            'Session',
            'Cache',
        ];

        $this->forAll(
            Generators::bool(),  // isCritical — whether to use a critical entity name
            Generators::bool(),  // hasSoftDeletes — whether model uses SoftDeletes trait
            Generators::choose(0, count($criticalEntities) - 1),    // criticalIndex
            Generators::choose(0, count($nonCriticalEntities) - 1)  // nonCriticalIndex
        )->then(function (bool $isCritical, bool $hasSoftDeletes, int $criticalIdx, int $nonCriticalIdx) use ($criticalEntities, $nonCriticalEntities) {
            // Pick the class name based on whether it's critical or not
            $baseClassName = $isCritical
                ? $criticalEntities[$criticalIdx]
                : $nonCriticalEntities[$nonCriticalIdx];

            // For the file, we use the exact critical name so the analyzer recognizes it
            // We need a unique file but the class name inside must match the critical entity name
            $filePath = $this->basePath . '/app/Models/' . $baseClassName . '.php';

            $source = $this->generateSoftDeleteModelStub($baseClassName, $hasSoftDeletes);
            file_put_contents($filePath, $source);

            $analyzer = new ModelAnalyzer(
                $this->basePath . '/app/Models',
                $this->basePath
            );

            $finding = $analyzer->checkSoftDeletes(
                'App\\Models\\' . $baseClassName,
                $source,
                $filePath
            );

            if (!$isCritical) {
                // Non-critical entity → should never be flagged
                $this->assertNull(
                    $finding,
                    "Non-critical model '{$baseClassName}' should NOT be flagged for missing SoftDeletes."
                );
            } elseif ($hasSoftDeletes) {
                // Critical entity WITH SoftDeletes → compliant, no finding
                $this->assertNull(
                    $finding,
                    "Critical model '{$baseClassName}' WITH SoftDeletes should NOT be flagged."
                );
            } else {
                // Critical entity WITHOUT SoftDeletes → MUST produce a finding
                $this->assertInstanceOf(
                    AuditFinding::class,
                    $finding,
                    "Critical model '{$baseClassName}' WITHOUT SoftDeletes MUST be flagged."
                );
                $this->assertSame(Severity::High, $finding->severity);
                $this->assertStringContainsString('SoftDeletes', $finding->title);
                $this->assertStringContainsString($baseClassName, $finding->title);
            }

            @unlink($filePath);
        });
    }

    // ── Model Stub Generators ───────────────────────────────────

    /**
     * Generate a model stub for tenant isolation testing.
     */
    private function generateTenantModelStub(string $className, bool $hasTenantId, bool $hasTrait): string
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
     * Generate a model stub for mass assignment testing.
     */
    private function generateMassAssignmentModelStub(string $className, string $config): string
    {
        $body = match ($config) {
            'fillable_explicit' => "    protected \$fillable = ['name', 'email', 'status'];",
            'guarded_empty' => "    protected \$guarded = [];",
            'guarded_specific' => "    protected \$guarded = ['id', 'created_at'];",
            'fillable_and_guarded' => "    protected \$fillable = ['name', 'email'];\n    protected \$guarded = ['id'];",
            'neither' => "    // No mass assignment configuration",
        };

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
{$body}
}
PHP;
    }

    /**
     * Generate a model stub with a relationship definition.
     */
    private function generateRelationshipModelStub(string $ownerClass, string $targetClass, string $relType): string
    {
        $methodName = lcfirst($targetClass);
        if (in_array($relType, ['hasMany', 'belongsToMany'])) {
            $methodName .= 's';
        }

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$ownerClass} extends Model
{
    protected \$fillable = ['name'];

    public function {$methodName}()
    {
        return \$this->{$relType}({$targetClass}::class);
    }
}
PHP;
    }

    /**
     * Generate a minimal model stub (used as a referenced target).
     */
    private function generateMinimalModelStub(string $className): string
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
    protected \$fillable = ['name'];
}
PHP;
    }

    /**
     * Generate a model stub for soft deletes testing.
     */
    private function generateSoftDeleteModelStub(string $className, bool $hasSoftDeletes): string
    {
        $traitUse = $hasSoftDeletes ? "    use \\Illuminate\\Database\\Eloquent\\SoftDeletes;\n" : '';

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
{$traitUse}
    protected \$fillable = ['name', 'status'];
}
PHP;
    }

    // ── Helpers ──────────────────────────────────────────────────

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
