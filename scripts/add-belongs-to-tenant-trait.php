<?php
/**
 * Script untuk menambahkan trait BelongsToTenant ke semua model yang punya tenant_id.
 * 
 * Cara pakai:
 * php scripts/add-belongs-to-tenant-trait.php
 */

$modelsDir = __DIR__ . '/../app/Models';
$traitUse = 'use App\\Traits\\BelongsToTenant;';
$modelsUpdated = 0;
$modelsSkipped = 0;

// Model yang TIDAK boleh pakai BelongsToTenant (karena sudah handled beda cara)
$skipModels = [
    'Tenant.php',           // Tenant itself
    'User.php',             // User has tenant relation but special handling
    'TenantApiSetting.php', // Already has custom tenant handling
    'SystemSetting.php',    // System-wide, not tenant-specific
    'ErrorLog.php',         // Logged for all tenants
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modelsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php')
        continue;

    $filename = $file->getFilename();
    $filepath = $file->getPathname();

    // Skip jika ada di daftar hitam
    if (in_array($filename, $skipModels)) {
        $modelsSkipped++;
        continue;
    }

    $content = file_get_contents($filepath);

    // Skip jika sudah punya BelongsToTenant
    if (strpos($content, 'BelongsToTenant') !== false) {
        continue;
    }

    // Skip jika tidak punya tenant_id
    if (strpos($content, "'tenant_id'") === false && strpos($content, '"tenant_id"') === false) {
        continue;
    }

    // Tambahkan use statement
    $content = str_replace(
        'namespace App\\Models;',
        "namespace App\\Models;\n\n{$traitUse}",
        $content
    );

    // Tambahkan trait ke use block
    $content = preg_replace(
        '/(class\s+\w+\s+extends\s+Model\s*\{)\s*(use\s+[\w,]+;)/',
        "$1\n    $2, BelongsToTenant;",
        $content,
        1,
        $count
    );

    if ($count === 0) {
        // Coba pattern lain (kalau tidak ada use statement)
        $content = preg_replace(
            '/(class\s+\w+\s+extends\s+Model\s*\{)/',
            "$1\n    use BelongsToTenant;",
            $content,
            1
        );
    }

    file_put_contents($filepath, $content);
    $modelsUpdated++;

    echo "✅ Updated: {$filename}\n";
}

echo "\n========================================\n";
echo "✅ Total models updated: {$modelsUpdated}\n";
echo "⏭️  Total models skipped: {$modelsSkipped}\n";
echo "========================================\n";
