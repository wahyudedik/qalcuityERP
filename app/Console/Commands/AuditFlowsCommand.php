<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\BusinessFlowAnalyzer;
use Illuminate\Console\Command;

class AuditFlowsCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:flows {--flow=} {--format=console} {--severity=} {--output=}';
    protected $description = 'Run business flow audit and optionally filter by flow.';

    public function handle(BusinessFlowAnalyzer $analyzer): int
    {
        $flow = strtolower((string) $this->option('flow'));
        $report = new AuditReport();

        foreach ($analyzer->analyze() as $finding) {
            if ($flow !== '' && !str_contains(strtolower($finding->title . ' ' . $finding->description), $flow)) {
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
