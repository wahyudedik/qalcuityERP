<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes Eloquent model classes for common issues:
 * - Missing BelongsToTenant trait on models with tenant_id
 * - Overly permissive mass assignment ($guarded = [])
 * - Relationship definitions referencing missing model classes
 * - Missing SoftDeletes on critical business entities
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * rather than booting the full Laravel app.
 */
class ModelAnalyzer implements AnalyzerInterface
{
    /**
     * Base path to scan for models.
     */
    private string $modelPath;

    /**
     * Project root path (for resolving class names to file paths).
     */
    private string $basePath;

    /**
     * Critical business entities that MUST have SoftDeletes.
     */
    private const CRITICAL_ENTITIES = [
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

    /**
     * Relationship method names to check.
     */
    private const RELATIONSHIP_METHODS = [
        'hasMany',
        'hasOne',
        'belongsTo',
        'belongsToMany',
        'morphTo',
        'morphMany',
        'morphOne',
        'morphToMany',
        'morphedByMany',
        'hasManyThrough',
        'hasOneThrough',
    ];

    public function __construct(?string $modelPath = null, ?string $basePath = null)
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
        $this->modelPath = $modelPath ?? ($this->basePath.'/app/Models');
    }

    /**
     * Run the full analysis across all models.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];
        $modelFiles = $this->discoverModelFiles();

        foreach ($modelFiles as $filePath) {
            $modelFindings = $this->analyzeFile($filePath);
            array_push($findings, ...$modelFindings);
        }

        return $findings;
    }

    /**
     * Analyze a single model class by its fully-qualified class name.
     *
     * @param  string  $modelClass  Fully-qualified class name
     * @return AuditFinding[]
     */
    public function analyzeModel(string $modelClass): array
    {
        $filePath = $this->resolveFilePath($modelClass);
        if ($filePath === null || ! file_exists($filePath)) {
            return [];
        }

        return $this->analyzeFile($filePath);
    }

    /**
     * Analyze a single model file by its file path.
     *
     * @param  string  $filePath  Absolute path to the model PHP file
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
                title: 'Unreadable model file',
                description: "Could not read model file: {$filePath}.",
                file: $this->relativePath($filePath),
                line: null,
                recommendation: 'Check file permissions.',
                metadata: ['file' => $filePath],
            );

            return $findings;
        }

        // Resolve the class name from the file for reporting
        $className = $this->resolveClassName($sourceCode);
        if ($className === null) {
            return $findings;
        }

        // Skip abstract classes, interfaces, and traits
        if ($this->isAbstractOrInterfaceOrTrait($sourceCode)) {
            return $findings;
        }

        // Check tenant trait
        $tenantFinding = $this->checkTenantTrait($className, $sourceCode, $filePath);
        if ($tenantFinding !== null) {
            $findings[] = $tenantFinding;
        }

        // Check mass assignment
        $massFinding = $this->checkMassAssignment($className, $sourceCode, $filePath);
        if ($massFinding !== null) {
            $findings[] = $massFinding;
        }

        // Check relationships
        $relFindings = $this->checkRelationships($className, $sourceCode, $filePath);
        array_push($findings, ...$relFindings);

        // Check soft deletes
        $softDeleteFinding = $this->checkSoftDeletes($className, $sourceCode, $filePath);
        if ($softDeleteFinding !== null) {
            $findings[] = $softDeleteFinding;
        }

        return $findings;
    }

    /**
     * Check if a model with tenant_id references is missing the BelongsToTenant trait.
     *
     * Detects models that have 'tenant_id' in $fillable or reference tenant_id
     * but don't use the BelongsToTenant trait.
     *
     * @param  string  $modelClass  Fully-qualified class name
     * @param  string|null  $sourceCode  Source code (if null, reads from file)
     * @param  string|null  $filePath  File path for reporting
     */
    public function checkTenantTrait(string $modelClass, ?string $sourceCode = null, ?string $filePath = null): ?AuditFinding
    {
        if ($sourceCode === null) {
            $filePath = $filePath ?? $this->resolveFilePath($modelClass);
            if ($filePath === null || ! file_exists($filePath)) {
                return null;
            }
            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                return null;
            }
        }

        // Check if model references tenant_id in $fillable or as a column
        $hasTenantId = $this->modelReferencesTenantId($sourceCode);
        if (! $hasTenantId) {
            return null;
        }

