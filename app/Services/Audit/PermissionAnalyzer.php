<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

/**
 * Analyzes the permission system for completeness and consistency:
 * - Cross-references PermissionService::MODULES with actual routes
 * - Identifies critical models lacking authorization policies
 * - Detects role escalation paths and inconsistent role checks
 * - Produces route-to-role-permission mapping
 * - Checks healthcare RBACMiddleware integration with main permission system
 *
 * Uses file-based static analysis (file_get_contents + regex).
 *
 * Validates: Requirements 5.1, 5.2, 5.3, 5.6, 5.8
 */
class PermissionAnalyzer implements AnalyzerInterface
{
    private string $basePath;

    private string $permissionServiceFile;

    private string $policyPath;

    private string $controllerPath;

    private string $rbacMiddlewareFile;

    /** @var string[] */
    private array $routeFiles;

    /**
     * Critical models that should have authorization policies.
     */
    private const CRITICAL_MODELS = [
        'Invoice',
        'SalesOrder',
        'PurchaseOrder',
        'JournalEntry',
        'Employee',
        'Customer',
        'Product',
        'PayrollRun',
        'Asset',
        'Budget',
        'Project',
    ];

    /**
     * Role check method patterns to scan for in controllers.
     */
    private const ROLE_CHECK_METHODS = [
        'isSuperAdmin' => 'super_admin',
        'isAdmin' => 'admin',
        'isManager' => 'manager',
        'isStaff' => 'staff',
        'isKasir' => 'kasir',
        'isGudang' => 'gudang',
        'isAffiliate' => 'affiliate',
    ];

    /**
     * Role hierarchy from highest to lowest privilege.
     */
    private const ROLE_HIERARCHY = [
        'super_admin' => 7,
        'admin' => 6,
        'manager' => 5,
        'staff' => 4,
        'kasir' => 3,
        'gudang' => 3,
        'affiliate' => 1,
    ];

    /**
     * Healthcare RBAC roles.
     */
    private const HEALTHCARE_RBAC_ROLES = [
        'superadmin',
        'admin',
        'doctor',
        'nurse',
        'receptionist',
        'pharmacist',
        'lab_technician',
        'radiologist',
        'billing_staff',
        'patient',
    ];

    public function __construct(
        ?string $permissionServiceFile = null,
        ?string $policyPath = null,
        ?string $controllerPath = null,
        ?string $rbacMiddlewareFile = null,
        ?array $routeFiles = null,
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

        $this->permissionServiceFile = $permissionServiceFile
            ?? ($this->basePath.'/app/Services/PermissionService.php');
        $this->policyPath = $policyPath
            ?? ($this->basePath.'/app/Policies');
        $this->controllerPath = $controllerPath
            ?? ($this->basePath.'/app/Http/Controllers');
        $this->rbacMiddlewareFile = $rbacMiddlewareFile
            ?? ($this->basePath.'/app/Http/Middleware/RBACMiddleware.php');
        $this->routeFiles = $routeFiles ?? [
            $this->basePath.'/routes/web.php',
            $this->basePath.'/routes/api.php',
            $this->basePath.'/routes/healthcare.php',
        ];
    }

    /**
     * Run the full permission analysis.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        array_push($findings, ...$this->checkModuleRouteCoverage());
        array_push($findings, ...$this->checkPolicyGaps());
        array_push($findings, ...$this->checkRoleConsistency());

        $matrix = $this->generatePermissionMatrix();
        array_push($findings, ...$this->findingsFromPermissionMatrix($matrix));

        array_push($findings, ...$this->checkHealthcareRbacIntegration());

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'permissions';
    }

    // ── Module Route Coverage (Requirement 5.2) ──────────────────

    /**
     * Cross-reference PermissionService::MODULES with actual routes.
     *
     * Detects:
     * - Modules defined in MODULES that have no corresponding routes
     * - Routes with permission middleware referencing undefined modules
     *
     * @return AuditFinding[]
     */
    public function checkModuleRouteCoverage(): array
    {
        $findings = [];

        $modules = $this->extractModulesFromPermissionService();
        if (empty($modules)) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: 'Cannot read PermissionService MODULES',
                description: 'Could not extract MODULES constant from PermissionService. '
                    .'The permission service file may be missing or malformed.',
                file: $this->relativePath($this->permissionServiceFile),
                line: null,
                recommendation: 'Verify that PermissionService.php exists and contains a MODULES constant.',
                metadata: ['check' => 'module_route_coverage'],
            );

