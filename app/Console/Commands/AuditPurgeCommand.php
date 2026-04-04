<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class AuditPurgeCommand extends Command
{
    protected $signature = 'audit:purge
                            {--days= : Retention period in days (default from config)}
                            {--tenant= : Purge only for specific tenant}
                            {--dry-run : Show count without deleting}
                            {--include-compliance : Also purge compliance-hold entries (AI actions & rolled-back entries)}';

    protected $description = 'Purge old audit trail entries beyond retention period (compliance-hold entries are preserved by default)';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('audit.retention_days', 365));
        $cutoff = now()->subDays($days);
        $tenantId = $this->option('tenant');
        $includeCompliance = $this->option('include-compliance');

        $query = ActivityLog::where('created_at', '<', $cutoff);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        // Compliance hold: by default, preserve rolled-back entries and AI action entries
        // because they may be required for SOX/compliance audits.
        // Pass --include-compliance to override.
        if (!$includeCompliance) {
            $query->whereNull('rolled_back_at')
                ->where('is_ai_action', false);
        }

        $count = $query->count();

        if ($count === 0) {
            $label = $includeCompliance ? '' : ' (non-compliance-hold)';
            $this->info("No audit logs{$label} older than {$days} days found.");
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $hold = $includeCompliance
                ? ''
                : ' (compliance-hold entries skipped — use --include-compliance to include them)';
            $this->info("[Dry run] Would purge {$count} audit log entries older than {$days} days (before {$cutoff->toDateString()}){$hold}.");
            return self::SUCCESS;
        }

        if (!$this->option('no-interaction') && !$this->confirm("Delete {$count} audit log entries older than {$days} days?")) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        // Delete in chunks to avoid memory issues
        $deleted = 0;

        do {
            $chunkQuery = ActivityLog::where('created_at', '<', $cutoff);
            if ($tenantId) {
                $chunkQuery->where('tenant_id', $tenantId);
            }
            if (!$includeCompliance) {
                $chunkQuery->whereNull('rolled_back_at')
                    ->where('is_ai_action', false);
            }
            $batch = $chunkQuery->limit(1000)->delete();
            $deleted += $batch;
            if ($batch > 0) {
                $this->output->write('.');
            }
        } while ($batch > 0);

        $this->newLine();
        $this->info("Purged {$deleted} audit log entries older than {$days} days.");

        if (!$includeCompliance) {
            $holdCount = ActivityLog::where('created_at', '<', $cutoff)
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->where(fn($q) => $q->whereNotNull('rolled_back_at')->orWhere('is_ai_action', true))
                ->count();
            if ($holdCount > 0) {
                $this->line("<comment>Note: {$holdCount} compliance-hold entries (rolled-back / AI) were preserved. Use --include-compliance to purge them.</comment>");
            }
        }

        return self::SUCCESS;
    }
}
