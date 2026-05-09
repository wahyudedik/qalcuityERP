<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use Illuminate\Routing\Route;

/**
 * Analyzes route definitions for security and integrity issues:
 * - Maps every route to its middleware stack (permission matrix)
 * - Detects data-modifying routes without permission middleware
 * - Detects routes pointing to missing controller methods (orphaned routes)
 *
 * Uses a dual approach:
 * - Primary: Route::getRoutes() when the Laravel app is booted
 * - Fallback: Static regex parsing of route files for unit testing
 *
 * Route file paths are injectable for testability.
 *
 * Validates: Requirements 5.1, 5.2, 5.7
 */
class RouteAnalyzer implements AnalyzerInterface
{
    /**
     * Paths to route files to parse (fallback mode).
     *
     * @var string[]
     */
    private array $routeFiles;

    /**
     * Base path for resolving controller file paths.
     */
    private string $basePath;

    /**
     * Data-modifying HTTP methods that require permission middleware.
     */
    private const DATA_MODIFYING_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Permission middleware patterns that satisfy the protection requirement.
     */
    private const PERMISSION_MIDDLEWARE_PATTERNS = [
        '/^permission:/',
        '/^role:/',
        '/^can:/',
    ];

    /**
     * Auth middleware patterns (weaker protection, but still counts).
     */
    private const AUTH_MIDDLEWARE_PATTERNS = [
        '/^auth$/',
        '/^auth:/',
        '/^auth\.basic/',
    ];

    /**
     * Route URI patterns that are exempt from permission checks.
     * These are login/logout/register, password reset, OAuth, webhooks, etc.
     */
    private const EXEMPT_URI_PATTERNS = [
        '/^login/',
        '/^logout/',
        '/^register/',
        '/^password/',
        '/^forgot-password/',
        '/^reset-password/',
        '/^email\/verify/',
        '/^oauth/',
        '/^auth\//',
        '/^webhooks?\//',
        '/^api\/webhooks?\//',
        '/^api-tokens?\//',
        '/^two-factor/',
        '/^2fa/',
        '/^sanctum\//',
        '/^_ignition\//',
        '/^_debugbar\//',
    ];

    /**
     * @param  string[]|null  $routeFiles  Paths to route files (for static parsing fallback)
     * @param  string|null  $basePath  Project root path
     */
    public function __construct(?array $routeFiles = null, ?string $basePath = null)
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

