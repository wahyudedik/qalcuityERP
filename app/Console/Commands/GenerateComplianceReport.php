<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Emr;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateComplianceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:compliance:generate-report
                            {--tenant= : Specific tenant ID}
                            {--month= : Month (YYYY-MM), defaults to last month}
                            {--format=pdf : Output format (pdf, excel, json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly healthcare compliance report';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $month = $this->option('month') ? now()->parse($this->option('month')) : now()->subMonth();
        $format = $this->option('format');

        $this->info("📋 Generating compliance report for {$month->format('F Y')}...");

        $tenants = $tenantId
            ? [Tenant::find($tenantId)]
            : Tenant::where('is_active', true)->get();

        $generatedCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $reportPath = $this->generateTenantReport($tenant, $month, $format);
                $generatedCount++;

                $this->info("✓ Report generated: {$reportPath}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to generate report for tenant {$tenant->id}: {$e->getMessage()}");

                Log::error('Compliance report generation failed', [
                    'tenant_id' => $tenant->id,
                    'month' => $month->format('Y-m'),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\n✅ Successfully generated {$generatedCount} compliance report(s)");

        return Command::SUCCESS;
    }

    /**
     * Generate compliance report for a tenant
     */
    protected function generateTenantReport($tenant, $month, string $format): string
    {
        $this->info("\nGenerating report for: {$tenant->name}");

        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        // Collect compliance data
        $reportData = [
            'report_metadata' => [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'report_period' => $month->format('F Y'),
                'generated_at' => now()->toDateTimeString(),
                'report_type' => 'Monthly Compliance Report',
            ],
            'access_audit' => $this->getAccessAuditStats($tenant->id, $startDate, $endDate),
            'data_privacy' => $this->getDataPrivacyStats($tenant->id, $startDate, $endDate),
            'security_incidents' => $this->getSecurityIncidents($tenant->id, $startDate, $endDate),
            'user_access_review' => $this->getUserAccessReview($tenant->id),
            'data_retention' => $this->getDataRetentionStats($tenant->id),
            'backup_status' => $this->getBackupStatus($tenant->id, $month),
            'training_compliance' => $this->getTrainingCompliance($tenant->id),
        ];

        // Generate report in specified format
        return match ($format) {
            'pdf' => $this->generatePDFReport($tenant, $month, $reportData),
            'excel' => $this->generateExcelReport($tenant, $month, $reportData),
            'json' => $this->generateJSONReport($tenant, $month, $reportData),
            default => $this->generateJSONReport($tenant, $month, $reportData),
        };
    }

    /**
     * Get access audit statistics
     */
    protected function getAccessAuditStats(int $tenantId, $startDate, $endDate): array
    {
        $totalAccess = AuditLog::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $afterHoursAccess = AuditLog::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereJsonContains('metadata->is_after_hours', true)
            ->count();

        return [
            'total_access_events' => $totalAccess,
            'after_hours_access_count' => $afterHoursAccess,
            'after_hours_percentage' => $totalAccess > 0 ? round($afterHoursAccess / $totalAccess * 100, 2) : 0,
        ];
    }

    /**
     * Get data privacy statistics
     */
    protected function getDataPrivacyStats(int $tenantId, $startDate, $endDate): array
    {
        return [
            'patient_data_exports' => 0,
            'patient_data_deletions' => 0,
            'consent_updates' => 0,
        ];
    }

    /**
     * Get security incidents
     */
    protected function getSecurityIncidents(int $tenantId, $startDate, $endDate): array
    {
        return [
            'total_incidents' => 0,
            'critical_incidents' => 0,
            'resolved_incidents' => 0,
        ];
    }

    /**
     * Get user access review
     */
    protected function getUserAccessReview(int $tenantId): array
    {
        $activeUsers = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        return [
            'total_active_users' => $activeUsers,
            'users_never_logged_in' => 0,
            'inactive_users_30_days' => 0,
        ];
    }

    /**
     * Get data retention statistics
     */
    protected function getDataRetentionStats(int $tenantId): array
    {
        return [
            'medical_records_count' => Emr::where('tenant_id', $tenantId)->count(),
            'oldest_record_date' => Emr::where('tenant_id', $tenantId)
                ->orderBy('created_at')
                ->value('created_at'),
        ];
    }

    /**
     * Get backup status
     */
    protected function getBackupStatus(int $tenantId, $month): array
    {
        $backupDir = "backups/medical/{$tenantId}";
        $backupCount = count(Storage::disk('local')->files($backupDir));

        return [
            'total_backups' => $backupCount,
            'last_backup_date' => null,
            'backup_compliance' => $backupCount >= 30 ? 'compliant' : 'non-compliant',
        ];
    }

    /**
     * Get training compliance
     */
    protected function getTrainingCompliance(int $tenantId): array
    {
        return [
            'staff_trained_hipaa' => 0,
            'training_completion_rate' => 0,
        ];
    }

    /**
     * Generate PDF report
     */
    protected function generatePDFReport($tenant, $month, array $reportData): string
    {
        // Use DomPDF or similar
        $fileName = "compliance_report_{$tenant->id}_{$month->format('Y_m')}.pdf";
        $filePath = "reports/compliance/{$fileName}";

        // Placeholder - implement PDF generation
        Storage::disk('local')->put(
            $filePath,
            json_encode($reportData, JSON_PRETTY_PRINT)
        );

        return $filePath;
    }

    /**
     * Generate Excel report
     */
    protected function generateExcelReport($tenant, $month, array $reportData): string
    {
        $fileName = "compliance_report_{$tenant->id}_{$month->format('Y_m')}.xlsx";
        $filePath = "reports/compliance/{$fileName}";

        // Placeholder - implement Excel generation using Laravel Excel
        Storage::disk('local')->put(
            $filePath,
            json_encode($reportData, JSON_PRETTY_PRINT)
        );

        return $filePath;
    }

    /**
     * Generate JSON report
     */
    protected function generateJSONReport($tenant, $month, array $reportData): string
    {
        $fileName = "compliance_report_{$tenant->id}_{$month->format('Y_m')}.json";
        $filePath = "reports/compliance/{$fileName}";

        Storage::disk('local')->put(
            $filePath,
            json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $filePath;
    }
}
