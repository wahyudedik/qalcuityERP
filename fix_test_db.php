<?php
// Fix test database by killing locks and recreating it
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');

// Kill any waiting processes
$processes = $pdo->query("SHOW PROCESSLIST")->fetchAll(PDO::FETCH_ASSOC);
foreach ($processes as $proc) {
    if ($proc['db'] === 'qalcuity_erp_test' && $proc['Command'] !== 'Sleep') {
        echo "Killing process {$proc['Id']}: {$proc['Command']} - {$proc['Info']}\n";
        try {
            $pdo->exec("KILL {$proc['Id']}");
        } catch (Exception $e) {
            echo "Could not kill: " . $e->getMessage() . "\n";
        }
    }
}

// Drop and recreate
echo "Dropping database...\n";
$pdo->exec("DROP DATABASE IF EXISTS qalcuity_erp_test");
echo "Creating database...\n";
$pdo->exec("CREATE DATABASE qalcuity_erp_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Done!\n";
