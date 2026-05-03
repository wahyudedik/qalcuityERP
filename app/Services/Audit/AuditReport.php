<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use Carbon\Carbon;

/**
 * Audit Report Aggregator
 *
 * Collects AuditFinding instances from analyzers and produces
 * structured output in JSON, Markdown, or summary array format.
 */
class AuditReport
{
    /** @var AuditFinding[] */
    private array $findings = [];

    /**
     * Add a single finding to the report.
     */
    public function add(AuditFinding $finding): void
    {
        $this->findings[] = $finding;
    }

    /**
     * Add multiple findings to the report.
     *
     * @param AuditFinding[] $findings
     */
    public function addAll(array $findings): void
    {
        foreach ($findings as $finding) {
            $this->add($finding);
        }
    }

    /**
     * Get findings, optionally filtered by category and/or severity.
     *
     * @return AuditFinding[]
     */
    public function getFindings(?string $category = null, ?Severity $severity = null): array
    {
        $results = $this->findings;

        if ($category !== null) {
            $results = array_values(array_filter(
                $results,
                fn(AuditFinding $f) => $f->category === $category
            ));
        }

        if ($severity !== null) {
            $results = array_values(array_filter(
                $results,
                fn(AuditFinding $f) => $f->severity === $severity
            ));
        }

        return $results;
    }

    /**
     * Get summary counts grouped by severity and category.
     *
     * @return array{total_findings: int, by_severity: array<string, int>, by_category: array<string, int>}
     */
    public function getSummary(): array
    {
        $bySeverity = [];
        foreach (Severity::cases() as $case) {
            $bySeverity[$case->value] = 0;
        }

        $byCategory = [];

        foreach ($this->findings as $finding) {
            $bySeverity[$finding->severity->value]++;

            if (!isset($byCategory[$finding->category])) {
                $byCategory[$finding->category] = 0;
            }
            $byCategory[$finding->category]++;
        }

        return [
            'total_findings' => count($this->findings),
            'by_severity' => $bySeverity,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Serialize the report to JSON matching the design spec structure.
     */
    public function toJson(): string
    {
        $summary = $this->getSummary();

        $findingsArray = array_map(fn(AuditFinding $f) => [
            'category' => $f->category,
            'severity' => $f->severity->value,
            'title' => $f->title,
            'description' => $f->description,
            'file' => $f->file,
            'line' => $f->line,
            'recommendation' => $f->recommendation,
            'metadata' => $f->metadata,
        ], $this->findings);

        return json_encode([
            'generated_at' => Carbon::now()->toIso8601String(),
            'summary' => $summary,
            'findings' => $findingsArray,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Render the report as a Markdown document with grouped findings
     * and severity badges.
     */
    public function toMarkdown(): string
    {
        $summary = $this->getSummary();
        $lines = [];

        $lines[] = '# ERP Audit Report';
        $lines[] = '';
        $lines[] = '**Generated at:** ' . Carbon::now()->toIso8601String();
        $lines[] = '';

        // Summary section
        $lines[] = '## Summary';
        $lines[] = '';
        $lines[] = '| Metric | Count |';
        $lines[] = '|--------|-------|';
        $lines[] = '| Total Findings | ' . $summary['total_findings'] . ' |';

        foreach ($summary['by_severity'] as $severity => $count) {
            $badge = $this->severityBadge($severity);
            $lines[] = "| {$badge} | {$count} |";
        }

        $lines[] = '';

        // Findings grouped by category
        $grouped = $this->groupByCategory();

        foreach ($grouped as $category => $findings) {
            $lines[] = '## ' . ucfirst($category);
            $lines[] = '';

            foreach ($findings as $finding) {
                $badge = $this->severityBadge($finding->severity->value);
                $lines[] = "### {$badge} {$finding->title}";
                $lines[] = '';
                $lines[] = $finding->description;
                $lines[] = '';

                if ($finding->file) {
                    $location = $finding->file;
                    if ($finding->line) {
                        $location .= ':' . $finding->line;
                    }
                    $lines[] = '**File:** `' . $location . '`';
                    $lines[] = '';
                }

                if ($finding->recommendation) {
                    $lines[] = '**Recommendation:** ' . $finding->recommendation;
                    $lines[] = '';
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Group findings by their category.
     *
     * @return array<string, AuditFinding[]>
     */
    private function groupByCategory(): array
    {
        $grouped = [];

        foreach ($this->findings as $finding) {
            $grouped[$finding->category][] = $finding;
        }

        return $grouped;
    }

    /**
     * Return a Markdown severity badge string.
     */
    private function severityBadge(string $severity): string
    {
        return match ($severity) {
            'critical' => '🔴 Critical',
            'high' => '🟠 High',
            'medium' => '🟡 Medium',
            'low' => '🟢 Low',
            default => $severity,
        };
    }
}
