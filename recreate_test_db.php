<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec('DROP DATABASE IF EXISTS qalcuity_erp_test');
    $pdo->exec('CREATE DATABASE qalcuity_erp_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "Test database recreated successfully\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
