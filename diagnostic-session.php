#!/usr/bin/env php
<?php

/**
 * Laravel Session & Redirect Loop Diagnostic Tool
 * 
 * Usage: php diagnostic-session.php
 * 
 * This script checks:
 * - Session configuration
 * - Session table health
 * - Cookie size issues
 * - Middleware redirect loops
 * - Public route accessibility
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   LARAVEL SESSION & REDIRECT LOOP DIAGNOSTIC TOOL       ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. Check .env configuration
echo "📋 1. ENVIRONMENT CONFIGURATION\n";
echo str_repeat('─', 60) . "\n";

$checks = [
    'APP_URL' => config('app.url'),
    'SESSION_DRIVER' => config('session.driver'),
    'SESSION_LIFETIME' => config('session.lifetime'),
    'SESSION_DOMAIN' => config('session.domain') ?? 'null',
    'SESSION_SECURE_COOKIE' => config('session.secure') ? 'true' : 'false',
    'SESSION_SAME_SITE' => config('session.same_site'),
    'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
];

foreach ($checks as $key => $value) {
    $status = '✅';

    // Check for common issues
    if ($key === 'APP_URL' && str_contains($value, 'https://') && app()->environment('local')) {
        $status = '⚠️';
        echo "{$status} {$key}: {$value} (WARNING: HTTPS in local environment)\n";
    } elseif ($key === 'SESSION_DRIVER' && $value === 'cookie') {
        $status = '❌';
        echo "{$status} {$key}: {$value} (ERROR: Cookie driver can exceed 4KB limit!)\n";
    } elseif ($key === 'SESSION_DOMAIN' && $value !== 'null' && str_starts_with($value, '.')) {
        $status = '⚠️';
        echo "{$status} {$key}: {$value} (WARNING: Should not start with dot for local dev)\n";
    } else {
        echo "{$status} {$key}: {$value}\n";
    }
}

echo "\n";

// 2. Session Table Health
echo "🗄️  2. SESSION TABLE HEALTH\n";
echo str_repeat('─', 60) . "\n";

if (Schema::hasTable('sessions')) {
    $totalSessions = DB::table('sessions')->count();
    $totalSize = DB::table('sessions')->sum(DB::raw('LENGTH(payload)'));
    $avgSize = $totalSessions > 0 ? $totalSize / $totalSessions : 0;
    $oldSessions = DB::table('sessions')
        ->where('last_activity', '<', now()->subHours(24)->timestamp)
        ->count();

    echo "✅ Sessions table exists\n";
    echo "   📊 Total sessions: {$totalSessions}\n";
    echo "   📦 Total size: " . number_format($totalSize / 1024, 2) . " KB\n";
    echo "   📏 Average session size: " . number_format($avgSize / 1024, 2) . " KB\n";
    echo "   🕐 Expired sessions (>24h): {$oldSessions}\n";

    if ($avgSize > 4096) {
        echo "   ⚠️  WARNING: Average session size exceeds 4KB!\n";
    }

    // Check for indexes
    $indexes = DB::select("SHOW INDEX FROM sessions");
    $hasLastActivityIndex = collect($indexes)->contains(function ($index) {
        return $index->Column_name === 'last_activity';
    });

    if ($hasLastActivityIndex) {
        echo "   ✅ Index on last_activity column exists\n";
    } else {
        echo "   ⚠️  Missing index on last_activity (performance issue)\n";
    }
} else {
    echo "❌ Sessions table DOES NOT EXIST!\n";
    echo "   Run: php artisan session:table\n";
    echo "   Then: php artisan migrate\n";
}

echo "\n";

// 3. Middleware Configuration
echo "🔒 3. MIDDLEWARE CONFIGURATION\n";
echo str_repeat('─', 60) . "\n";

// Check if CheckTenantActive middleware exists
if (class_exists(\App\Http\Middleware\CheckTenantActive::class)) {
    echo "✅ CheckTenantActive middleware exists\n";

    // Check if it has public route skip logic
    $reflection = new ReflectionClass(\App\Http\Middleware\CheckTenantActive::class);
    $method = $reflection->getMethod('handle');
    $filename = $method->getFileName();
    $startLine = $method->getStartLine();
    $endLine = $method->getEndLine();
    $length = $endLine - $startLine;

    $source = file($filename);
    $methodContent = implode("", array_slice($source, $startLine - 1, $length));

    if (str_contains($methodContent, 'resources.*') || str_contains($methodContent, 'legal.*')) {
        echo "   ✅ Public route skip logic present\n";
    } else {
        echo "   ⚠️  Missing public route skip logic (may cause redirect loop)\n";
    }
} else {
    echo "ℹ️  CheckTenantActive middleware not found\n";
}

echo "\n";

// 4. Route Analysis
echo "🛣️  4. PUBLIC ROUTE ACCESSIBILITY\n";
echo str_repeat('─', 60) . "\n";

$publicRoutes = [
    'resources.help' => '/resources/help',
    'legal.terms' => '/legal/terms-of-service',
    'landing' => '/',
];

foreach ($publicRoutes as $name => $path) {
    $route = Route::getRoutes()->getByName($name);
    if ($route) {
        $middleware = $route->middleware();
        echo "✅ Route '{$name}' exists ({$path})\n";
        echo "   Middleware: " . (empty($middleware) ? 'none' : implode(', ', $middleware)) . "\n";
    } else {
        echo "❌ Route '{$name}' NOT FOUND ({$path})\n";
    }
}

echo "\n";

// 5. Cache Status
echo "💾 5. CACHE STATUS\n";
echo str_repeat('─', 60) . "\n";

$cacheKeys = [
    'config' => Cache::has('config'),
    'routes' => Cache::has('routes'),
    'views' => Cache::has('views'),
];

echo "Cache store: " . config('cache.default') . "\n";
echo "Note: Laravel does not cache config/routes/views by default in dev mode\n";
echo "If issues persist, run: php artisan optimize:clear\n";

echo "\n";

// 6. Recommendations
echo "💡 6. RECOMMENDATIONS\n";
echo str_repeat('─', 60) . "\n";

$recommendations = [];

if (config('session.driver') === 'cookie') {
    $recommendations[] = "Change SESSION_DRIVER from 'cookie' to 'database' or 'file'";
}

if (config('app.url') && str_contains(config('app.url'), 'https') && app()->environment('local')) {
    $recommendations[] = "Change APP_URL to http:// for local development";
}

$recommendations[] = "Clear all caches: php artisan optimize:clear";
$recommendations[] = "Clear browser cookies for qalcuityerp.test";
$recommendations[] = "Check browser DevTools > Application > Cookies for size > 4096 bytes";

if (!empty($recommendations)) {
    foreach ($recommendations as $i => $rec) {
        echo ($i + 1) . ". {$rec}\n";
    }
} else {
    echo "✅ No critical issues found\n";
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              DIAGNOSTIC COMPLETE                         ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