        $this->routeFiles = $routeFiles ?? [
            $this->basePath.'/routes/web.php',
            $this->basePath.'/routes/api.php',
        ];
    }

    /**
     * Run the full route analysis.
     *
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        $unprotected = $this->findUnprotectedRoutes();
        array_push($findings, ...$unprotected);

        $orphaned = $this->findOrphanedRoutes();
        array_push($findings, ...$orphaned);

        return $findings;
    }

    /**
     * Map every route to its middleware stack.
     *
     * Returns an array of route entries:
     * [
     *   [
     *     'method'     => 'POST',
     *     'uri'        => 'products',
     *     'action'     => 'App\Http\Controllers\ProductController@store',
     *     'middleware'  => ['auth', 'permission:inventory,create'],
     *     'name'       => 'products.store',
     *   ],
     *   ...
     * ]
     *
     * @return array<int, array{method: string, uri: string, action: string, middleware: string[], name: string|null}>
     */
    public function getRoutePermissionMatrix(): array
    {
        // Try Laravel's Route facade first (works when app is booted)
        $routes = $this->getRoutesFromFacade();
        if ($routes !== null) {
            return $routes;
        }

        // Fallback: parse route files statically
        return $this->parseRouteFilesStatically();
    }

    /**
     * Find data-modifying routes (POST/PUT/PATCH/DELETE) that lack permission middleware.
     *
     * @return AuditFinding[]
     */
    public function findUnprotectedRoutes(): array
    {
        $findings = [];
        $matrix = $this->getRoutePermissionMatrix();

        foreach ($matrix as $route) {
            $method = strtoupper($route['method']);

            // Only check data-modifying methods
            if (! in_array($method, self::DATA_MODIFYING_METHODS, true)) {
                continue;
            }

            $uri = $route['uri'];

            // Skip exempt routes (login, webhooks, etc.)
            if ($this->isExemptRoute($uri)) {
                continue;
            }

            $middleware = $route['middleware'];

            // Check if route has permission/role/can middleware
            if ($this->hasPermissionMiddleware($middleware)) {
                continue;
            }

            // Check if route has at least auth middleware
            $hasAuth = $this->hasAuthMiddleware($middleware);

            $severity = $hasAuth ? Severity::Medium : Severity::Critical;
            $description = $hasAuth
                ? "Route {$method} {$uri} has auth middleware but lacks specific permission/role middleware. "
                .'Any authenticated user can access this data-modifying endpoint.'
                : "Route {$method} {$uri} has NO authentication or permission middleware. "
                .'This data-modifying endpoint is publicly accessible.';

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: $severity,
                title: "Unprotected {$method} route: {$uri}",
                description: $description,
                file: null,
                line: null,
                recommendation: "Add permission middleware (e.g., 'permission:{module},{action}') to this route.",
                metadata: [
                    'method' => $method,
                    'uri' => $uri,
                    'action' => $route['action'],
                    'middleware' => $middleware,
                    'name' => $route['name'] ?? null,
                    'has_auth' => $hasAuth,
                ],
            );
        }

        return $findings;
    }

    /**
     * Find routes that point to controller methods that don't exist.
     *
     * @return AuditFinding[]
     */
    public function findOrphanedRoutes(): array
    {
        $findings = [];
        $matrix = $this->getRoutePermissionMatrix();

        foreach ($matrix as $route) {
            $action = $route['action'];

            // Skip closure routes (no controller reference)
            if ($action === 'Closure' || ! str_contains($action, '@')) {
                continue;
            }

            [$controllerClass, $methodName] = explode('@', $action, 2);

            // Resolve controller file path
            $filePath = $this->resolveControllerFilePath($controllerClass);
            if ($filePath === null) {
                continue;
            }

            if (! file_exists($filePath)) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Missing controller: {$controllerClass}",
                    description: "Route '{$route['method']} {$route['uri']}' references controller "
                        ."'{$controllerClass}' but the controller file does not exist at '{$this->relativePath($filePath)}'.",
                    file: null,
                    line: null,
                    recommendation: 'Create the controller class or update the route to point to an existing controller.',
                    metadata: [
                        'method' => $route['method'],
                        'uri' => $route['uri'],
                        'controller' => $controllerClass,
                        'expected_file' => $this->relativePath($filePath),
                    ],
                );

                continue;
            }

            // Check if the method exists in the controller file
            if (! $this->controllerHasMethod($filePath, $methodName)) {
                $shortClass = $this->shortClassName($controllerClass);
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::High,
                    title: "Missing method: {$shortClass}@{$methodName}",
                    description: "Route '{$route['method']} {$route['uri']}' references method "
                        ."'{$methodName}()' in '{$controllerClass}', but the method does not exist.",
                    file: $this->relativePath($filePath),
                    line: null,
                    recommendation: "Add the '{$methodName}()' method to {$shortClass} or update the route.",
                    metadata: [
                        'method' => $route['method'],
                        'uri' => $route['uri'],
                        'controller' => $controllerClass,
                        'missing_method' => $methodName,
                    ],
                );
            }
        }

        return $findings;
    }

    /**
     * Get the analyzer category name.
     */
    public function category(): string
    {
        return 'route';
    }

    // ── Route Collection Methods ─────────────────────────────────

    /**
     * Try to get routes from Laravel's Route facade.
     * Returns null if the app is not booted.
     *
     * @return array<int, array{method: string, uri: string, action: string, middleware: string[], name: string|null}>|null
     */
    private function getRoutesFromFacade(): ?array
    {
        try {
            if (! class_exists(\Illuminate\Support\Facades\Route::class)) {
                return null;
            }

            $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();
            if ($routeCollection === null) {
                return null;
            }

            $routeList = $routeCollection->getRoutes();
            if (empty($routeList)) {
                return null;
            }

            $routes = [];
            foreach ($routeCollection as $route) {
                $methods = $route->methods();
                $middleware = $this->resolveRouteMiddleware($route);

                foreach ($methods as $method) {
                    if ($method === 'HEAD') {
                        continue;
                    }

                    $action = $route->getActionName();
                    if ($action === 'Closure') {
                        $action = 'Closure';
                    }

                    $routes[] = [
                        'method' => $method,
                        'uri' => $route->uri(),
                        'action' => $action,
                        'middleware' => $middleware,
                        'name' => $route->getName(),
                    ];
                }
            }

            return $routes;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve all middleware for a route (including group middleware).
     *
     * @param  Route  $route
     * @return string[]
     */
    private function resolveRouteMiddleware($route): array
    {
        try {
            $middleware = $route->gatherMiddleware();

            return array_values(array_unique(
                array_map(fn ($m) => is_string($m) ? $m : get_class($m), $middleware)
            ));
        } catch (\Throwable) {
            return [];
        }
    }

    // ── Static Route File Parsing ────────────────────────────────

    /**
     * Parse route files statically using regex.
     * This is the fallback when the Laravel app is not booted.
     *
     * @return array<int, array{method: string, uri: string, action: string, middleware: string[], name: string|null}>
     */
    private function parseRouteFilesStatically(): array
    {
        $routes = [];

        foreach ($this->routeFiles as $routeFile) {
            if (! file_exists($routeFile)) {
                continue;
            }

            $content = @file_get_contents($routeFile);
            if ($content === false) {
                continue;
            }

            $parsed = $this->parseRouteFileContent($content);
            array_push($routes, ...$parsed);
        }

        return $routes;
    }

    /**
     * Parse route definitions from file content using regex.
     *
     * Handles patterns like:
     *   Route::post('/path', [Controller::class, 'method'])->middleware('permission:mod,act');
     *   Route::post('/path', [Controller::class, 'method'])->name('name')->middleware(['auth', 'permission:mod,act']);
     *   Route::middleware(['auth'])->group(function () { ... });
     *
     * @return array<int, array{method: string, uri: string, action: string, middleware: string[], name: string|null}>
     */
    private function parseRouteFileContent(string $content): array
    {
        $routes = [];

        // First, resolve use statements to map short class names to FQCNs
        $useMap = $this->parseUseStatements($content);

        // Parse middleware groups to track inherited middleware
        $lines = explode("\n", $content);
        $middlewareStack = []; // Stack of middleware arrays for nested groups
        $braceDepth = 0;
        $groupDepths = []; // Track at which brace depth each group was opened

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);

            // Track middleware group openings
            if (preg_match('/Route::(?:middleware|prefix)\s*\(/', $trimmed) && str_contains($trimmed, 'group')) {
                $groupMiddleware = $this->extractMiddlewareFromLine($trimmed);
                $middlewareStack[] = $groupMiddleware;
                // Count opening braces on this line to track group depth
                $openBraces = substr_count($trimmed, '{');
                $closeBraces = substr_count($trimmed, '}');
                $braceDepth += $openBraces - $closeBraces;
                $groupDepths[] = $braceDepth;

                continue;
            }

            // Track brace depth for group nesting
            $stripped = $this->stripStringsAndComments($trimmed);
            $openBraces = substr_count($stripped, '{');
            $closeBraces = substr_count($stripped, '}');
            $braceDepth += $openBraces - $closeBraces;

            // Close groups when their brace depth is exited
            while (! empty($groupDepths) && $braceDepth < end($groupDepths)) {
                array_pop($groupDepths);
                array_pop($middlewareStack);
            }

            // Match route definitions: Route::get/post/put/patch/delete/any
            if (preg_match(
                '/Route::(get|post|put|patch|delete|any)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/',
                $trimmed,
                $methodMatch
            )) {
                $httpMethod = strtoupper($methodMatch[1]);
                $uri = ltrim($methodMatch[2], '/');

                // Extract controller@method action
                $action = $this->extractActionFromLine($trimmed, $useMap);

                // Extract inline middleware
                $inlineMiddleware = $this->extractMiddlewareFromLine($trimmed);

                // Combine group middleware with inline middleware
                $allMiddleware = [];
                foreach ($middlewareStack as $groupMw) {
                    array_push($allMiddleware, ...$groupMw);
                }
                array_push($allMiddleware, ...$inlineMiddleware);
                $allMiddleware = array_values(array_unique($allMiddleware));

                // Extract route name
                $name = $this->extractRouteNameFromLine($trimmed);

                $routes[] = [
                    'method' => $httpMethod,
                    'uri' => $uri,
                    'action' => $action,
                    'middleware' => $allMiddleware,
                    'name' => $name,
                ];
            }
        }

        return $routes;
    }

    /**
     * Parse use statements from file content to build a class alias map.
     *
     * @return array<string, string> Map of short name => FQCN
     */
    private function parseUseStatements(string $content): array
    {
        $map = [];

        if (preg_match_all('/^use\s+([^;]+)\s*;/m', $content, $matches)) {
            foreach ($matches[1] as $useStatement) {
                $useStatement = trim($useStatement);

                // Handle aliased imports: use Foo\Bar as Baz;
                if (preg_match('/(.+)\s+as\s+(\w+)$/', $useStatement, $aliasMatch)) {
                    $map[$aliasMatch[2]] = $aliasMatch[1];
                } else {
                    // Use the last segment as the short name
                    $parts = explode('\\', $useStatement);
                    $shortName = end($parts);
                    $map[$shortName] = $useStatement;
                }
            }
        }

        return $map;
    }

    /**
     * Extract the controller@method action from a route definition line.
     *
     * Handles:
     *   [Controller::class, 'method']
     *   'Controller@method'
     *
     * @param  array<string, string>  $useMap
     */
    private function extractActionFromLine(string $line, array $useMap): string
    {
        // Pattern: [Controller::class, 'method']
        if (preg_match('/\[\s*([\\\\A-Za-z0-9_]+)::class\s*,\s*[\'"](\w+)[\'"]\s*\]/', $line, $match)) {
            $className = $match[1];
            $method = $match[2];

            // Resolve short class name via use map
            if (! str_contains($className, '\\') && isset($useMap[$className])) {
                $className = $useMap[$className];
            }

            return "{$className}@{$method}";
        }

        // Pattern: 'Controller@method'
        if (preg_match('/[\'"]([\\\\A-Za-z0-9_]+)@(\w+)[\'"]/', $line, $match)) {
            $className = $match[1];
            $method = $match[2];

            if (! str_contains($className, '\\') && isset($useMap[$className])) {
                $className = $useMap[$className];
            }

            return "{$className}@{$method}";
        }

        // Closure or other
        return 'Closure';
    }

    /**
     * Extract middleware from a route definition or group line.
     *
     * Handles:
     *   ->middleware('auth')
     *   ->middleware(['auth', 'permission:mod,act'])
     *   Route::middleware(['auth'])->group(...)
     *
     * @return string[]
     */
    private function extractMiddlewareFromLine(string $line): array
    {
        $middleware = [];

        // Match all middleware() calls on the line
        if (preg_match_all('/->middleware\s*\(\s*([^)]+)\s*\)/', $line, $matches)) {
            foreach ($matches[1] as $mwArg) {
                $extracted = $this->parseMiddlewareArgument($mwArg);
                array_push($middleware, ...$extracted);
            }
        }

        // Also match Route::middleware(...) at the start
        if (preg_match('/Route::middleware\s*\(\s*([^)]+)\s*\)/', $line, $match)) {
            $extracted = $this->parseMiddlewareArgument($match[1]);
            array_push($middleware, ...$extracted);
        }

        return array_values(array_unique($middleware));
    }

    /**
     * Parse a middleware argument string into individual middleware names.
     *
     * Handles:
     *   'auth'
     *   ['auth', 'permission:mod,act']
     *   'role:admin,manager'
     *
     * @return string[]
     */
    private function parseMiddlewareArgument(string $arg): array
    {
        $middleware = [];
        $arg = trim($arg);

        // Remove array brackets
        $arg = trim($arg, '[]');

        // Split by comma, but respect middleware parameters (e.g., 'permission:mod,act')
        // We split by commas that are followed by a quote
        if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $arg, $matches)) {
            foreach ($matches[1] as $mw) {
                $middleware[] = trim($mw);
            }
        }

        return $middleware;
    }

    /**
     * Extract route name from a line.
     */
    private function extractRouteNameFromLine(string $line): ?string
    {
        if (preg_match('/->name\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $match)) {
            return $match[1];
        }

        return null;
    }

    // ── Middleware Check Helpers ──────────────────────────────────

    /**
     * Check if middleware array contains permission/role/can middleware.
     *
     * @param  string[]  $middleware
     */
    private function hasPermissionMiddleware(array $middleware): bool
    {
        foreach ($middleware as $mw) {
            foreach (self::PERMISSION_MIDDLEWARE_PATTERNS as $pattern) {
                if (preg_match($pattern, $mw)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if middleware array contains auth middleware.
     *
     * @param  string[]  $middleware
     */
    private function hasAuthMiddleware(array $middleware): bool
    {
        foreach ($middleware as $mw) {
            foreach (self::AUTH_MIDDLEWARE_PATTERNS as $pattern) {
                if (preg_match($pattern, $mw)) {
                    return true;
                }
            }
            // Also check for 'verified' which implies auth
            if ($mw === 'verified') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a route URI matches an exempt pattern.
     */
    private function isExemptRoute(string $uri): bool
    {
        foreach (self::EXEMPT_URI_PATTERNS as $pattern) {
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }

        return false;
    }

    // ── Controller Resolution Helpers ────────────────────────────

    /**
     * Resolve a controller class name to a file path.
     */
    private function resolveControllerFilePath(string $className): ?string
    {
        // Handle App\ namespace -> app/ directory
        if (str_starts_with($className, 'App\\')) {
            $relativePath = str_replace('\\', '/', substr($className, 4));

            return $this->basePath."/app/{$relativePath}.php";
        }

        // Handle fully-qualified inline class references
        if (str_starts_with($className, '\\App\\')) {
            $relativePath = str_replace('\\', '/', substr($className, 5));

            return $this->basePath."/app/{$relativePath}.php";
        }

        return null;
    }

    /**
     * Check if a controller file contains a specific method.
     */
    private function controllerHasMethod(string $filePath, string $methodName): bool
    {
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        // Look for public/protected/private function declaration
        $pattern = '/\b(?:public|protected|private)\s+(?:static\s+)?function\s+'.preg_quote($methodName, '/').'\s*\(/';

        return (bool) preg_match($pattern, $content);
    }

    // ── General Helpers ──────────────────────────────────────────

    /**
     * Strip string literals and comments from a line for brace counting.
     */
    private function stripStringsAndComments(string $line): string
    {
        // Remove single-line comments
        $line = preg_replace('/\/\/.*$/', '', $line);
        $line = preg_replace('/#.*$/', '', $line);

        // Remove string literals
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
