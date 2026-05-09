<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes CRUD completeness across the ERP system:
 * - Maps all Eloquent models to their corresponding controllers
 * - Checks for missing CRUD methods (index/create/store/show/edit/update/destroy)
 * - Classifies each entity as Complete, Partial, or Missing
 * - Checks list views for search, pagination, sorting, filtering, bulk actions
 * - Checks import/export capabilities for major entities
 *
 * Uses file-based static analysis (file_get_contents + regex)
 * rather than booting the full Laravel app.
 *
 * Validates: Requirements 6.1, 6.2, 6.3, 6.6, 6.7
 */
class CrudCompletenessAnalyzer implements AnalyzerInterface
{
    private string $modelPath;

    private string $controllerPath;

    private string $viewPath;

    private string $importPath;

    private string $exportPath;

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

    /**
     * Core business entities that MUST have complete CRUD.
     */
    private const CORE_ENTITIES = [
        'Customer',
        'Supplier',
        'Product',
        'Employee',
        'Invoice',
        'SalesOrder',
        'PurchaseOrder',
        'JournalEntry',
        'Asset',
        'Budget',
        'Project',
    ];

    /**
     * Major entities that should support import/export.
     */
    private const IMPORT_EXPORT_ENTITIES = [
        'Customer',
        'Product',
        'Employee',
        'Supplier',
        'Invoice',
        'SalesOrder',
        'PurchaseOrder',
    ];

    /**
     * List-view features to check on index pages.
     */
    private const LIST_VIEW_FEATURES = [
        'search' => ['/wire:model.*search/i', '/@livewire.*search/i', '/x-data.*search/i', '/\$search/i', '/filter.*search/i', '/name=["\']search["\']/i'],
        'pagination' => ['/\{\{\s*\$\w+->links\(\)/i', '/paginate\s*\(/i', '/->links\(\)/i', '/wire:click.*page/i', '/pagination/i'],
        'sorting' => ['/sortBy/i', '/sort_by/i', '/orderBy/i', '/wire:click.*sort/i', '/@click.*sort/i'],
        'filtering' => ['/wire:model.*filter/i', '/x-model.*filter/i', '/\$filter/i', '/name=["\']filter/i', '/x-data.*filter/i'],
        'bulk_actions' => ['/selectAll/i', '/selectedItems/i', '/bulk/i', '/checkbox.*select/i', '/wire:model.*selected/i'],
    ];

    public function __construct(
        ?string $modelPath = null,
        ?string $controllerPath = null,
        ?string $viewPath = null,
        ?string $importPath = null,
        ?string $exportPath = null,
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

        $this->modelPath = $modelPath ?? ($this->basePath.'/app/Models');
        $this->controllerPath = $controllerPath ?? ($this->basePath.'/app/Http/Controllers');
        $this->viewPath = $viewPath ?? ($this->basePath.'/resources/views');
        $this->importPath = $importPath ?? ($this->basePath.'/app/Imports');
        $this->exportPath = $exportPath ?? ($this->basePath.'/app/Exports');
    }

