<?php
/**
 * Script untuk audit index tenant_id di semua table
 * Mengidentifikasi table yang perlu ditambahkan index
 * 
 * Cara pakai:
 * php scripts/audit-tenant-indexes.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Auditing tenant_id indexes...\n\n";

// Get semua table yang punya kolom tenant_id
$tables = DB::select("
    SELECT TABLE_NAME, COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = ? 
    AND COLUMN_NAME = 'tenant_id'
    ORDER BY TABLE_NAME
", [config('database.connections.mysql.database')]);

$tablesWithTenantId = [];
$tablesWithIndex = [];
$tablesWithoutIndex = [];

foreach ($tables as $table) {
    $tableName = $table->TABLE_NAME;
    $tablesWithTenantId[] = $tableName;

    // Check apakah sudah ada index di tenant_id
    $indexes = DB::select("
        SHOW INDEX FROM `{$tableName}`
        WHERE Column_name = 'tenant_id'
    ");

    if (count($indexes) > 0) {
        $tablesWithIndex[] = $tableName;
    } else {
        $tablesWithoutIndex[] = $tableName;
    }
}

// Print summary
echo "📊 AUDIT SUMMARY\n";
echo "================\n\n";
echo "Tables with tenant_id column: " . count($tablesWithTenantId) . "\n";
echo "✅ Tables WITH index: " . count($tablesWithIndex) . "\n";
echo "⚠️  Tables WITHOUT index: " . count($tablesWithoutIndex) . "\n\n";

if (!empty($tablesWithoutIndex)) {
    echo "⚠️  TABLES NEEDING INDEX:\n";
    echo "========================\n\n";

    foreach ($tablesWithoutIndex as $tableName) {
        // Get row count
        $rowCount = DB::table($tableName)->count();
        $severity = $rowCount > 10000 ? '🔴 HIGH' : ($rowCount > 1000 ? '🟡 MEDIUM' : '🟢 LOW');

        echo "{$severity} {$tableName} ({$rowCount} rows)\n";
    }

    echo "\n";
}

// Generate migration code
if (!empty($tablesWithoutIndex)) {
    echo "📝 MIGRATION CODE TO ADD:\n";
    echo "========================\n\n";

    echo "<?php\n\n";
    echo "use Illuminate\\Database\\Migrations\\Migration;\n";
    echo "use Illuminate\\Database\\Schema\\Blueprint;\n";
    echo "use Illuminate\\Support\\Facades\\Schema;\n\n";
    echo "return new class extends Migration\n";
    echo "{\n";
    echo "    public function up(): void\n";
    echo "    {\n";

    foreach ($tablesWithoutIndex as $tableName) {
        echo "        Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        echo "            \$table->index('tenant_id');\n";
        echo "        });\n";
    }

    echo "    }\n\n";
    echo "    public function down(): void\n";
    echo "    {\n";

    foreach ($tablesWithoutIndex as $tableName) {
        echo "        Schema::table('{$tableName}', function (Blueprint \$table) {\n";
        echo "            \$table->dropIndex(['tenant_id']);\n";
        echo "        });\n";
    }

    echo "    }\n";
    echo "};\n";

    echo "\n";
}

// Export to JSON for reference
$export = [
    'total_tables_with_tenant_id' => count($tablesWithTenantId),
    'tables_with_index' => $tablesWithIndex,
    'tables_without_index' => $tablesWithoutIndex,
    'recommendation' => 'Add index to all tables in tables_without_index array',
];

$outputFile = __DIR__ . '/tenant-index-audit.json';
file_put_contents($outputFile, json_encode($export, JSON_PRETTY_PRINT));

echo "📄 Full audit exported to: {$outputFile}\n";
echo "\n✅ Audit complete!\n";
