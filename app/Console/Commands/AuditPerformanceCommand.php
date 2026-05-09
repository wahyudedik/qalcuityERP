<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\PerformanceAnalyzer;
use App\Services\Audit\QueryAnalyzer;
use Illuminate\Console\Command;

class AuditPerformanceCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:performance {--format=console} {--severity=} {--output=}';

    protected $description = 'Run performance audit (pagination, eager loading, exports, jobs).';

    public function handle(PerformanceAnalyzer $performanceAnalyzer, QueryAnalyzer $queryAnalyzer): int
    {
        $report = new AuditReport;
        $report->addAll($performanceAnalyzer->analyze());
        $report->addAll($queryAnalyzer->analyze());

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