        // Check if model uses BelongsToTenant trait
        $usesTrait = $this->usesTrait($sourceCode, 'BelongsToTenant');
        if ($usesTrait) {
            return null;
        }

        $shortClass = $this->shortClassName($modelClass);
        $line = $this->findTenantIdLine($sourceCode);

        return new AuditFinding(
            category: $this->category(),
            severity: Severity::Critical,
            title: "Missing BelongsToTenant trait on {$shortClass}",
            description: "Model {$shortClass} references tenant_id but does not use the BelongsToTenant trait. "
                .'This means queries on this model are NOT automatically scoped by tenant, '
                .'creating a potential data leakage vulnerability.',
            file: $filePath ? $this->relativePath($filePath) : null,
            line: $line,
            recommendation: "Add `use BelongsToTenant;` to the {$shortClass} model class.",
            metadata: [
                'model' => $modelClass,
                'check' => 'tenant_trait',
            ],
        );
    }

    /**
     * Check if a model uses $guarded = [] (empty guarded array).
     *
     * @param  string  $modelClass  Fully-qualified class name
     * @param  string|null  $sourceCode  Source code (if null, reads from file)
     * @param  string|null  $filePath  File path for reporting
     */
    public function checkMassAssignment(string $modelClass, ?string $sourceCode = null, ?string $filePath = null): ?AuditFinding
    {
        if ($sourceCode === null) {
            $filePath = $filePath ?? $this->resolveFilePath($modelClass);
            if ($filePath === null || ! file_exists($filePath)) {
                return null;
            }
            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                return null;
            }
        }

        // Check for $guarded = [] pattern
        if (! preg_match('/\$guarded\s*=\s*\[\s*\]/', $sourceCode, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $shortClass = $this->shortClassName($modelClass);
        $line = $this->lineNumberAtOffset($sourceCode, $matches[0][1]);

        return new AuditFinding(
            category: $this->category(),
            severity: Severity::High,
            title: "Overly permissive mass assignment in {$shortClass}",
            description: "Model {$shortClass} uses \$guarded = [] which allows mass assignment of ALL fields. "
                .'This is a security vulnerability that could allow attackers to set sensitive fields '
                .'like tenant_id, is_admin, or role via mass assignment.',
            file: $filePath ? $this->relativePath($filePath) : null,
            line: $line,
            recommendation: 'Replace $guarded = [] with an explicit $fillable array listing only the fields that should be mass-assignable.',
            metadata: [
                'model' => $modelClass,
                'check' => 'mass_assignment',
            ],
        );
    }

    /**
     * Check relationship definitions for missing referenced model classes.
     *
     * Parses relationship method definitions and verifies the referenced
     * model class exists in the project.
     *
     * @param  string  $modelClass  Fully-qualified class name
     * @param  string|null  $sourceCode  Source code (if null, reads from file)
     * @param  string|null  $filePath  File path for reporting
     * @return AuditFinding[]
     */
    public function checkRelationships(string $modelClass, ?string $sourceCode = null, ?string $filePath = null): array
    {
        $findings = [];

        if ($sourceCode === null) {
            $filePath = $filePath ?? $this->resolveFilePath($modelClass);
            if ($filePath === null || ! file_exists($filePath)) {
                return $findings;
            }
            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                return $findings;
            }
        }

        $shortClass = $this->shortClassName($modelClass);
        $relationships = $this->extractRelationships($sourceCode);

        foreach ($relationships as $rel) {
            $referencedClass = $rel['referenced_class'];
            $methodName = $rel['method_name'];
            $relType = $rel['relationship_type'];

            // morphTo doesn't reference a specific class
            if ($relType === 'morphTo') {
                continue;
            }

            // Skip if no referenced class could be determined
            if ($referencedClass === null) {
                continue;
            }

            // Resolve the full class path and check if the file exists
            $resolvedPath = $this->resolveModelFilePath($referencedClass, $sourceCode);
            if ($resolvedPath !== null && file_exists($resolvedPath)) {
                continue;
            }

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Missing referenced model in {$shortClass}::{$methodName}()",
                description: "Relationship {$methodName}() in {$shortClass} references model class "
                    ."'{$referencedClass}' which could not be found in the project. "
                    .'This may indicate a typo, a missing model, or an incorrect namespace.',
                file: $filePath ? $this->relativePath($filePath) : null,
                line: $rel['line'],
                recommendation: "Verify that the model class '{$referencedClass}' exists and the namespace is correct.",
                metadata: [
                    'model' => $modelClass,
                    'method' => $methodName,
                    'relationship_type' => $relType,
                    'referenced_class' => $referencedClass,
                    'check' => 'relationship',
                ],
            );
        }

        return $findings;
    }

    /**
     * Check if a critical business entity is missing the SoftDeletes trait.
     *
     * @param  string  $modelClass  Fully-qualified class name
     * @param  string|null  $sourceCode  Source code (if null, reads from file)
     * @param  string|null  $filePath  File path for reporting
     */
    public function checkSoftDeletes(string $modelClass, ?string $sourceCode = null, ?string $filePath = null): ?AuditFinding
    {
        if ($sourceCode === null) {
            $filePath = $filePath ?? $this->resolveFilePath($modelClass);
            if ($filePath === null || ! file_exists($filePath)) {
                return null;
            }
            $sourceCode = @file_get_contents($filePath);
            if ($sourceCode === false) {
                return null;
            }
        }

        $shortClass = $this->shortClassName($modelClass);

        // Only check critical entities
        if (! in_array($shortClass, self::CRITICAL_ENTITIES, true)) {
            return null;
        }

        // Check if model uses SoftDeletes trait
        $usesSoftDeletes = $this->usesTrait($sourceCode, 'SoftDeletes');
        if ($usesSoftDeletes) {
            return null;
        }

        return new AuditFinding(
            category: $this->category(),
            severity: Severity::High,
            title: "Missing SoftDeletes on critical entity {$shortClass}",
            description: "Critical business entity {$shortClass} does not use the SoftDeletes trait. "
                ."Accidental or malicious deletion of {$shortClass} records would result in permanent data loss "
                .'with no ability to recover.',
            file: $filePath ? $this->relativePath($filePath) : null,
            line: null,
            recommendation: "Add `use SoftDeletes;` to the {$shortClass} model and ensure the database table has a `deleted_at` column.",
            metadata: [
                'model' => $modelClass,
                'check' => 'soft_deletes',
            ],
        );
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'model';
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Recursively discover all PHP files under the models directory.
     * Excludes subdirectories like Concerns/ and Scopes/ that contain
     * traits and scopes rather than model classes.
     *
     * @return string[]
     */
    private function discoverModelFiles(): array
    {
        $files = [];

        if (! is_dir($this->modelPath)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->modelPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Skip Concerns/ and Scopes/ subdirectories (traits, not models)
                $relativePath = str_replace($this->modelPath.'/', '', $file->getPathname());
                if (str_starts_with($relativePath, 'Concerns/') || str_starts_with($relativePath, 'Scopes/')) {
                    continue;
                }
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Resolve a fully-qualified class name from PHP source code.
     * Parses the namespace and class declarations.
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
     * Resolve a file path from a fully-qualified class name.
     * Assumes PSR-4 autoloading with App\ mapped to app/.
     */
    private function resolveFilePath(string $className): ?string
    {
        if (str_starts_with($className, 'App\\')) {
            $relativePath = str_replace('\\', '/', substr($className, 4));

            return $this->basePath."/app/{$relativePath}.php";
        }

        return null;
    }

    /**
     * Resolve a model file path from a class reference found in source code.
     * Handles both fully-qualified names and short names (assumes App\Models namespace).
     */
    private function resolveModelFilePath(string $referencedClass, string $sourceCode): ?string
    {
        // If it's already fully qualified
        if (str_contains($referencedClass, '\\')) {
            return $this->resolveFilePath($referencedClass);
        }

        // Check use imports in the source code
        $importedClass = $this->findImportedClass($sourceCode, $referencedClass);
        if ($importedClass !== null) {
            return $this->resolveFilePath($importedClass);
        }

        // Default: assume App\Models namespace
        return $this->basePath."/app/Models/{$referencedClass}.php";
    }

    /**
     * Find a fully-qualified class name from use imports in source code.
     */
    private function findImportedClass(string $sourceCode, string $shortName): ?string
    {
        // Match: use Some\Namespace\ClassName;
        // Match: use Some\Namespace\ClassName as Alias;
        $pattern = '/^\s*use\s+([^;]+\\\\'.preg_quote($shortName, '/').')\s*;/m';
        if (preg_match($pattern, $sourceCode, $matches)) {
            return trim($matches[1]);
        }

        // Check for aliased imports
        $aliasPattern = '/^\s*use\s+([^;]+)\s+as\s+'.preg_quote($shortName, '/').'\s*;/m';
        if (preg_match($aliasPattern, $sourceCode, $matches)) {
            return trim($matches[1]);
        }

        return null;
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
     * Looks for tenant_id in $fillable array or as a property/column reference.
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
     * Check if the source code uses a specific trait.
     */
    private function usesTrait(string $sourceCode, string $traitName): bool
    {
        // Match: use TraitName; or use TraitName, OtherTrait;
        // Also match: use \Full\Namespace\TraitName;
        // Also match: use SoftDeletes, AuditsChanges;
        $patterns = [
            // Direct use statement: use TraitName;
            '/\buse\s+[^;]*\b'.preg_quote($traitName, '/').'\b[^;]*;/',
            // Fully qualified: use \App\Traits\TraitName;
            '/\buse\s+[^;]*\\\\'.preg_quote($traitName, '/').'\b[^;]*;/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sourceCode)) {
                // Make sure this is a trait use inside the class body, not a namespace import
                // Namespace imports have the form: use Namespace\Class;
                // Trait uses are inside class body
                // We check by looking for the pattern after the class declaration
                if ($this->isTraitUseInsideClass($sourceCode, $traitName)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if a trait use statement is inside a class body (not a namespace import).
     */
    private function isTraitUseInsideClass(string $sourceCode, string $traitName): bool
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
     * Extract relationship definitions from source code.
     *
     * Returns an array of ['method_name' => string, 'relationship_type' => string,
     *                       'referenced_class' => ?string, 'line' => int]
     */
    private function extractRelationships(string $sourceCode): array
    {
        $relationships = [];
        $lines = explode("\n", $sourceCode);
        $methodPattern = '/^\s*(?:public\s+)?function\s+(\w+)\s*\(/';

        // Build a regex pattern for all relationship types
        $relTypes = implode('|', self::RELATIONSHIP_METHODS);
        $relPattern = '/\$this\s*->\s*('.$relTypes.')\s*\(\s*([^)]*)\)/';

        for ($i = 0; $i < count($lines); $i++) {
            // Look for relationship calls in the source
            if (preg_match($relPattern, $lines[$i], $relMatch)) {
                $relType = $relMatch[1];
                $args = $relMatch[2];

                // Find the enclosing method name
                $methodName = $this->findEnclosingMethod($lines, $i);

                // Extract the referenced class from the first argument
                $referencedClass = $this->extractReferencedClass($args);

                $relationships[] = [
                    'method_name' => $methodName ?? 'unknown',
                    'relationship_type' => $relType,
                    'referenced_class' => $referencedClass,
                    'line' => $i + 1,
                ];
            }
        }

        return $relationships;
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
     * Extract the referenced model class from relationship method arguments.
     *
     * Handles patterns like:
     * - ClassName::class
     * - \App\Models\ClassName::class
     * - 'App\Models\ClassName'
     */
    private function extractReferencedClass(string $args): ?string
    {
        // Match ClassName::class or \Namespace\ClassName::class
        if (preg_match('/([\w\\\\]+)::class/', $args, $matches)) {
            $class = $matches[1];
            // Remove leading backslash
            $class = ltrim($class, '\\');
            // Extract just the short class name if it's a simple reference
            $parts = explode('\\', $class);

            return end($parts);
        }

        // Match string class name: 'App\Models\ClassName'
        if (preg_match('/[\'"]([^"\']+)[\'"]/', $args, $matches)) {
            $class = $matches[1];
            // Only treat as class if it looks like a class name (contains backslash or starts with uppercase)
            if (str_contains($class, '\\') || preg_match('/^[A-Z]/', $class)) {
                $parts = explode('\\', $class);

                return end($parts);
            }
        }

        return null;
    }

    /**
     * Find the line number where tenant_id first appears in the source.
     */
    private function findTenantIdLine(string $sourceCode): ?int
    {
        $lines = explode("\n", $sourceCode);
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'tenant_id')) {
                return $i + 1;
            }
        }

        return null;
    }

    /**
     * Get the line number at a given byte offset in the source code.
     */
    private function lineNumberAtOffset(string $sourceCode, int $offset): int
    {
        return substr_count($sourceCode, "\n", 0, $offset) + 1;
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
