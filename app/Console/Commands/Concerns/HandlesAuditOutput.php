<?php

namespace App\Console\Commands\Concerns;

use App\DTOs\Audit\Severity;
use App\Services\Audit\AuditReport;
use Illuminate\Console\Command;

/**
 * @mixin Command
 */
trait HandlesAuditOutput
{
    abstract protected function line($string, $verbosity = null);

    abstract protected function info($string, $verbosity = null);

    abstract protected function table($headers, $rows, $tableStyle = 'default', array $columnStyles = []);

    protected function resolveSeverityFilter(?string $severity): ?Severity
    {
        if ($severity === null || $severity === '') {
            return null;
        }

        return match (strtolower($severity)) {
            'critical' => Severity::Critical,
            'high' => Severity::High,
            'medium' => Severity::Medium,
            'low' => Severity::Low,
            default => null,
        };
    }

    protected function renderAuditReport(AuditReport $report, string $format, ?string $outputPath = null): void
    {
        $format = strtolower($format);
        if (! in_array($format, ['console', 'json', 'markdown'], true)) {
            $format = 'console';
        }

        if ($format === 'json') {
            $payload = $report->toJson();
            $this->line($payload);
            if ($outputPath) {
                file_put_contents($outputPath, $payload);
                $this->info("Report written to {$outputPath}");
            }

            return;
        }

        if ($format === 'markdown') {
            $payload = $report->toMarkdown();
            $this->line($payload);
            if ($outputPath) {
                file_put_contents($outputPath, $payload);
                $this->info("Report written to {$outputPath}");
            }

            return;
        }

        $rows = [];
        foreach ($report->getFindings() as $finding) {
            $rows[] = [
                strtoupper($finding->severity->value),
                $finding->category,
                $finding->title,
                $finding->file ?? '-',
                $finding->line ?? '-',
            ];
        }

        $this->table(['Severity', 'Category', 'Title', 'File', 'Line'], $rows);
        $summary = $report->getSummary();
        $this->info('Total findings: '.$summary['total_findings']);
    }
}
