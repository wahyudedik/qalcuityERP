<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\CrudCompletenessAnalyzer;
use App\Services\Audit\ModelAnalyzer;
use App\Services\Audit\RouteAnalyzer;
use Illuminate\Console\Command;

class AuditCrudCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:crud {--format=console} {--severity=} {--output=}';
    protected $description = 'Run CRUD completeness audit.';

    public function handle(
        CrudCompletenessAnalyzer $crudAnalyzer,
        ModelAnalyzer $modelAnalyzer,
        RouteAnalyzer $routeAnalyzer
    ): int {
        $report = new AuditReport();
        $report->addAll($crudAnalyzer->analyze());
        $report->addAll($modelAnalyzer->analyze());
        $report->addAll($routeAnalyzer->analyze());

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
