<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\ControllerAnalyzer;
use App\Services\Audit\ModelAnalyzer;
use App\Services\Audit\QueryAnalyzer;
use Illuminate\Console\Command;

class AuditCoreCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:core {--format=console} {--severity=} {--output=}';

    protected $description = 'Run core audit analyzers (controller, query, model).';

    public function handle(
        ControllerAnalyzer $controllerAnalyzer,
        QueryAnalyzer $queryAnalyzer,
        ModelAnalyzer $modelAnalyzer
    ): int {
        $report = new AuditReport;
        $report->addAll($controllerAnalyzer->analyze());
        $report->addAll($queryAnalyzer->analyze());
        $report->addAll($modelAnalyzer->analyze());

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
