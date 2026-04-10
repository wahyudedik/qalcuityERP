<?php

/**
 * Generate Missing Controller Methods
 * This script adds stub methods for all missing controller methods
 * Usage: php scripts/generate-missing-methods.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Load missing methods report
$reportFile = __DIR__ . '/missing-methods-report.json';
if (!file_exists($reportFile)) {
    echo "❌ Report file not found. Please run audit-routes.php first.\n";
    exit(1);
}

$report = json_decode(file_get_contents($reportFile), true);
$missingMethods = $report['missing_methods'];

echo "========================================\n";
echo "GENERATING MISSING METHODS\n";
echo "Total Controllers: " . count($missingMethods) . "\n";
echo "========================================\n\n";

$generated = 0;
$errors = 0;

foreach ($missingMethods as $controllerClass => $methods) {
    echo "📁 Processing: " . class_basename($controllerClass) . "\n";

    // Check if controller file exists
    $relativePath = str_replace('App\\', '', $controllerClass);
    $filePath = base_path('app/' . str_replace('\\', '/', $relativePath) . '.php');

    if (!file_exists($filePath)) {
        echo "   ⚠️  File not found: $filePath\n";
        $errors++;
        continue;
    }

    $fileContent = file_get_contents($filePath);
    $newMethods = [];

    foreach ($methods as $methodInfo) {
        $methodName = $methodInfo['method'];
        $routeName = $methodInfo['route_name'];
        $uri = $methodInfo['uri'];

        // Check if method already exists
        if (preg_match("/public\s+function\s+{$methodName}\s*\(/", $fileContent)) {
            echo "   ✓ Method {$methodName}() already exists\n";
            continue;
        }

        // Generate method stub
        $stub = generateMethodStub($methodName, $routeName, $uri);
        $newMethods[] = $stub;
        $generated++;
    }

    // Insert methods before the last closing brace
    if (!empty($newMethods)) {
        $methodsCode = "\n" . implode("\n", $newMethods) . "\n";
        $fileContent = preg_replace(
            '/\n\}\s*$/',
            $methodsCode . "}\n",
            $fileContent
        );

        // Write back to file
        file_put_contents($filePath, $fileContent);
        echo "   ✅ Added " . count($newMethods) . " method(s)\n";
    }

    echo "\n";
}

echo "========================================\n";
echo "GENERATION COMPLETE\n";
echo "Methods Generated: $generated\n";
echo "Errors: $errors\n";
echo "========================================\n";

/**
 * Generate a method stub based on method name and type
 */
function generateMethodStub(string $methodName, string $routeName, string $uri): string
{
    $commonCRUD = [
        'edit' => [
            'template' => 'edit',
            'validation' => null,
        ],
        'update' => [
            'template' => 'update',
            'validation' => true,
        ],
        'destroy' => [
            'template' => 'destroy',
            'validation' => null,
        ],
        'create' => [
            'template' => 'create',
            'validation' => null,
        ],
        'show' => [
            'template' => 'show',
            'validation' => null,
        ],
    ];

    if (isset($commonCRUD[$methodName])) {
        return generateCRUDMethodStub($methodName, $routeName, $uri, $commonCRUD[$methodName]);
    }

    // Custom method
    return generateCustomMethodStub($methodName, $routeName, $uri);
}

function generateCRUDMethodStub(string $methodName, string $routeName, string $uri, array $config): string
{
    $routeNameDotted = str_replace('/', '.', $routeName);

    switch ($methodName) {
        case 'edit':
            return <<<PHP
    /**
     * Show the form for editing the specified resource.
     *
     * Route: $uri
     * Route Name: $routeNameDotted
     */
    public function edit(\$model)
    {
        \$this->authorize('update', \$model);
        
        return view('LOWERCONTROLLER.edit', compact('model'));
    }

PHP;

        case 'update':
            return <<<PHP
    /**
     * Update the specified resource in storage.
     *
     * Route: $uri
     * Route Name: $routeNameDotted
     */
    public function update(Request \$request, \$model)
    {
        \$this->authorize('update', \$model);
        
        // TODO: Add validation rules
        \$validated = \$request->validate([
            // Add your validation rules here
        ]);
        
        \$model->update(\$validated);
        
        return redirect()->route('INDEX_ROUTE')
            ->with('success', 'Resource updated successfully.');
    }

PHP;

        case 'destroy':
            return <<<PHP
    /**
     * Remove the specified resource from storage.
     *
     * Route: $uri
     * Route Name: $routeNameDotted
     */
    public function destroy(\$model)
    {
        \$this->authorize('delete', \$model);
        
        \$model->delete();
        
        return redirect()->route('INDEX_ROUTE')
            ->with('success', 'Resource deleted successfully.');
    }

PHP;

        case 'create':
            return <<<PHP
    /**
     * Show the form for creating a new resource.
     *
     * Route: $uri
     * Route Name: $routeNameDotted
     */
    public function create()
    {
        \$this->authorize('create', self::class);
        
        return view('LOWERCONTROLLER.create');
    }

PHP;

        case 'show':
            return <<<PHP
    /**
     * Display the specified resource.
     *
     * Route: $uri
     * Route Name: $routeNameDotted
     */
    public function show(\$model)
    {
        \$this->authorize('view', \$model);
        
        return view('LOWERCONTROLLER.show', compact('model'));
    }

PHP;
    }

    return '';
}

function generateCustomMethodStub(string $methodName, string $routeName, string $uri): string
{
    $routeNameDotted = str_replace('/', '.', $routeName);

    return <<<PHP
    /**
     * {$methodName} action.
     *
     * Route: $uri
     * Route Name: $routeNameDotted
     */
    public function {$methodName}(Request \$request)
    {
        // TODO: Add authorization
        // \$this->authorize('ACTION', MODEL::class);
        
        // TODO: Add validation
        // \$validated = \$request->validate([
        //     // Add validation rules
        // ]);
        
        // TODO: Implement business logic
        
        return back()->with('success', 'Action completed successfully.');
    }

PHP;
}
