<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class AuditPurgeCommand extends Command
{
    protected $signature = 'audit:purge
                            {--days= : Retention period in days (default from config)}
                            {--tenant= : Purge only for specific tenant}
                            {--dry-run : Show count without deleting}';

    protected $description = 'Purge old audit trail entries beyond retention period';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('audit.retention_days', 365));
        $cutoff = now()->subDays($days);
        $tenantId = $this->option('tenant');

        $query = ActivityLog::where('created_at', '<', $cutoff);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info("No audit logs older than {$days} days found.");
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("[Dry run] Would purge {$count} audit log entries older than {$days} days (before {$cutoff->toDateString()}).");
            return self::SUCCESS;
        }

        if (!$this->option('no-interaction') && !$this->confirm("Delete {$count} audit log entries older than {$days} days?")) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        $chunkQuery = ActivityLog::where('created_at', '<', $cutoff);
        if ($tenantId) {
            $chunkQuery->where('tenant_id', $tenantId);
        }

        do {
            $batch = $chunkQuery->limit(1000)->delete();
            $deleted += $batch;
            if ($batch > 0) {
                $this->output->write(".");
            }
        } while ($batch > 0);

        $this->newLine();
        $this->info("Purged {$deleted} audit log entries older than {$days} days.");

        return self::SUCCESS;
    }
}
