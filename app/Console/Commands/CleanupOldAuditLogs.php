<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupOldAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:cleanup:audit-logs
                            {--days=2555 : Archive logs older than this (default 7 years)}
                            {--tenant= : Specific tenant ID}
                            {--archive : Archive before deleting}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive and cleanup old audit logs for compliance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $tenantId = $this->option('tenant');
        $archive = $this->option('archive');
        $force = $this->option('force');

        $cutoffDate = now()->subDays($days);

        $this->info("🧹 Cleaning up audit logs older than {$days} days...");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

        if (! $force) {
            if (! $this->confirm('Do you want to proceed?', true)) {
                $this->info('Operation cancelled.');

                return Command::FAILURE;
            }
        }

        // Get old audit logs
        $query = AuditLog::where('created_at', '<', $cutoffDate);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $totalLogs = $query->count();

        if ($totalLogs === 0) {
            $this->info('✅ No audit logs to cleanup');

            return Command::SUCCESS;
        }

        $this->warn("Found {$totalLogs} old audit log(s)");

        if ($archive) {
            $this->info("\n📦 Archiving old logs...");
            $this->archiveAuditLogs($query, $cutoffDate);
        }

        // Delete old logs
        $this->info("\n🗑️ Deleting old logs...");

        $deletedCount = 0;
        $batchSize = 1000;

        while (true) {
            $ids = $query->limit($batchSize)->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted = AuditLog::whereIn('id', $ids)->delete();
            $deletedCount += $deleted;

            $this->line("   Deleted {$deleted} logs (Total: {$deletedCount})");
        }

        $this->info("\n✅ Successfully cleaned up {$deletedCount} audit log(s)");

        // Cleanup old log files
        $this->cleanupOldLogFiles($days);

        return Command::SUCCESS;
    }

    /**
     * Archive audit logs before deletion
     */
    protected function archiveAuditLogs($query, $cutoffDate): void
    {
        $archiveDir = 'archives/audit-logs';
        $archiveFile = "{$archiveDir}/audit_logs_archive_{$cutoffDate->format('Y_m_d')}.json";

        $logs = $query->get();

        if ($logs->isEmpty()) {
            return;
        }

        $archiveData = [
            'archived_at' => now()->toDateTimeString(),
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'total_records' => $logs->count(),
            'logs' => $logs->toArray(),
        ];

        $archiveJson = json_encode($archiveData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $compressed = gzencode($archiveJson, 9);

        Storage::disk('local')->put(
            "{$archiveFile}.gz",
            $compressed
        );

        $this->info("   ✓ Archived to: {$archiveFile}.gz");
    }

    /**
     * Cleanup old log files
     */
    protected function cleanupOldLogFiles(int $days): void
    {
        $this->info("\n🗂️ Cleaning up old log files...");

        $logFiles = [
            'logs/healthcare/audit.log',
            'logs/healthcare/security.log',
            'logs/healthcare/compliance.log',
        ];

        foreach ($logFiles as $logFile) {
            if (! Storage::disk('local')->exists($logFile)) {
                continue;
            }

            // Laravel daily logs create files like audit-2024-01-01.log
            $logDir = dirname($logFile);
            $logBase = basename($logFile, '.log');

            $pattern = str_replace('.log', '-*', $logFile);
            $files = Storage::disk('local')->files(dirname($pattern));

            $deletedCount = 0;
            $cutoffDate = now()->subDays($days);

            foreach ($files as $file) {
                if (! str_contains($file, $logBase)) {
                    continue;
                }

                // Extract date from filename
                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
                    $fileDate = now()->parse($matches[1]);

                    if ($fileDate < $cutoffDate) {
                        Storage::disk('local')->delete($file);
                        $deletedCount++;
                    }
                }
            }

            if ($deletedCount > 0) {
                $this->line("   ✓ Cleaned {$deletedCount} old file(s) from {$logFile}");
            }
        }
    }
}
