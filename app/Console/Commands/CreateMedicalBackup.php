<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Emr;
use App\Models\LabResult;
use App\Models\MedicalBill;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreateMedicalBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:backup:medical-records
                            {--tenant= : Specific tenant ID}
                            {--full : Full backup including all data}
                            {--incremental : Incremental backup (changes only)}
                            {--compress : Compress backup file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create automated backup of medical records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $full = $this->option('full');
        $incremental = $this->option('incremental');
        $compress = $this->option('compress') || ! $incremental;

        $this->info('💾 Creating medical records backup...');
        $this->info('Mode: '.($full ? 'FULL' : ($incremental ? 'INCREMENTAL' : 'STANDARD')));

        $tenants = $tenantId
            ? [Tenant::find($tenantId)]
            : Tenant::where('is_active', true)->get();

        $backupCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $backupPath = $this->createTenantBackup($tenant, $full, $incremental, $compress);
                $backupCount++;

                $this->info("✓ Backup created: {$backupPath}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to backup tenant {$tenant->id}: {$e->getMessage()}");

                Log::error('Medical backup failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\n✅ Successfully backed up {$backupCount} tenant(s)");

        // Cleanup old backups
        $this->cleanupOldBackups();

        return Command::SUCCESS;
    }

    /**
     * Create backup for a tenant
     */
    protected function createTenantBackup($tenant, bool $full, bool $incremental, bool $compress): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupName = "medical_backup_tenant_{$tenant->id}_{$timestamp}";
        $backupDir = "backups/medical/{$tenant->id}";

        $this->info("\nBacking up tenant: {$tenant->name}");

        // Data to backup
        $backupData = [
            'metadata' => [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'backup_date' => now()->toDateTimeString(),
                'backup_type' => $full ? 'full' : ($incremental ? 'incremental' : 'standard'),
                'version' => '1.0',
            ],
            'patients' => $this->backupPatients($tenant->id, $incremental),
            'emr_records' => $this->backupEMR($tenant->id, $incremental),
            'appointments' => $this->backupAppointments($tenant->id, $incremental),
            'prescriptions' => $this->backupPrescriptions($tenant->id, $incremental),
            'lab_results' => $this->backupLabResults($tenant->id, $incremental),
            'billing' => $this->backupBilling($tenant->id, $incremental),
        ];

        // Save backup as JSON
        $backupJson = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($compress) {
            $fileName = "{$backupName}.json.gz";
            $compressed = gzencode($backupJson, 9);
            Storage::disk('local')->put("{$backupDir}/{$fileName}", $compressed);
        } else {
            $fileName = "{$backupName}.json";
            Storage::disk('local')->put("{$backupDir}/{$fileName}", $backupJson);
        }

        $fileSize = Storage::disk('local')->size("{$backupDir}/{$fileName}");
        $this->line('  Records: '.array_sum(array_map('count', array_filter($backupData, 'is_array'))));
        $this->line('  Size: '.number_format($fileSize / 1024, 2).' KB');

        return "{$backupDir}/{$fileName}";
    }

    /**
     * Backup patients data
     */
    protected function backupPatients(int $tenantId, bool $incremental): array
    {
        $query = Patient::where('tenant_id', $tenantId);

        if ($incremental) {
            $query->where('updated_at', '>=', now()->subDay());
        }

        return $query->get()->toArray();
    }

    /**
     * Backup EMR records
     */
    protected function backupEMR(int $tenantId, bool $incremental): array
    {
        $query = Emr::where('tenant_id', $tenantId);

        if ($incremental) {
            $query->where('updated_at', '>=', now()->subDay());
        }

        return $query->with(['diagnoses', 'prescriptions'])->get()->toArray();
    }

    /**
     * Backup appointments
     */
    protected function backupAppointments(int $tenantId, bool $incremental): array
    {
        $query = Appointment::where('tenant_id', $tenantId);

        if ($incremental) {
            $query->where('updated_at', '>=', now()->subDay());
        }

        return $query->get()->toArray();
    }

    /**
     * Backup prescriptions
     */
    protected function backupPrescriptions(int $tenantId, bool $incremental): array
    {
        $query = Prescription::where('tenant_id', $tenantId);

        if ($incremental) {
            $query->where('updated_at', '>=', now()->subDay());
        }

        return $query->get()->toArray();
    }

    /**
     * Backup lab results
     */
    protected function backupLabResults(int $tenantId, bool $incremental): array
    {
        $query = LabResult::where('tenant_id', $tenantId);

        if ($incremental) {
            $query->where('updated_at', '>=', now()->subDay());
        }

        return $query->get()->toArray();
    }

    /**
     * Backup billing records
     */
    protected function backupBilling(int $tenantId, bool $incremental): array
    {
        $query = MedicalBill::where('tenant_id', $tenantId);

        if ($incremental) {
            $query->where('updated_at', '>=', now()->subDay());
        }

        return $query->with(['payments'])->get()->toArray();
    }

    /**
     * Cleanup old backups
     */
    protected function cleanupOldBackups(): void
    {
        $retentionDays = config('healthcare.data_retention.backup_days', 365);
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("\n🧹 Cleaning up backups older than {$retentionDays} days...");

        $files = Storage::disk('local')->allFiles('backups/medical');
        $deletedCount = 0;

        foreach ($files as $file) {
            $lastModified = Storage::disk('local')->lastModified($file);
            $fileDate = now()->setTimestamp($lastModified);

            if ($fileDate < $cutoffDate) {
                Storage::disk('local')->delete($file);
                $deletedCount++;
            }
        }

        $this->line("   Deleted: {$deletedCount} old backup(s)");
    }
}
