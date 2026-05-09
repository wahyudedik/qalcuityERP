<?php

namespace Tests\Unit\Services\Audit;

use App\Services\Audit\CrudCompletenessAnalyzer;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-Based Tests for CrudCompletenessAnalyzer.
 *
 * Feature: comprehensive-erp-audit
 *
 * These tests generate random model and controller file stubs in a
 * temporary directory, run the CrudCompletenessAnalyzer against them,
 * and verify that the analyzer correctly classifies each entity as
 * Complete, Partial, or Missing.
 */
class CrudCompletenessAnalyzerPropertyTest extends TestCase
{
    use TestTrait;

    private string $tempDir;

    private string $basePath;

    /**
     * The seven standard CRUD methods for a Laravel resource controller.
     */
    private const CRUD_METHODS = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/crud_analyzer_test_'.uniqid();
        mkdir($this->tempDir.'/app/Models', 0777, true);
        mkdir($this->tempDir.'/app/Http/Controllers', 0777, true);
        mkdir($this->tempDir.'/resources/views', 0777, true);
        mkdir($this->tempDir.'/app/Imports', 0777, true);
        mkdir($this->tempDir.'/app/Exports', 0777, true);
        $this->basePath = $this->tempDir;
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Property 6: CRUD Completeness Mapping ───────────────────

    /**
     * Property 6: CRUD Completeness Mapping
     *
     * For any Eloquent model:
     * - With a controller that has ALL 7 CRUD methods → status = "Complete"
     * - With a controller that has SOME but not all CRUD methods → status = "Partial"
     * - WITHOUT a controller → status = "Missing"
     *
     * **Validates: Requirements 6.1, 6.7, 12.5**
     *
     * // Feature: comprehensive-erp-audit, Property 6: CRUD Completeness Mapping
     */
    #[ErisRepeat(repeat: 100)]
    public function test_property_6_crud_completeness_mapping(): void
    {
        $this->forAll(
            Generators::elements(
                'Invoice',
                'Product',
                'Customer',
                'Employee',
                'Asset',
                'Budget',
                'Ticket',
                'Report',
                'Setting',
                'Widget'
            ), // modelName
            Generators::elements('complete', 'partial', 'missing'), // scenario
            Generators::choose(1, 6) // partialMethodCount — how many CRUD methods for partial scenario
        )->then(function (string $modelName, string $scenario, int $partialMethodCount) {
            // Clean up any files from previous iterations
            $this->cleanTempFiles();

            $uniqueSuffix = uniqid();
            $uniqueModelName = $modelName.'C'.$uniqueSuffix;

            // Always create the model file
            $modelPath = $this->basePath.'/app/Models/'.$uniqueModelName.'.php';
            file_put_contents($modelPath, $this->generateModelStub($uniqueModelName));

            // Create controller based on scenario
            if ($scenario !== 'missing') {
                $controllerName = $uniqueModelName.'Controller';
                $controllerPath = $this->basePath.'/app/Http/Controllers/'.$controllerName.'.php';

                if ($scenario === 'complete') {
                    // All 7 CRUD methods
                    $methods = self::CRUD_METHODS;
                } else {
                    // Partial: pick a random subset of methods
                    $methods = array_slice(self::CRUD_METHODS, 0, $partialMethodCount);
                }

                file_put_contents($controllerPath, $this->generateControllerStub($controllerName, $methods));
            }

            // Run the analyzer
            $analyzer = new CrudCompletenessAnalyzer(
                modelPath: $this->basePath.'/app/Models',
                controllerPath: $this->basePath.'/app/Http/Controllers',
                viewPath: $this->basePath.'/resources/views',
                importPath: $this->basePath.'/app/Imports',
                exportPath: $this->basePath.'/app/Exports',
                basePath: $this->basePath,
            );

            $matrix = $analyzer->generateCrudMatrix();

            // Find our model in the matrix
            $entry = null;
            foreach ($matrix as $row) {
                if ($row['model'] === $uniqueModelName) {
                    $entry = $row;
                    break;
                }
            }

            $this->assertNotNull(
                $entry,
                "Model '{$uniqueModelName}' should appear in the CRUD matrix."
            );

            switch ($scenario) {
                case 'complete':
                    $this->assertSame(
                        'Complete',
                        $entry['status'],
                        "Model with ALL 7 CRUD methods should be classified as 'Complete'. "
                            ."model={$uniqueModelName}, present=".implode(',', $entry['present_methods'])
                    );
                    $this->assertEmpty(
                        $entry['missing_methods'],
                        "Complete model should have no missing methods. model={$uniqueModelName}"
                    );
                    $this->assertCount(
                        7,
                        $entry['present_methods'],
                        "Complete model should have all 7 methods present. model={$uniqueModelName}"
                    );
                    break;

                case 'partial':
                    $this->assertSame(
                        'Partial',
                        $entry['status'],
                        "Model with SOME CRUD methods should be classified as 'Partial'. "
                            ."model={$uniqueModelName}, methodCount={$partialMethodCount}, "
                            .'present='.implode(',', $entry['present_methods'])
                    );
                    $this->assertNotEmpty(
                        $entry['missing_methods'],
                        "Partial model should have some missing methods. model={$uniqueModelName}"
                    );
                    $this->assertCount(
                        $partialMethodCount,
                        $entry['present_methods'],
                        "Partial model should have exactly {$partialMethodCount} methods present. "
                            ."model={$uniqueModelName}"
                    );
                    $this->assertCount(
                        7 - $partialMethodCount,
                        $entry['missing_methods'],
                        'Partial model should have exactly '.(7 - $partialMethodCount).' missing methods. '
                            ."model={$uniqueModelName}"
                    );
                    break;

                case 'missing':
                    $this->assertSame(
                        'Missing',
                        $entry['status'],
                        "Model WITHOUT a controller should be classified as 'Missing'. "
                            ."model={$uniqueModelName}"
                    );
                    $this->assertNull(
                        $entry['controller'],
                        "Missing model should have no controller. model={$uniqueModelName}"
                    );
                    $this->assertNull(
                        $entry['controller_file'],
                        "Missing model should have no controller file. model={$uniqueModelName}"
                    );
                    $this->assertSame(
                        self::CRUD_METHODS,
                        $entry['missing_methods'],
                        "Missing model should list all 7 CRUD methods as missing. model={$uniqueModelName}"
                    );
                    break;
            }
        });
    }

    // ── Stub Generators ─────────────────────────────────────────

    /**
     * Generate a minimal Eloquent model stub.
     */
    private function generateModelStub(string $className): string
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
    protected \$fillable = ['name', 'status'];
}
PHP;
    }

    /**
     * Generate a controller stub with the specified public CRUD methods.
     *
     * @param  string  $className  The controller class name
     * @param  string[]  $methods  Which CRUD methods to include
     */
    private function generateControllerStub(string $className, array $methods): string
    {
        $methodBodies = '';
        foreach ($methods as $method) {
            $methodBodies .= <<<PHP

    public function {$method}()
    {
        // {$method} implementation
    }

PHP;
        }

        return <<<PHP
<?php

namespace App\Http\Controllers;

class {$className}
{
{$methodBodies}
}
PHP;
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Remove all PHP files from the temp model and controller directories
     * to ensure a clean state for each iteration.
     */
    private function cleanTempFiles(): void
    {
        $dirs = [
            $this->basePath.'/app/Models',
            $this->basePath.'/app/Http/Controllers',
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            foreach (glob($dir.'/*.php') as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Recursively remove a directory and all its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
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
