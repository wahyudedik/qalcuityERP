<?php

namespace App\Console\Commands;

use App\Services\AutomatedBackupService;
use App\Services\RestorePointService;
use App\Services\UndoRollbackService;
use Illuminate\Console\Command;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cleanup:old-data {--days=30 : Days threshold for cleanup}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup old backups, restore points, and action logs';

    protected $backupService;

    protected $undoService;

    protected $restorePointService;

    public function __construct(
        AutomatedBackupService $backupService,
        UndoRollbackService $undoService,
        RestorePointService $restorePointService
    ) {
        parent::__construct();
        $this->backupService = $backupService;
        $this->undoService = $undoService;
        $this->restorePointService = $restorePointService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of old data...');

        // Cleanup expired backups
        $deletedBackups = $this->backupService->cleanupOldBackups();
        $this->info("✓ Deleted {$deletedBackups} expired backups");

        // Cleanup expired action logs
        $deletedLogs = $this->undoService->cleanupExpiredLogs();
        $this->info("✓ Deleted {$deletedLogs} expired action logs");

        // Cleanup expired restore points
        $deletedPoints = $this->restorePointService->cleanupExpiredPoints();
        $this->info("✓ Deleted {$deletedPoints} expired restore points");

        $totalDeleted = $deletedBackups + $deletedLogs + $deletedPoints;
        $this->info("\nTotal items cleaned: {$totalDeleted}");

        return 0;
    }
}
