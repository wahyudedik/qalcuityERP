<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\ModelAnalyzer;
use App\Services\Audit\TenantIsolationAnalyzer;
use Illuminate\Console\Command;

class AuditTenancyCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:tenancy {--format=console} {--severity=} {--output=}';

    protected $description = 'Run tenant isolation audit.';

    public function handle(TenantIsolationAnalyzer $tenantAnalyzer, ModelAnalyzer $modelAnalyzer): int
    {
        $report = new AuditReport;
        $report->addAll($tenantAnalyzer->analyze());
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
