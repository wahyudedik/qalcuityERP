#!/usr/bin/env php
<?php

/**
 * Emergency Cookie & Session Reset Tool
 * 
 * Usage: php emergency-reset.php
 * 
 * This will:
 * 1. Delete ALL sessions from database
 * 2. Clear all caches
 * 3. Generate fresh session table structure
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     🚨 EMERGENCY SESSION & COOKIE RESET TOOL            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  WARNING: This will delete ALL active sessions!\n";
echo "   All users will need to login again.\n\n";

echo "Press ENTER to continue or Ctrl+C to cancel...";
fgets(STDIN);

echo "\n";

// Step 1: Clear all caches
echo "📦 Step 1: Clearing all caches...\n";
Artisan::call('optimize:clear');
echo "✅ Caches cleared\n\n";

// Step 2: Count current sessions
if (Schema::hasTable('sessions')) {
    $count = DB::table('sessions')->count();
    echo "🗄️  Step 2: Found {$count} sessions in database\n";

    // Step 3: Delete ALL sessions
    echo "🗑️  Step 3: Deleting ALL sessions...\n";
    $deleted = DB::table('sessions')->delete();
    echo "✅ Deleted {$deleted} sessions\n\n";

    // Step 4: Optimize sessions table
    echo "🔧 Step 4: Optimizing sessions table...\n";
    try {
        DB::statement('OPTIMIZE TABLE sessions');
        echo "✅ Sessions table optimized\n\n";
    } catch (\Exception $e) {
        echo "ℹ️  Table optimization skipped (not critical)\n\n";
    }
} else {
    echo "❌ Sessions table does not exist!\n";
    echo "   Run: php artisan session:table\n";
    echo "   Then: php artisan migrate\n\n";
    exit(1);
}

// Step 5: Verify
echo "✅ Step 5: Verifying...\n";
$remainingSessions = DB::table('sessions')->count();
echo "   Sessions remaining: {$remainingSessions} (should be 0)\n\n";

// Step 6: Generate instructions
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              🎯 NEXT STEPS (MANUAL)                     ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  CRITICAL: You MUST clear browser cookies manually!\n\n";

echo "📋 INSTRUCTIONS:\n\n";

echo "METHOD 1: Chrome/Edge DevTools (RECOMMENDED)\n";
echo str_repeat('─', 60) . "\n";
echo "1. Press F12 to open DevTools\n";
echo "2. Go to 'Application' tab\n";
echo "3. Left sidebar: Storage → Cookies\n";
echo "4. Click 'http://qalcuityerp.test'\n";
echo "5. Right-click → 'Clear' OR click 🚫 icon\n";
echo "6. Verify: Should show 0 cookies\n";
echo "7. Press Ctrl+Shift+R (hard refresh)\n";
echo "8. Go to: http://qalcuityerp.test/login\n\n";

echo "METHOD 2: Chrome Settings\n";
echo str_repeat('─', 60) . "\n";
echo "1. Press Ctrl+Shift+Delete\n";
echo "2. Time range: 'All time'\n";
echo "3. Check ONLY 'Cookies and other site data'\n";
echo "4. Click 'Clear data'\n";
echo "5. Close ALL browser windows\n";
echo "6. Reopen browser\n";
echo "7. Go to: http://qalcuityerp.test\n\n";

echo "METHOD 3: Nuclear Option (If nothing works)\n";
echo str_repeat('─', 60) . "\n";
echo "1. Close ALL browser windows completely\n";
echo "2. Delete Chrome/Edge profile cookies manually:\n";
echo "   Windows: %LOCALAPPDATA%\\Google\\Chrome\\User Data\\Default\\Cookies\n";
echo "   OR: %LOCALAPPDATA%\\Microsoft\\Edge\\User Data\\Default\\Cookies\n";
echo "3. Delete the file (backup first if needed)\n";
echo "4. Reopen browser\n";
echo "5. Go to: http://qalcuityerp.test\n\n";

echo "METHOD 4: Use Different Browser (Quick Test)\n";
echo str_repeat('─', 60) . "\n";
echo "1. Open Firefox/Edge/Safari (different from Chrome)\n";
echo "2. Go to: http://qalcuityerp.test/login\n";
echo "3. If it works → Confirms Chrome cookies are corrupted\n";
echo "4. Then go back to Method 1 for Chrome\n\n";

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              ✅ VERIFICATION CHECKLIST                  ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "After clearing cookies, verify:\n";
echo "☐ No 'Set-Cookie header is ignored' errors in console\n";
echo "☐ Only 1-2 cookies visible in DevTools (not 50+)\n";
echo "☐ Each cookie size < 1000 bytes (not > 4096)\n";
echo "☐ Login page loads without redirect loop\n";
echo "☐ Can login successfully\n\n";

echo "🚀 If still not working:\n";
echo "   1. Try Incognito mode: Ctrl+Shift+N\n";
echo "   2. Try different browser\n";
echo "   3. Restart Laravel Herd (system tray → Restart PHP)\n";
echo "   4. Run: php artisan optimize:clear\n\n";

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              ✅ RESET COMPLETE                           ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
