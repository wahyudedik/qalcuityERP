<?php

namespace Tests\Unit\Services\Audit;

use App\Services\Audit\MigrationGeneratorService;
use PHPUnit\Framework\TestCase;

class MigrationGeneratorServiceTest extends TestCase
{
    private string $tempDir;
    private string $migrationDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/migration_generator_test_' . uniqid();
        $this->migrationDir = $this->tempDir . '/database/migrations';
        mkdir($this->migrationDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_timestamp_generation_is_after_base_timestamp(): void
    {
        $service = new MigrationGeneratorService(
            basePath: $this->tempDir,
            migrationPath: $this->migrationDir
        );

        $first = $service->getNextTimestamp();
        $second = $service->getNextTimestamp();

        $this->assertGreaterThanOrEqual('2026_05_10_000001', $first);
        $this->assertGreaterThan($first, $second);
    }

    public function test_generates_index_migration_with_expected_naming_and_content(): void
    {
        $service = new MigrationGeneratorService($this->tempDir, $this->migrationDir);
        $migration = $service->generateIndexMigration('orders', ['tenant_id', 'status', 'created_at']);

        $this->assertStringContainsString('_add_indexes_to_orders_table.php', $migration['filename']);
        $this->assertStringContainsString("\$table->index('tenant_id');", $migration['content']);
        $this->assertStringContainsString("\$table->index('status');", $migration['content']);
        $this->assertStringContainsString("\$table->index('created_at');", $migration['content']);
    }

    public function test_generates_foreign_key_migration_content(): void
    {
        $service = new MigrationGeneratorService($this->tempDir, $this->migrationDir);
        $migration = $service->generateForeignKeyMigration('orders', ['customer_id' => 'customers']);

        $this->assertStringContainsString('_add_foreign_keys_to_orders_table.php', $migration['filename']);
        $this->assertStringContainsString("\$table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();", $migration['content']);
    }

    public function test_generates_soft_delete_migration_content(): void
    {
        $service = new MigrationGeneratorService($this->tempDir, $this->migrationDir);
        $migration = $service->generateSoftDeleteMigration('orders');

        $this->assertStringContainsString('_add_soft_deletes_to_orders_table.php', $migration['filename']);
        $this->assertStringContainsString("\$table->softDeletes();", $migration['content']);
        $this->assertStringContainsString("\$table->dropSoftDeletes();", $migration['content']);
    }

    public function test_generates_enum_expansion_migration_content(): void
    {
        $service = new MigrationGeneratorService($this->tempDir, $this->migrationDir);
        $migration = $service->generateEnumExpansionMigration('orders', 'status', ['draft', 'paid', 'cancelled']);

        $this->assertStringContainsString('_expand_enum_orders_status.php', $migration['filename']);
        $this->assertStringContainsString("ALTER TABLE orders MODIFY status ENUM('draft', 'paid', 'cancelled')", $migration['content']);
        $this->assertStringContainsString('use Illuminate\Support\Facades\DB;', $migration['content']);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
