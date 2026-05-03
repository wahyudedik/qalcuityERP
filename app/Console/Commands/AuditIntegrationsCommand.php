<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\IntegrationAnalyzer;
use Illuminate\Console\Command;

class AuditIntegrationsCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:integrations {--service=} {--format=console} {--severity=} {--output=}';
    protected $description = 'Run integration audit (payment, ecommerce, messaging, AI, HTTP patterns).';

    public function handle(IntegrationAnalyzer $analyzer): int
    {
        $service = strtolower((string) $this->option('service'));
        $report = new AuditReport();

        foreach ($analyzer->analyze() as $finding) {
            if ($service !== '' && !str_contains(strtolower($finding->title . ' ' . $finding->description), $service)) {
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
