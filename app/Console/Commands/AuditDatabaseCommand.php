<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\MigrationGeneratorService;
use App\Services\Audit\ModelAnalyzer;
use Illuminate\Console\Command;

class AuditDatabaseCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:database {--generate-migrations} {--dry-run} {--format=console} {--severity=} {--output=}';
    protected $description = 'Run database/schema audit and optionally generate migration files.';

    public function handle(ModelAnalyzer $modelAnalyzer, MigrationGeneratorService $migrationGenerator): int
    {
        $report = new AuditReport();
        $report->addAll($modelAnalyzer->analyze());

        if ($this->option('generate-migrations') || $this->option('dry-run')) {
            $migration = $migrationGenerator->generateIndexMigration('users', ['tenant_id', 'created_at']);
            if ($this->option('dry-run')) {
                $this->info('Dry-run migration preview: ' . $migration['filename']);
                $this->line($migration['content']);
            } else {
                $path = $migrationGenerator->writeMigrationFile($migration['filename'], $migration['content']);
                $this->info("Migration generated: {$path}");
            }
        }

        $severity = $this->resolveSeverityFilter($this->option('severity'));
        $filtered = new AuditReport();
        $filtered->addAll($report->getFindings(severity: $severity));

        $this->renderAuditReport(
            $filtered,
            (string) $this->option('format'),
            $this->option('output') ? (string) $this->option('output') : null
        );

        return self::SUCCESS;
    }
}
