<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HandlesAuditOutput;
use App\Services\Audit\AuditReport;
use App\Services\Audit\BusinessFlowAnalyzer;
use App\Services\Audit\ControllerAnalyzer;
use App\Services\Audit\CrudCompletenessAnalyzer;
use App\Services\Audit\IntegrationAnalyzer;
use App\Services\Audit\ModelAnalyzer;
use App\Services\Audit\PermissionAnalyzer;
use App\Services\Audit\QueryAnalyzer;
use App\Services\Audit\RoadmapGenerator;
use App\Services\Audit\RouteAnalyzer;
use App\Services\Audit\SecurityAnalyzer;
use App\Services\Audit\TenantIsolationAnalyzer;
use App\Services\Audit\ViewAnalyzer;
use Illuminate\Console\Command;

class AuditRoadmapCommand extends Command
{
    use HandlesAuditOutput;

    protected $signature = 'audit:roadmap {--output=} {--format=json}';
    protected $description = 'Generate prioritized roadmap and audit matrices.';

    public function handle(
        ControllerAnalyzer $controllerAnalyzer,
        QueryAnalyzer $queryAnalyzer,
        ModelAnalyzer $modelAnalyzer,
        RouteAnalyzer $routeAnalyzer,
        TenantIsolationAnalyzer $tenantAnalyzer,
        PermissionAnalyzer $permissionAnalyzer,
        CrudCompletenessAnalyzer $crudAnalyzer,
        BusinessFlowAnalyzer $flowAnalyzer,
        ViewAnalyzer $viewAnalyzer,
        IntegrationAnalyzer $integrationAnalyzer,
        SecurityAnalyzer $securityAnalyzer,
        RoadmapGenerator $roadmapGenerator
    ): int {
        $report = new AuditReport();
        foreach ([
            $controllerAnalyzer,
            $queryAnalyzer,
            $modelAnalyzer,
            $routeAnalyzer,
            $tenantAnalyzer,
            $permissionAnalyzer,
            $crudAnalyzer,
            $flowAnalyzer,
            $viewAnalyzer,
            $integrationAnalyzer,
            $securityAnalyzer,
        ] as $analyzer) {
            $report->addAll($analyzer->analyze());
        }

        $roadmap = $roadmapGenerator->generate($report->getFindings());
        $payload = json_encode($roadmap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->line((string) $payload);

        $output = $this->option('output') ? (string) $this->option('output') : null;
        if ($output !== null && $output !== '') {
            if (!is_dir($output)) {
                mkdir($output, 0777, true);
            }
            file_put_contents(rtrim($output, '/\\') . '/audit-roadmap.json', (string) $payload);
            $this->info('Roadmap written to ' . rtrim($output, '/\\') . '/audit-roadmap.json');
        }

        return self::SUCCESS;
    }
}