    /**
     * Run the full CRUD completeness analysis.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        $crudMatrix = $this->generateCrudMatrix();

        // Report findings from the CRUD matrix
        foreach ($crudMatrix as $entry) {
            $modelName = $entry['model'];
            $status = $entry['status'];

            if ($status === 'Missing') {
                $isCoreEntity = in_array($modelName, self::CORE_ENTITIES, true);
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: $isCoreEntity ? Severity::High : Severity::Medium,
                    title: "No controller found for model {$modelName}",
                    description: "Model {$modelName} has no corresponding controller. "
                        .($isCoreEntity
                            ? 'This is a core business entity and requires complete CRUD operations.'
                            : 'The entity may be orphaned or pending implementation.'),
                    file: $entry['model_file'] ?? null,
                    line: null,
                    recommendation: "Create {$modelName}Controller with full CRUD methods or verify the model is intentionally controller-less.",
                    metadata: [
                        'check' => 'crud_completeness',
                        'model' => $modelName,
                        'status' => $status,
                        'missing_methods' => self::CRUD_METHODS,
                    ],
                );
            } elseif ($status === 'Partial') {
                $missingMethods = $entry['missing_methods'] ?? [];
                $isCoreEntity = in_array($modelName, self::CORE_ENTITIES, true);
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: $isCoreEntity ? Severity::High : Severity::Medium,
                    title: "Incomplete CRUD for {$modelName}",
                    description: "Controller for {$modelName} is missing methods: "
                        .implode(', ', $missingMethods).'.',
                    file: $entry['controller_file'] ?? null,
                    line: null,
                    recommendation: 'Add the missing CRUD methods to ensure full data management capability.',
                    metadata: [
                        'check' => 'crud_completeness',
                        'model' => $modelName,
                        'status' => $status,
                        'controller' => $entry['controller'] ?? null,
                        'present_methods' => $entry['present_methods'] ?? [],
                        'missing_methods' => $missingMethods,
                    ],
                );
            }
        }

        // Check list view features for controllers that have an index method
        array_push($findings, ...$this->checkListViewFeatures($crudMatrix));

        // Check import/export capabilities
        array_push($findings, ...$this->checkImportExportCapabilities());

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'crud';
    }

    // ── Model-to-Controller Mapping (Requirement 6.1) ────────────

    /**
     * Map all Eloquent models to their corresponding controllers.
     *
     * Scans app/Models/ for model class names, then searches
     * app/Http/Controllers/ for a matching controller file
     * (e.g., Product → ProductController).
     *
     * @return array<string, array{model: string, model_file: string, controller: string|null, controller_file: string|null}>
     */
    public function mapModelToController(): array
    {
        $mapping = [];

        $modelFiles = $this->discoverPhpFiles($this->modelPath);

        // Build a lookup of all available controllers: short name → file path
        $controllerLookup = $this->buildControllerLookup();

        foreach ($modelFiles as $modelFile) {
            $sourceCode = @file_get_contents($modelFile);
            if ($sourceCode === false) {
                continue;
            }

            if ($this->isAbstractOrInterfaceOrTrait($sourceCode)) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            $shortName = $this->shortClassName($className);
            $controllerName = $shortName.'Controller';

            $controllerFile = $controllerLookup[$controllerName] ?? null;

            $mapping[$shortName] = [
                'model' => $shortName,
                'model_file' => $this->relativePath($modelFile),
                'controller' => $controllerFile !== null ? $controllerName : null,
                'controller_file' => $controllerFile !== null ? $this->relativePath($controllerFile) : null,
            ];
        }

        ksort($mapping);

        return $mapping;
    }

    // ── CRUD Method Check (Requirement 6.2) ──────────────────────

    /**
     * Check which CRUD methods are present in a controller file.
     *
     * Reads the controller source and looks for public method
     * declarations matching the 7 standard CRUD method names.
     *
     * @param  string  $controllerFile  Absolute path to the controller PHP file
     * @return array{present: string[], missing: string[]}
     */
    public function checkCrudMethods(string $controllerFile): array
    {
        $present = [];
        $missing = [];

        if (! is_file($controllerFile)) {
            return ['present' => [], 'missing' => self::CRUD_METHODS];
        }

        $sourceCode = @file_get_contents($controllerFile);
        if ($sourceCode === false) {
            return ['present' => [], 'missing' => self::CRUD_METHODS];
        }

        $publicMethods = $this->extractPublicMethods($sourceCode);

        foreach (self::CRUD_METHODS as $method) {
            if (in_array($method, $publicMethods, true)) {
                $present[] = $method;
            } else {
                $missing[] = $method;
            }
        }

        return ['present' => $present, 'missing' => $missing];
    }

    // ── CRUD Matrix Generation (Requirement 6.7, 12.5) ──────────

    /**
     * Generate a CRUD completeness matrix for all models.
     *
     * Classifies each entity as:
     * - Complete: All 7 CRUD methods present in the controller
     * - Partial: Some but not all CRUD methods present
     * - Missing: No corresponding controller found
     *
     * @return array<int, array{model: string, model_file: string, controller: string|null, controller_file: string|null, status: string, present_methods: string[], missing_methods: string[]}>
     */
    public function generateCrudMatrix(): array
    {
        $matrix = [];
        $modelToController = $this->mapModelToController();

        foreach ($modelToController as $entry) {
            $row = [
                'model' => $entry['model'],
                'model_file' => $entry['model_file'],
                'controller' => $entry['controller'],
                'controller_file' => $entry['controller_file'],
                'status' => 'Missing',
                'present_methods' => [],
                'missing_methods' => self::CRUD_METHODS,
            ];

            if ($entry['controller_file'] !== null) {
                $absolutePath = $this->basePath.'/'.$entry['controller_file'];
                $crudCheck = $this->checkCrudMethods($absolutePath);

                $row['present_methods'] = $crudCheck['present'];
                $row['missing_methods'] = $crudCheck['missing'];

                if (empty($crudCheck['missing'])) {
                    $row['status'] = 'Complete';
                } elseif (! empty($crudCheck['present'])) {
                    $row['status'] = 'Partial';
                } else {
                    // Controller exists but has none of the 7 CRUD methods
                    $row['status'] = 'Partial';
                }
            }

            $matrix[] = $row;
        }

        return $matrix;
    }

