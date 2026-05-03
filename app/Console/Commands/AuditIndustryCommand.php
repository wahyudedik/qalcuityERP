<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\ControllerAnalyzer;
use App\Services\Audit\ModelAnalyzer;
use Illuminate\Console\Command;

class AuditIndustryCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:industry {--module=} {--format=console} {--severity=} {--output=}';
    protected $description = 'Run industry-focused audit scoped by module.';

    public function handle(ControllerAnalyzer $controllerAnalyzer, ModelAnalyzer $modelAnalyzer): int
    {
        $module = strtolower((string) $this->option('module'));
        $report = new AuditReport();

        foreach (array_merge($controllerAnalyzer->analyze(), $modelAnalyzer->analyze()) as $finding) {
            if ($module !== '' && $finding->file !== null && !str_contains(strtolower($finding->file), $module)) {
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