            return $findings;
        }

        $routeModules = $this->extractModulesFromRoutes();

        // Modules defined in MODULES but with no routes using permission:module,action
        foreach (array_keys($modules) as $module) {
            if (! in_array($module, $routeModules, true)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Medium,
                    title: "Module '{$module}' has no permission-protected routes",
                    description: "Module '{$module}' is defined in PermissionService::MODULES with actions ["
                        .implode(', ', $modules[$module])
                        ."] but no routes use 'permission:{$module},*' middleware. "
                        .'This module may be unused or its routes may lack permission enforcement.',
                    file: $this->relativePath($this->permissionServiceFile),
                    line: null,
                    recommendation: "Add 'permission:{$module},<action>' middleware to routes that belong to this module, "
                        ."or remove the module from MODULES if it's no longer needed.",
                    metadata: [
                        'check' => 'module_route_coverage',
                        'module' => $module,
                        'defined_actions' => $modules[$module],
                    ],
                );
            }
        }

        // Routes referencing modules not defined in MODULES
        $definedModuleNames = array_keys($modules);
        foreach ($routeModules as $routeModule) {
            if (! in_array($routeModule, $definedModuleNames, true)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Route references undefined module '{$routeModule}'",
                    description: "A route uses 'permission:{$routeModule},*' middleware but '{$routeModule}' "
                        .'is not defined in PermissionService::MODULES. This permission check will always fail.',
                    file: null,
                    line: null,
                    recommendation: "Add '{$routeModule}' to PermissionService::MODULES or fix the middleware parameter.",
                    metadata: [
                        'check' => 'module_route_coverage',
                        'module' => $routeModule,
                        'direction' => 'route_references_undefined',
                    ],
                );
            }
        }

        return $findings;
    }

    // ── Policy Gaps (Requirement 5.6) ────────────────────────────

    /**
     * Identify critical models lacking authorization policies.
     *
     * Scans app/Policies/ for existing policy files and compares against
     * the CRITICAL_MODELS list.
     *
     * @return AuditFinding[]
     */
    public function checkPolicyGaps(): array
    {
        $findings = [];

        $existingPolicies = $this->discoverExistingPolicies();

        foreach (self::CRITICAL_MODELS as $model) {
            $expectedPolicyName = $model.'Policy';

            if (! in_array($expectedPolicyName, $existingPolicies, true)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Missing authorization policy for {$model}",
                    description: "Critical model {$model} does not have a corresponding {$expectedPolicyName}. "
                        .'Without a policy, authorization checks via $this->authorize() or Gate::allows() '
                        .'cannot be used for fine-grained access control on this model.',
                    file: null,
                    line: null,
                    recommendation: "Create app/Policies/{$expectedPolicyName}.php with viewAny, view, create, "
                        .'update, delete, and restore methods.',
                    metadata: [
                        'check' => 'policy_gaps',
                        'model' => $model,
                        'expected_policy' => $expectedPolicyName,
                    ],
                );
            }
        }

        return $findings;
    }

    // ── Role Consistency (Requirement 5.8) ───────────────────────

    /**
     * Detect role escalation paths and inconsistent role checks in controllers.
     *
     * Scans controller files for role check patterns (isSuperAdmin, isAdmin, etc.)
     * and detects:
     * - Methods that check for a lower role but not a higher one (escalation risk)
     * - Inconsistent role check patterns within the same controller
     * - Direct role string comparisons instead of using role helper methods
     *
     * @return AuditFinding[]
     */
    public function checkRoleConsistency(): array
    {
        $findings = [];
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

            array_push($findings, ...$this->detectRoleEscalation($sourceCode, $className, $filePath));
            array_push($findings, ...$this->detectDirectRoleComparisons($sourceCode, $className, $filePath));
        }

        return $findings;
    }

    // ── Permission Matrix (Requirement 5.1) ──────────────────────

    /**
     * Produce route-to-role-permission mapping.
     *
     * Parses route files and extracts permission middleware to build a matrix of:
     * route URI => [method, middleware, module, action]
     *
     * @return array<int, array{method: string, uri: string, middleware: string[], module: string|null, action: string|null}>
     */
    public function generatePermissionMatrix(): array
    {
        $matrix = [];

        foreach ($this->routeFiles as $routeFile) {
            if (! file_exists($routeFile)) {
                continue;
            }

            $content = @file_get_contents($routeFile);
            if ($content === false) {
                continue;
            }

            $routes = $this->parseRoutesFromContent($content);

            foreach ($routes as $route) {
                $permModule = null;
                $permAction = null;

                // Use the last permission middleware (most specific / inline overrides group)
                foreach ($route['middleware'] as $mw) {
                    if (preg_match('/^permission:(\w+),(\w+)$/', $mw, $m)) {
                        $permModule = $m[1];
                        $permAction = $m[2];
                    }
                }

                $matrix[] = [
                    'method' => $route['method'],
                    'uri' => $route['uri'],
                    'middleware' => $route['middleware'],
                    'module' => $permModule,
                    'action' => $permAction,
                ];
            }
        }

        return $matrix;
    }

    // ── Healthcare RBAC Integration (Requirement 5.3) ────────────

    /**
     * Check healthcare RBACMiddleware integration with main permission system.
     *
     * Detects:
     * - Healthcare RBAC roles that overlap with main roles but use different naming
     * - Missing integration between healthcare RBAC and PermissionService
     * - Healthcare routes that use RBAC but not the main permission middleware
     *
     * @return AuditFinding[]
     */
    public function checkHealthcareRbacIntegration(): array
    {
        $findings = [];

        if (! file_exists($this->rbacMiddlewareFile)) {
            return $findings;
        }

        $rbacSource = @file_get_contents($this->rbacMiddlewareFile);
        if ($rbacSource === false) {
            return $findings;
        }

        // Extract roles from RBAC middleware
        $rbacRoles = $this->extractRbacRoles($rbacSource);

        // Check for role naming conflicts between healthcare RBAC and main system
        $mainRoles = array_values(self::ROLE_CHECK_METHODS);

        $overlappingRoles = [];
        foreach ($rbacRoles as $rbacRole) {
            // Healthcare 'admin' vs main 'admin', 'superadmin' vs 'super_admin'
            foreach ($mainRoles as $mainRole) {
                $normalizedRbac = str_replace('_', '', strtolower($rbacRole));
                $normalizedMain = str_replace('_', '', strtolower($mainRole));
                if ($normalizedRbac === $normalizedMain && $rbacRole !== $mainRole) {
                    $overlappingRoles[] = ['rbac' => $rbacRole, 'main' => $mainRole];
                }
            }
        }

        foreach ($overlappingRoles as $overlap) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: "Healthcare RBAC role '{$overlap['rbac']}' conflicts with main role '{$overlap['main']}'",
                description: "Healthcare RBACMiddleware defines role '{$overlap['rbac']}' which is similar to "
                    ."the main permission system role '{$overlap['main']}' but uses different naming. "
                    .'This inconsistency can cause confusion and permission bypass if a user has one role '
                    .'in the main system but a differently-named equivalent in healthcare.',
                file: $this->relativePath($this->rbacMiddlewareFile),
                line: null,
                recommendation: 'Align healthcare RBAC role names with the main permission system, '
                    ."or add explicit mapping between '{$overlap['rbac']}' and '{$overlap['main']}'.",
                metadata: [
                    'check' => 'healthcare_rbac_integration',
                    'rbac_role' => $overlap['rbac'],
                    'main_role' => $overlap['main'],
                ],
            );
        }

        // Check if RBAC middleware references PermissionService for integration
        $usesPermissionService = str_contains($rbacSource, 'PermissionService')
            || str_contains($rbacSource, 'PermissionMiddleware');

        if (! $usesPermissionService) {
            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: 'Healthcare RBACMiddleware not integrated with PermissionService',
                description: 'The healthcare RBACMiddleware manages its own role-permission mapping '
                    .'independently of the main PermissionService. This creates two separate permission '
                    .'systems that could diverge, making it harder to audit and manage permissions centrally.',
                file: $this->relativePath($this->rbacMiddlewareFile),
                line: null,
                recommendation: 'Consider integrating healthcare RBAC with PermissionService by adding '
                    .'healthcare modules to PermissionService::MODULES and using PermissionMiddleware '
                    .'for healthcare routes.',
                metadata: [
                    'check' => 'healthcare_rbac_integration',
                    'uses_permission_service' => false,
                ],
            );
        }

        return $findings;
    }

    // ── Private: Extract Modules from PermissionService ──────────

    /**
     * Extract the MODULES constant from PermissionService.php.
     *
     * @return array<string, string[]> Module name => array of actions
     */
    private function extractModulesFromPermissionService(): array
    {
        if (! file_exists($this->permissionServiceFile)) {
            return [];
        }

        $content = @file_get_contents($this->permissionServiceFile);
        if ($content === false) {
            return [];
        }

        $modules = [];

        // Match the MODULES constant array entries: 'module_name' => ['action1', 'action2']
        if (preg_match('/MODULES\s*=\s*\[(.*?)\];/s', $content, $outerMatch)) {
            $arrayContent = $outerMatch[1];

            // Match each module entry
            if (preg_match_all(
                '/[\'"](\w+)[\'"]\s*=>\s*\[([^\]]*)\]/',
                $arrayContent,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $match) {
                    $moduleName = $match[1];
                    $actionsStr = $match[2];

                    $actions = [];
                    if (preg_match_all('/[\'"](\w+)[\'"]/', $actionsStr, $actionMatches)) {
                        $actions = $actionMatches[1];
                    }

                    $modules[$moduleName] = $actions;
                }
            }
        }

        return $modules;
    }

    // ── Private: Extract Modules Referenced in Routes ────────────

    /**
     * Extract module names referenced in permission middleware across route files.
     *
     * Looks for patterns like: permission:module_name,action
     *
     * @return string[] Unique module names found in routes
     */
    private function extractModulesFromRoutes(): array
    {
        $modules = [];

        foreach ($this->routeFiles as $routeFile) {
            if (! file_exists($routeFile)) {
                continue;
            }

            $content = @file_get_contents($routeFile);
            if ($content === false) {
                continue;
            }

            // Match permission:module,action in middleware declarations
            if (preg_match_all('/permission:(\w+),\w+/', $content, $matches)) {
                foreach ($matches[1] as $module) {
                    $modules[] = $module;
                }
            }
        }

        return array_values(array_unique($modules));
    }

    // ── Private: Discover Existing Policies ──────────────────────

    /**
     * Discover policy class names from the Policies directory.
     *
     * @return string[] Policy class names (e.g., ['CompanyGroupPolicy', 'MedicalRecordPolicy'])
     */
    private function discoverExistingPolicies(): array
    {
        $policies = [];

        if (! is_dir($this->policyPath)) {
            return $policies;
        }

        $files = $this->discoverPhpFiles($this->policyPath);

        foreach ($files as $filePath) {
            $filename = basename($filePath, '.php');
            if (str_ends_with($filename, 'Policy')) {
                $policies[] = $filename;
            }
        }

        return $policies;
    }

    // ── Private: Role Escalation Detection ───────────────────────

    /**
     * Detect potential role escalation paths in a controller.
     *
     * Looks for methods that check for a lower-privilege role without also
     * checking for higher-privilege roles, which could indicate that higher
     * roles are accidentally excluded.
     *
     * @return AuditFinding[]
     */
    private function detectRoleEscalation(string $sourceCode, string $className, string $filePath): array
    {
        $findings = [];
        $lines = explode("\n", $sourceCode);
        $methods = $this->extractMethodBodies($lines);

        foreach ($methods as $methodName => $methodBody) {
            $rolesChecked = [];

            foreach (self::ROLE_CHECK_METHODS as $checkMethod => $role) {
                if (preg_match('/->('.preg_quote($checkMethod, '/').')\s*\(\s*\)/', $methodBody)) {
                    $rolesChecked[$role] = self::ROLE_HIERARCHY[$role] ?? 0;
                }
            }

            if (count($rolesChecked) < 2) {
                continue;
            }

            // Check for escalation: if a lower role is checked but a higher role is not
            $maxChecked = max($rolesChecked);
            $minChecked = min($rolesChecked);

            // Find roles between min and max that are NOT checked
            $missingRoles = [];
            foreach (self::ROLE_HIERARCHY as $role => $level) {
                if ($level > $minChecked && $level < $maxChecked && ! isset($rolesChecked[$role])) {
                    $missingRoles[] = $role;
                }
            }

            if (! empty($missingRoles)) {
                $shortClass = $this->shortClassName($className);
                $checkedRoleNames = implode(', ', array_keys($rolesChecked));
                $missingRoleNames = implode(', ', $missingRoles);

                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Medium,
                    title: "Potential role escalation gap in {$shortClass}::{$methodName}()",
                    description: "Method {$methodName}() checks roles [{$checkedRoleNames}] but skips "
                        ."intermediate roles [{$missingRoleNames}]. This could indicate an escalation gap "
                        .'where certain roles are accidentally excluded from access control.',
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Review the role checks in {$methodName}() and ensure all appropriate "
                        .'roles are included, or use PermissionService for centralized permission checks.',
                    metadata: [
                        'check' => 'role_consistency',
                        'method' => $methodName,
                        'checked_roles' => array_keys($rolesChecked),
                        'missing_roles' => $missingRoles,
                    ],
                );
            }
        }

        return $findings;
    }

    /**
     * Detect direct role string comparisons instead of using role helper methods.
     *
     * Patterns like: $user->role === 'admin' instead of $user->isAdmin()
     *
     * @return AuditFinding[]
     */
    private function detectDirectRoleComparisons(string $sourceCode, string $className, string $filePath): array
    {
        $findings = [];
        $lines = explode("\n", $sourceCode);

        // Pattern: ->role === 'role_name' or ->role == 'role_name' or ->role != 'role_name'
        $pattern = '/->role\s*[!=]==?\s*[\'"](\w+)[\'"]/';

        $reported = false;
        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match($pattern, $lines[$i], $match)) {
                if ($reported) {
                    continue;
                }

                $shortClass = $this->shortClassName($className);
                $methodName = $this->findEnclosingMethod($lines, $i);

                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Low,
                    title: "Direct role comparison in {$shortClass}".($methodName ? "::{$methodName}()" : ''),
                    description: "Controller uses direct role string comparison (->role === '{$match[1]}') "
                        .'instead of the type-safe role helper methods (e.g., ->isAdmin()). '
                        .'Direct comparisons are fragile and can lead to inconsistencies if role names change.',
                    file: $this->relativePath($filePath),
                    line: $i + 1,
                    recommendation: 'Use the User model role helper methods (isSuperAdmin(), isAdmin(), '
                        .'isManager(), etc.) or PermissionService for role checks.',
                    metadata: [
                        'check' => 'role_consistency',
                        'method' => $methodName,
                        'role_compared' => $match[1],
                    ],
                );

                $reported = true;
            }
        }

        return $findings;
    }

    // ── Private: Findings from Permission Matrix ─────────────────

    /**
     * Generate findings from the permission matrix analysis.
     *
     * Detects data-modifying routes (POST/PUT/PATCH/DELETE) that have no
     * permission module assigned.
     *
     * @param  array<int, array{method: string, uri: string, middleware: string[], module: string|null, action: string|null}>  $matrix
     * @return AuditFinding[]
     */
    private function findingsFromPermissionMatrix(array $matrix): array
    {
        $findings = [];
        $dataModifyingMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

        $exemptPatterns = [
            '/^login/',
            '/^logout/',
            '/^register/',
            '/^password/',
            '/^forgot-password/',
            '/^reset-password/',
            '/^webhooks?\//',
            '/^api\/webhooks?\//',
            '/^oauth/',
            '/^auth\//',
            '/^two-factor/',
            '/^2fa/',
            '/^sanctum\//',
        ];

        foreach ($matrix as $entry) {
            $method = strtoupper($entry['method']);

            if (! in_array($method, $dataModifyingMethods, true)) {
                continue;
            }

            // Skip exempt routes
            $exempt = false;
            foreach ($exemptPatterns as $pattern) {
                if (preg_match($pattern, $entry['uri'])) {
                    $exempt = true;
                    break;
                }
            }
            if ($exempt) {
                continue;
            }

            // Check if route has any permission-like middleware
            $hasPermission = false;
            foreach ($entry['middleware'] as $mw) {
                if (preg_match('/^(permission:|role:|can:)/', $mw)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (! $hasPermission) {
                // Check if it at least has auth
                $hasAuth = false;
                foreach ($entry['middleware'] as $mw) {
                    if (preg_match('/^auth(:|$)/', $mw) || $mw === 'verified') {
                        $hasAuth = true;
                        break;
                    }
                }

                // Only report if it doesn't even have auth — RouteAnalyzer handles the rest
                if (! $hasAuth) {
                    $findings[] = new AuditFinding(
                        category: $this->category(),
                        severity: Severity::Critical,
                        title: "Unprotected {$method} route in permission matrix: {$entry['uri']}",
                        description: "Route {$method} {$entry['uri']} has no authentication or permission "
                            .'middleware in the permission matrix. This data-modifying endpoint is publicly accessible.',
                        file: null,
                        line: null,
                        recommendation: "Add 'permission:<module>,<action>' middleware to this route.",
                        metadata: [
                            'check' => 'permission_matrix',
                            'method' => $method,
                            'uri' => $entry['uri'],
                            'middleware' => $entry['middleware'],
                        ],
                    );
                }
            }
        }

        return $findings;
    }

    // ── Private: Route Parsing ───────────────────────────────────

    /**
     * Parse route definitions from file content.
     *
     * @return array<int, array{method: string, uri: string, middleware: string[]}>
     */
    private function parseRoutesFromContent(string $content): array
    {
        $routes = [];
        $lines = explode("\n", $content);
        $middlewareStack = [];
        $braceDepth = 0;
        $groupDepths = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Track middleware group openings
            if (preg_match('/Route::(?:middleware|prefix)\s*\(/', $trimmed) && str_contains($trimmed, 'group')) {
                $groupMiddleware = $this->extractMiddlewareFromLine($trimmed);
                $middlewareStack[] = $groupMiddleware;
                $stripped = $this->stripStringsAndComments($trimmed);
                $braceDepth += substr_count($stripped, '{') - substr_count($stripped, '}');
                $groupDepths[] = $braceDepth;

                continue;
            }

            // Track brace depth
            $stripped = $this->stripStringsAndComments($trimmed);
            $braceDepth += substr_count($stripped, '{') - substr_count($stripped, '}');

            // Close groups
            while (! empty($groupDepths) && $braceDepth < end($groupDepths)) {
                array_pop($groupDepths);
                array_pop($middlewareStack);
            }

            // Match route definitions
            if (preg_match(
                '/Route::(get|post|put|patch|delete|any)\s*\(\s*[\'"]([^\'"]+)[\'"]/',
                $trimmed,
                $methodMatch
            )) {
                $httpMethod = strtoupper($methodMatch[1]);
                $uri = ltrim($methodMatch[2], '/');

                $inlineMiddleware = $this->extractMiddlewareFromLine($trimmed);

                $allMiddleware = [];
                foreach ($middlewareStack as $groupMw) {
                    array_push($allMiddleware, ...$groupMw);
                }
                array_push($allMiddleware, ...$inlineMiddleware);
                $allMiddleware = array_values(array_unique($allMiddleware));

                $routes[] = [
                    'method' => $httpMethod,
                    'uri' => $uri,
                    'middleware' => $allMiddleware,
                ];
            }
        }

        return $routes;
    }

    /**
     * Extract middleware from a route definition or group line.
     *
     * @return string[]
     */
    private function extractMiddlewareFromLine(string $line): array
    {
        $middleware = [];

        if (preg_match_all('/->middleware\s*\(\s*([^)]+)\s*\)/', $line, $matches)) {
            foreach ($matches[1] as $mwArg) {
                array_push($middleware, ...$this->parseMiddlewareArgument($mwArg));
            }
        }

        if (preg_match('/Route::middleware\s*\(\s*([^)]+)\s*\)/', $line, $match)) {
            array_push($middleware, ...$this->parseMiddlewareArgument($match[1]));
        }

        return array_values(array_unique($middleware));
    }

    /**
     * Parse a middleware argument string into individual middleware names.
     *
     * @return string[]
     */
    private function parseMiddlewareArgument(string $arg): array
    {
        $middleware = [];
        $arg = trim($arg, '[] ');

        if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $arg, $matches)) {
            foreach ($matches[1] as $mw) {
                $middleware[] = trim($mw);
            }
        }

        return $middleware;
    }

    // ── Private: RBAC Role Extraction ────────────────────────────

    /**
     * Extract role names from the RBACMiddleware source.
     *
     * @return string[]
     */
    private function extractRbacRoles(string $source): array
    {
        $roles = [];

        // Match array keys in $rolePermissions: 'role_name' => [...]
        if (preg_match_all('/[\'"](\w+)[\'"]\s*=>\s*\[/', $source, $matches)) {
            $roles = $matches[1];
        }

        return array_values(array_unique($roles));
    }

    // ── Private: Method Body Extraction ──────────────────────────

    /**
     * Extract method names and their bodies from source lines.
     *
     * @param  string[]  $lines
     * @return array<string, string> Method name => method body text
     */
    private function extractMethodBodies(array $lines): array
    {
        $methods = [];
        $currentMethod = null;
        $braceDepth = 0;
        $methodStart = false;
        $bodyLines = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            if (preg_match('/^\s*(?:public|protected|private)\s+(?:static\s+)?function\s+(\w+)\s*\(/', $line, $match)) {
                if ($currentMethod !== null && ! empty($bodyLines)) {
                    $methods[$currentMethod] = implode("\n", $bodyLines);
                }

                $currentMethod = $match[1];
                $bodyLines = [$line];
                $braceDepth = 0;
                $methodStart = true;
            }

            if ($currentMethod !== null) {
                if (! $methodStart) {
                    $bodyLines[] = $line;
                }
                $methodStart = false;

                $stripped = $this->stripStringsAndComments($line);
                $braceDepth += substr_count($stripped, '{') - substr_count($stripped, '}');

                if ($braceDepth <= 0 && count($bodyLines) > 1) {
                    $methods[$currentMethod] = implode("\n", $bodyLines);
                    $currentMethod = null;
                    $bodyLines = [];
                }
            }
        }

        if ($currentMethod !== null && ! empty($bodyLines)) {
            $methods[$currentMethod] = implode("\n", $bodyLines);
        }

        return $methods;
    }

    // ── Private: General Helpers ─────────────────────────────────

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
