<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\AnalyzerInterface;
use App\Services\Audit\AuditReport;
use App\Services\Audit\BusinessFlowAnalyzer;
use App\Services\Audit\ControllerAnalyzer;
use App\Services\Audit\CrudCompletenessAnalyzer;
use App\Services\Audit\IntegrationAnalyzer;
use App\Services\Audit\ModelAnalyzer;
use App\Services\Audit\PerformanceAnalyzer;
use App\Services\Audit\PermissionAnalyzer;
use App\Services\Audit\QueryAnalyzer;
use App\Services\Audit\RouteAnalyzer;
use App\Services\Audit\SecurityAnalyzer;
use App\Services\Audit\TenantIsolationAnalyzer;
use App\Services\Audit\ViewAnalyzer;
use Illuminate\Console\Command;

class AuditAllCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:all {--format=console} {--severity=} {--output=}';

    protected $description = 'Run all audit analyzers and aggregate findings.';

    public function handle(): int
    {
        $report = new AuditReport;
        $analyzerClasses = [
            ControllerAnalyzer::class,
            QueryAnalyzer::class,
            ModelAnalyzer::class,
            RouteAnalyzer::class,
            TenantIsolationAnalyzer::class,
            PermissionAnalyzer::class,
            CrudCompletenessAnalyzer::class,
            BusinessFlowAnalyzer::class,
            ViewAnalyzer::class,
            IntegrationAnalyzer::class,
            SecurityAnalyzer::class,
            PerformanceAnalyzer::class,
        ];

        foreach ($analyzerClasses as $class) {
            try {
                /** @var AnalyzerInterface $analyzer */
                $analyzer = app($class);
                $report->addAll($analyzer->analyze());
            } catch (\Throwable $e) {
                $report->add(new AuditFinding(
                    category: 'system',
                    severity: Severity::High,
                    title: 'Analyzer execution failed: '.class_basename($class),
                    description: $e->getMessage(),
                    file: null,
                    line: null,
                    recommendation: 'Inspect analyzer dependencies and fix runtime error.',
                    metadata: ['analyzer' => $class],
                ));
            }
        }

        $severity = $this->resolveSeverityFilter($this->option('severity'));
        $filtered = new AuditReport;
        $filtered->addAll($report->getFindings(severity: $severity));

        $output = $this->option('output') ? (string) $this->option('output') : null;
        if ($output !== null && $output !== '') {
            $dir = dirname($output);
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        $this->renderAuditReport($filtered, (string) $this->option('format'), $output);

        return self::SUCCESS;
    }
}
