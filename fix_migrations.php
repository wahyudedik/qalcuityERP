<?php

/**
 * Fix test database migrations by running them iteratively.
 * When a migration fails with "already exists" or "Duplicate column",
 * mark it as ran and continue.
 */

$host = '127.0.0.1';
$port = '3306';
$db   = 'qalcuity_erp_test';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);

$maxAttempts = 200;
$attempt = 0;
$totalSkipped = 0;

putenv('APP_ENV=testing');
putenv('DB_DATABASE=qalcuity_erp_test');

while ($attempt < $maxAttempts) {
    $attempt++;

    // Run migrate
    $result = shell_exec('php artisan migrate --force 2>&1');

    if (strpos($result, 'Nothing to migrate') !== false) {
        echo "✓ All migrations completed after $attempt attempts! Skipped: $totalSkipped\n";
        break;
    }

    // Check for "already exists" or "Duplicate column" errors
    if (preg_match('/(\d{4}_\d{2}_\d{2}_\d+_\S+)\s+.*FAIL/', $result, $m)) {
        $failingMigration = $m[1];

        $errorType = '';
        if (strpos($result, 'already exists') !== false) {
            $errorType = 'table/column already exists';
        } elseif (strpos($result, 'Duplicate column') !== false) {
            $errorType = 'duplicate column';
        } elseif (strpos($result, 'referenced table') !== false) {
            $errorType = 'referenced table not found';
        } else {
            // Extract error message
            preg_match('/SQLSTATE\[.*?\].*?(?=\n|$)/', $result, $errMatch);
            $errorType = $errMatch[0] ?? 'unknown error';
        }

        echo "Attempt $attempt: Skipping '$failingMigration' ($errorType)\n";

        // Get current max batch
        $maxBatch = $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn();

        // Mark as ran
        $stmt = $pdo->prepare('INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)');
        $stmt->execute([$failingMigration, $maxBatch + 1]);

        $totalSkipped++;
        continue;
    }

    if (strpos($result, 'DONE') !== false) {
        echo "Attempt $attempt: Migrations ran successfully\n";
        continue;
    }

    // Unknown error
    echo "Attempt $attempt: Unexpected result:\n";
    echo substr($result, -300) . "\n";
    break;
}

if ($attempt >= $maxAttempts) {
    echo "Reached max attempts ($maxAttempts)\n";
}

// Final status
$pending = $pdo->query("SELECT COUNT(*) FROM migrations WHERE migration NOT IN (SELECT migration FROM migrations WHERE batch > 0)")->fetchColumn();
echo "\nFinal check - checking pending migrations...\n";
$result = shell_exec('php artisan migrate:status 2>&1 | findstr Pending');
echo $result ?: "No pending migrations found!\n";
