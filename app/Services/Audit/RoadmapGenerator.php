<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

class RoadmapGenerator
{
    /**
     * @param  AuditFinding[]  $findings
     * @return array<string, mixed>
     */
    public function generate(array $findings): array
    {
        $ordered = $this->sortBySeverity($findings);

        return [
            'summary' => [
                'total' => count($ordered),
                'critical' => count(array_filter($ordered, fn (AuditFinding $f) => $f->severity === Severity::Critical)),
                'high' => count(array_filter($ordered, fn (AuditFinding $f) => $f->severity === Severity::High)),
                'medium' => count(array_filter($ordered, fn (AuditFinding $f) => $f->severity === Severity::Medium)),
                'low' => count(array_filter($ordered, fn (AuditFinding $f) => $f->severity === Severity::Low)),
            ],
            'prioritized_findings' => array_map(fn (AuditFinding $f) => [
                'category' => $f->category,
                'severity' => $f->severity->value,
                'title' => $f->title,
                'description' => $f->description,
                'recommendation' => $f->recommendation,
                'timeframe' => $this->categorizeByTimeframe($f),
            ], $ordered),
            'timeframes' => $this->groupByTimeframe($ordered),
            'feature_gap_analysis' => $this->generateFeatureGapAnalysis(),
            'permission_matrix' => $this->generatePermissionMatrix($ordered),
            'crud_matrix' => $this->generateCrudMatrix($ordered),
            'migration_plan' => $this->generateMigrationPlan($ordered),
        ];
    }

    public function categorizeByTimeframe(AuditFinding $finding): string
    {
        return match ($finding->severity) {
            Severity::Critical, Severity::High => 'short-term',
            Severity::Medium => 'medium-term',
            Severity::Low => 'long-term',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function generateFeatureGapAnalysis(): array
    {
        return [
            'status' => 'placeholder',
            'notes' => 'Feature gap analysis can be enriched by comparing README module claims against current implementation.',
        ];
    }

    /**
     * @param  AuditFinding[]  $findings
     * @return array<int, array<string, string>>
     */
    public function generatePermissionMatrix(array $findings): array
    {
        $rows = [];
        foreach ($findings as $finding) {
            if (! in_array($finding->category, ['permission', 'route', 'security'], true)) {
                continue;
            }

            $rows[] = [
                'area' => $finding->category,
                'issue' => $finding->title,
                'severity' => $finding->severity->value,
                'action' => $finding->recommendation ?? 'Review access control policy.',
            ];
        }

        return $rows;
    }

    /**
     * @param  AuditFinding[]  $findings
     * @return array<int, array<string, string>>
     */
    public function generateCrudMatrix(array $findings): array
    {
        $rows = [];
        foreach ($findings as $finding) {
            if (! in_array($finding->category, ['crud', 'model', 'controller'], true)) {
                continue;
            }

            $rows[] = [
                'entity' => (string) ($finding->metadata['model'] ?? $finding->metadata['controller'] ?? 'Unknown'),
                'status' => $finding->severity === Severity::Low ? 'partial' : 'missing',
                'issue' => $finding->title,
            ];
        }

        return $rows;
    }

    /**
     * @param  AuditFinding[]  $findings
     * @return array<int, array<string, string>>
     */
    public function generateMigrationPlan(array $findings): array
    {
        $rows = [];
        foreach ($findings as $finding) {
            if (! in_array($finding->category, ['model', 'database', 'tenancy'], true)) {
                continue;
            }

            $rows[] = [
                'priority' => $finding->severity->value,
                'task' => $finding->title,
                'recommendation' => $finding->recommendation ?? 'Review schema and migration dependencies.',
            ];
        }

        return $rows;
    }

    /**
     * @param  AuditFinding[]  $findings
     * @return AuditFinding[]
     */
    private function sortBySeverity(array $findings): array
    {
        $weight = [
            Severity::Critical->value => 4,
            Severity::High->value => 3,
            Severity::Medium->value => 2,
            Severity::Low->value => 1,
        ];

        usort($findings, static function (AuditFinding $a, AuditFinding $b) use ($weight): int {
            return $weight[$b->severity->value] <=> $weight[$a->severity->value];
        });

        return $findings;
    }

    /**
     * @param  AuditFinding[]  $findings
     * @return array<string, array<int, string>>
     */
    private function groupByTimeframe(array $findings): array
    {
        $groups = [
            'short-term' => [],
            'medium-term' => [],
            'long-term' => [],
        ];

        foreach ($findings as $finding) {
            $groups[$this->categorizeByTimeframe($finding)][] = $finding->title;
        }

        return $groups;
    }
}
