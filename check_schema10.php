<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=qalcuity_erp', 'root', '');

$tables = ['breeding_records', 'livestock_health_records', 'waste_management_logs', 'fishing_vessels', 'fishing_trips', 'aquaculture_ponds', 'water_quality_logs', 'cold_storage_units', 'temperature_logs'];

foreach ($tables as $table) {
    echo "\n=== $table ===\n";
    try {
        $stmt = $pdo->query("DESCRIBE `$table`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "  TABLE NOT FOUND\n";
    }
}
