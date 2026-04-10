<?php

/**
 * Route Audit Script
 * Extract all routes and compare with controller methods
 * Usage: php scripts/audit-routes.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "========================================\n";
echo "ROUTE AUDIT REPORT\n";
echo "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

// Collect all routes
$routes = Route::getRoutes();

$routeList = [];
$missingMethods = [];

foreach ($routes as $route) {
    $action = $route->getAction();
    $uri = $route->uri();
    $methods = $route->methods();

    // Skip if not a controller action
    if (!isset($action['controller']) || !is_string($action['controller'])) {
        continue;
    }

    // Parse controller@method
    if (strpos($action['controller'], '@') !== false) {
        [$controller, $method] = explode('@', $action['controller']);
    } else {
        // Laravel 11+ uses array syntax [Controller::class, 'method']
        continue;
    }

    // Skip closures
    if (strpos($controller, 'Closure') !== false) {
        continue;
    }

    $routeList[] = [
        'methods' => implode(', ', $methods),
        'uri' => $uri,
        'controller' => $controller,
        'method' => $method,
        'name' => $action['as'] ?? 'N/A',
    ];

    // Check if method exists
    if (class_exists($controller)) {
        if (!method_exists($controller, $method)) {
            $missingMethods[] = [
                'controller' => $controller,
                'method' => $method,
                'uri' => $uri,
                'route_name' => $action['as'] ?? 'N/A',
            ];
        }
    } else {
        echo "⚠️  Controller not found: $controller\n";
    }
}

// Display summary
echo "📊 ROUTE SUMMARY\n";
echo "----------------------------------------\n";
echo "Total Routes: " . count($routeList) . "\n";
echo "Missing Methods: " . count($missingMethods) . "\n\n";

// Display missing methods
if (count($missingMethods) > 0) {
    echo "❌ MISSING CONTROLLER METHODS\n";
    echo "========================================\n\n";

    // Group by controller
    $grouped = [];
    foreach ($missingMethods as $missing) {
        $controller = $missing['controller'];
        if (!isset($grouped[$controller])) {
            $grouped[$controller] = [];
        }
        $grouped[$controller][] = $missing;
    }

    foreach ($grouped as $controller => $methods) {
        echo "📁 Controller: " . class_basename($controller) . "\n";
        echo "   Full: $controller\n";
        echo "   Missing Methods:\n";
        foreach ($methods as $method) {
            echo "   - {$method['method']}() [Route: {$method['uri']}, Name: {$method['route_name']}]\n";
        }
        echo "\n";
    }

    // Save to file
    $outputFile = __DIR__ . '/missing-methods-report.json';
    file_put_contents(
        $outputFile,
        json_encode([
            'generated_at' => now()->toDateTimeString(),
            'total_routes' => count($routeList),
            'total_missing' => count($missingMethods),
            'missing_methods' => $grouped,
        ], JSON_PRETTY_PRINT)
    );

    echo "\n✅ Full report saved to: $outputFile\n";
} else {
    echo "✅ All controller methods exist!\n";
}

echo "\n========================================\n";
echo "AUDIT COMPLETE\n";
echo "========================================\n";
