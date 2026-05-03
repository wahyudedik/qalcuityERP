<?php

namespace App\Services\Audit;

class MigrationGeneratorService
{
    private string $basePath;
    private string $migrationPath;
    private int $counter = 0;

    private const BASE_TIMESTAMP = '2026_05_10_000001';

    public function __construct(?string $basePath = null, ?string $migrationPath = null)
    {
        if ($basePath !== null) {
            $this->basePath = $basePath;
        } else {
            try {
                $this->basePath = base_path();
            } catch (\Throwable) {
                $this->basePath = getcwd();
            }
        }

        $this->migrationPath = $migrationPath ?? ($this->basePath . '/database/migrations');
    }

    /**
     * @param string[] $columns
     * @return array{filename:string, content:string}
     */
    public function generateIndexMigration(string $table, array $columns): array
    {
        $class = 'AddIndexesTo' . $this->studly($table) . 'Table';
        $name = 'add_indexes_to_' . $table . '_table';
        $timestamp = $this->getNextTimestamp();
        $filename = "{$timestamp}_{$name}.php";
        $indexLines = array_map(
            static fn (string $column): string => "\$table->index('{$column}');",
            $columns
        );

        $content = $this->makeMigrationTemplate(
            className: $class,
            upBody: "Schema::table('{$table}', function (Blueprint \$table): void {\n            " . implode("\n            ", $indexLines) . "\n        });",
            downBody: "Schema::table('{$table}', function (Blueprint \$table): void {\n            // TODO: drop indexes if needed.\n        });",
        );

        return ['filename' => $filename, 'content' => $content];
    }

    /**
     * @param array<string, string> $foreignKeys key=column, value=referenced table
     * @return array{filename:string, content:string}
     */
    public function generateForeignKeyMigration(string $table, array $foreignKeys): array
    {
        $class = 'AddForeignKeysTo' . $this->studly($table) . 'Table';
        $name = 'add_foreign_keys_to_' . $table . '_table';
        $timestamp = $this->getNextTimestamp();
        $filename = "{$timestamp}_{$name}.php";
        $fkLines = [];

        foreach ($foreignKeys as $column => $referencedTable) {
            $fkLines[] = "\$table->foreign('{$column}')->references('id')->on('{$referencedTable}')->nullOnDelete();";
        }

        $content = $this->makeMigrationTemplate(
            className: $class,
            upBody: "Schema::table('{$table}', function (Blueprint \$table): void {\n            " . implode("\n            ", $fkLines) . "\n        });",
            downBody: "Schema::table('{$table}', function (Blueprint \$table): void {\n            // TODO: drop foreign keys if needed.\n        });",
        );

        return ['filename' => $filename, 'content' => $content];
    }

    /**
     * @return array{filename:string, content:string}
     */
    public function generateSoftDeleteMigration(string $table): array
    {
        $class = 'AddSoftDeletesTo' . $this->studly($table) . 'Table';
        $name = 'add_soft_deletes_to_' . $table . '_table';
        $timestamp = $this->getNextTimestamp();
        $filename = "{$timestamp}_{$name}.php";

        $content = $this->makeMigrationTemplate(
            className: $class,
            upBody: "Schema::table('{$table}', function (Blueprint \$table): void {\n            \$table->softDeletes();\n        });",
            downBody: "Schema::table('{$table}', function (Blueprint \$table): void {\n            \$table->dropSoftDeletes();\n        });",
        );

        return ['filename' => $filename, 'content' => $content];
    }

    /**
     * @param string[] $allowedValues
     * @return array{filename:string, content:string}
     */
    public function generateEnumExpansionMigration(string $table, string $column, array $allowedValues): array
    {
        $class = 'ExpandEnum' . $this->studly($table) . $this->studly($column);
        $name = 'expand_enum_' . $table . '_' . $column;
        $timestamp = $this->getNextTimestamp();
        $filename = "{$timestamp}_{$name}.php";
        $enumValues = implode("', '", $allowedValues);

        $content = $this->makeMigrationTemplate(
            className: $class,
            upBody: "DB::statement(\"ALTER TABLE {$table} MODIFY {$column} ENUM('{$enumValues}')\");",
            downBody: '// No automatic rollback for enum contractions.',
            needsDbFacade: true,
        );

        return ['filename' => $filename, 'content' => $content];
    }

    public function getNextTimestamp(): string
    {
        $base = \DateTimeImmutable::createFromFormat('Y_m_d_His', self::BASE_TIMESTAMP);
        if ($base === false) {
            throw new \RuntimeException('Invalid base timestamp format.');
        }

        $next = $base->modify('+' . $this->counter . ' seconds');
        $this->counter++;

        return $next->format('Y_m_d_His');
    }

    public function writeMigrationFile(string $filename, string $content): string
    {
        if (!is_dir($this->migrationPath)) {
            mkdir($this->migrationPath, 0777, true);
        }

        $fullPath = $this->migrationPath . '/' . $filename;
        file_put_contents($fullPath, $content);

        return $fullPath;
    }

    private function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', strtolower($value));
        return str_replace(' ', '', ucwords($value));
    }

    private function makeMigrationTemplate(
        string $className,
        string $upBody,
        string $downBody,
        bool $needsDbFacade = false,
    ): string {
        $dbImport = $needsDbFacade ? "use Illuminate\\Support\\Facades\\DB;\n" : '';

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
{$dbImport}
return new class extends Migration
{
    public function up(): void
    {
        {$upBody}
    }

    public function down(): void
    {
        {$downBody}
    }
};
PHP;
    }
}
