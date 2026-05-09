<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\SecurityAnalyzer;
use Illuminate\Console\Command;

class AuditSecurityCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:security {--format=console} {--severity=} {--output=}';

    protected $description = 'Run security audit.';

    public function handle(SecurityAnalyzer $analyzer): int
    {
        $report = new AuditReport;
        $report->addAll($analyzer->analyze());

        $severity = $this->resolveSeverityFilter($this->option('severity'));
        $filtered = new AuditReport;
        $filtered->addAll($report->getFindings(severity: $severity));

        $this->renderAuditReport(
            $filtered,
            (string) $this->option('format'),
            $this->option('output') ? (string) $this->option('output') : null
        );

        return self::SUCCESS;
    }
}
