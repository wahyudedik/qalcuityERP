<?php

$folder = 'tests/Feature/BugExploration';
$files = glob($folder . '/*.php');

foreach ($files as $path) {
    $content = file_get_contents($path);
    $original = $content;

    // 1. Replace RefreshDatabase import
    $content = str_replace(
        'use Illuminate\Foundation\Testing\RefreshDatabase;',
        'use Illuminate\Foundation\Testing\DatabaseTransactions;',
        $content
    );

    // 2. Replace use RefreshDatabase; trait
    $content = str_replace('use RefreshDatabase;', 'use DatabaseTransactions;', $content);

    // 3. Replace Tenant::factory()->create([...]) - handle multiline
    $content = preg_replace_callback(
        '/Tenant::factory\(\)->create\(\[([^\]]+)\]\)/s',
        function ($m) {
            $inner = $m[1];
            if (preg_match("/'plan'\s*=>\s*'([^']+)'/", $inner, $pm)) {
                $plan = $pm[1];
                return "\$this->createTenant(['plan' => '{$plan}'])";
            }
            return '$this->createTenant()';
        },
        $content
    );

    // 4. Replace User::factory()->create([...]) - handle multiline
    // For SecurityTenantScopeTest and SecurityExportOwnershipTest with tenantA/tenantB
    $content = preg_replace_callback(
        '/(\$this->user[A-Za-z0-9]*)\s*=\s*User::factory\(\)->create\(\[([^\]]+)\]\)/s',
        function ($m) {
            $varName = trim($m[1]);
            $inner = $m[2];
            // Detect which tenant variable is referenced
            if (preg_match('/tenant_id.*\$this->tenant([A-Z])/s', $inner, $tm)) {
                $suffix = $tm[1];
                return "{$varName} = \$this->createAdminUser(\$this->tenant{$suffix})";
            }
            return "{$varName} = \$this->createAdminUser(\$this->tenant)";
        },
        $content
    );

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: " . basename($path) . "\n";
    } else {
        echo "No change: " . basename($path) . "\n";
    }
}

echo "Done.\n";