    // ── List View Feature Checks (Requirement 6.3) ───────────────

    /**
     * Check list views for search, pagination, sorting, filtering, bulk actions.
     *
     * For each model that has a controller with an index method, look for
     * a corresponding Blade view and check for common list-view features.
     *
     * @param  array  $crudMatrix  The CRUD matrix from generateCrudMatrix()
     * @return AuditFinding[]
     */
    private function checkListViewFeatures(array $crudMatrix): array
    {
        $findings = [];

        foreach ($crudMatrix as $entry) {
            // Only check entities that have an index method
            if (! in_array('index', $entry['present_methods'], true)) {
                continue;
            }

            // Only check core entities for list view features
            if (! in_array($entry['model'], self::CORE_ENTITIES, true)) {
                continue;
            }

            $viewDir = $this->guessViewDirectory($entry['model']);
            if ($viewDir === null || ! is_dir($viewDir)) {
                continue;
            }

            // Look for index.blade.php or similar
            $indexView = $this->findIndexView($viewDir);
            if ($indexView === null) {
                continue;
            }

            $viewContent = @file_get_contents($indexView);
            if ($viewContent === false) {
                continue;
            }

            $missingFeatures = [];
            foreach (self::LIST_VIEW_FEATURES as $feature => $patterns) {
                $found = false;
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $viewContent)) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    $missingFeatures[] = $feature;
                }
            }

            if (! empty($missingFeatures)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Low,
                    title: "List view for {$entry['model']} missing features",
                    description: "The index view for {$entry['model']} is missing: "
                        .implode(', ', $missingFeatures).'.',
                    file: $this->relativePath($indexView),
                    line: null,
                    recommendation: 'Add the missing list view features for better user experience.',
                    metadata: [
                        'check' => 'list_view_features',
                        'model' => $entry['model'],
                        'missing_features' => $missingFeatures,
                    ],
                );
            }
        }

        return $findings;
    }

    // ── Import/Export Capability Check (Requirement 6.6) ─────────

    /**
     * Check import/export capabilities for major entities.
     *
     * Verifies that major entities have corresponding Import and/or
     * Export classes under app/Imports/ and app/Exports/.
     *
     * @return AuditFinding[]
     */
    private function checkImportExportCapabilities(): array
    {
        $findings = [];

        $importFiles = $this->discoverPhpFiles($this->importPath);
        $exportFiles = $this->discoverPhpFiles($this->exportPath);

        // Build lookup of available imports/exports by entity name
        $availableImports = $this->extractEntityNames($importFiles, 'Import');
        $availableExports = $this->extractEntityNames($exportFiles, 'Export');

        foreach (self::IMPORT_EXPORT_ENTITIES as $entity) {
            $hasImport = in_array($entity, $availableImports, true);
            $hasExport = in_array($entity, $availableExports, true);

            if (! $hasImport && ! $hasExport) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Medium,
                    title: "No import/export for {$entity}",
                    description: "{$entity} has neither import nor export capability. "
                        .'Major business entities should support data import and export.',
                    file: null,
                    line: null,
                    recommendation: "Create {$entity}Import and/or {$entity}Export classes.",
                    metadata: [
                        'check' => 'import_export',
                        'entity' => $entity,
                        'has_import' => false,
                        'has_export' => false,
                    ],
                );
            } elseif (! $hasImport) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Low,
                    title: "No import capability for {$entity}",
                    description: "{$entity} has export but no import capability.",
                    file: null,
                    line: null,
                    recommendation: "Create {$entity}Import class for data import.",
                    metadata: [
                        'check' => 'import_export',
                        'entity' => $entity,
                        'has_import' => false,
                        'has_export' => true,
                    ],
                );
            } elseif (! $hasExport) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Low,
                    title: "No export capability for {$entity}",
                    description: "{$entity} has import but no export capability.",
                    file: null,
                    line: null,
                    recommendation: "Create {$entity}Export class for data export.",
                    metadata: [
                        'check' => 'import_export',
                        'entity' => $entity,
                        'has_import' => true,
                        'has_export' => false,
                    ],
                );
            }
        }

        return $findings;
    }

    // ── Private Helpers ──────────────────────────────────────────

    /**
     * Build a lookup of all controller short names to their file paths.
     *
     * Recursively scans the controller directory and maps each
     * controller class name (e.g., "ProductController") to its file path.
     *
     * @return array<string, string> Map of controller short name → absolute file path
     */
    private function buildControllerLookup(): array
    {
        $lookup = [];
        $controllerFiles = $this->discoverPhpFiles($this->controllerPath);

        foreach ($controllerFiles as $file) {
            $sourceCode = @file_get_contents($file);
            if ($sourceCode === false) {
                continue;
            }

            if ($this->isAbstractOrInterfaceOrTrait($sourceCode)) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            $shortName = $this->shortClassName($className);
            $lookup[$shortName] = $file;
        }

        return $lookup;
    }

    /**
     * Extract public method names from PHP source code.
     *
     * @return string[]
     */
    private function extractPublicMethods(string $sourceCode): array
    {
        $methods = [];

        if (preg_match_all(
            '/^\s*public\s+function\s+(\w+)\s*\(/m',
            $sourceCode,
            $matches
        )) {
            $methods = $matches[1];
        }

        // Filter out __construct and other magic methods
        return array_values(array_filter(
            $methods,
            fn (string $m) => ! str_starts_with($m, '__')
        ));
    }

    /**
     * Guess the Blade view directory for a model.
     *
     * Converts model name to snake_case plural directory name
     * (e.g., SalesOrder → sales_orders, Product → products).
     *
     * @return string|null Absolute path to the view directory, or null
     */
    private function guessViewDirectory(string $modelName): ?string
    {
        // Convert PascalCase to snake_case
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));

        // Try plural form first (most common)
        $candidates = [
            $this->viewPath.'/'.$snake,
            $this->viewPath.'/'.$snake.'s',
        ];

        // Special pluralizations
        if (str_ends_with($snake, 'y')) {
            $candidates[] = $this->viewPath.'/'.substr($snake, 0, -1).'ies';
        }

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Find the index view file in a view directory.
     *
     * @return string|null Absolute path to the index view, or null
     */
    private function findIndexView(string $viewDir): ?string
    {
        $candidates = [
            $viewDir.'/index.blade.php',
            $viewDir.'/list.blade.php',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Extract entity names from import/export file paths.
     *
     * Strips the suffix (Import/Export) from the class name to get the entity name.
     *
     * @param  string[]  $files
     * @param  string  $suffix  'Import' or 'Export'
     * @return string[]
     */
    private function extractEntityNames(array $files, string $suffix): array
    {
        $entities = [];

        foreach ($files as $file) {
            $sourceCode = @file_get_contents($file);
            if ($sourceCode === false) {
                continue;
            }

            $className = $this->resolveClassName($sourceCode);
            if ($className === null) {
                continue;
            }

            $shortName = $this->shortClassName($className);

            // Strip the suffix to get the entity name
            if (str_ends_with($shortName, $suffix)) {
                $entityName = substr($shortName, 0, -strlen($suffix));
                if ($entityName !== '') {
                    $entities[] = $entityName;
                }
            }
        }

        return $entities;
    }

    /**
     * Recursively discover all PHP files in a directory.
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
     * Get the short class name (without namespace).
     */
    private function shortClassName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    /**
     * Convert an absolute path to a relative path from the project root.
     *
     * Normalises directory separators to forward slashes for consistency
     * across operating systems.
     */
    private function relativePath(string $absolutePath): string
    {
        // Normalise separators to forward slashes
        $normalised = str_replace('\\', '/', $absolutePath);
        $basePath = str_replace('\\', '/', $this->basePath).'/';

        if (str_starts_with($normalised, $basePath)) {
            return substr($normalised, strlen($basePath));
        }

        return $normalised;
    }
}
