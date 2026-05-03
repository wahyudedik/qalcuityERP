<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\PermissionAnalyzer;
use App\Services\Audit\RouteAnalyzer;
use Illuminate\Console\Command;

class AuditPermissionsCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:permissions {--format=console} {--severity=} {--output=}';
    protected $description = 'Run permission and route authorization audit.';

    public function handle(PermissionAnalyzer $permissionAnalyzer, RouteAnalyzer $routeAnalyzer): int
    {
        $report = new AuditReport();
        $report->addAll($permissionAnalyzer->analyze());
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
