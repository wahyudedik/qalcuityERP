<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\ViewAnalyzer;
use Illuminate\Console\Command;

class AuditUiCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:ui {--directory=} {--format=console} {--severity=} {--output=}';
    protected $description = 'Run UI/responsiveness/accessibility audit.';

    public function handle(ViewAnalyzer $analyzer): int
    {
        $directory = strtolower((string) $this->option('directory'));
        $report = new AuditReport();

        foreach ($analyzer->analyze() as $finding) {
            if ($directory !== '' && $finding->file !== null && !str_contains(strtolower($finding->file), $directory)) {
                continue;
            }
            $report->add($finding);
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
