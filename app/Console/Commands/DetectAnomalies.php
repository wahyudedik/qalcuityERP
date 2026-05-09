<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\AnomalyDetectionService;
use Illuminate\Console\Command;

class DetectAnomalies extends Command
{
    protected $signature = 'anomalies:detect {--tenant= : ID tenant spesifik}';

    protected $description = 'Deteksi anomali otomatis untuk semua tenant aktif';

    public function handle(AnomalyDetectionService $service): int
    {
        $tenantId = $this->option('tenant');

        $query = Tenant::where('is_active', true);
        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();
        $total = 0;

        foreach ($tenants as $tenant) {
            try {
                $count = $service->detectAndSave($tenant->id);
                $total += $count;
                if ($count > 0) {
                    $this->line("Tenant #{$tenant->id} ({$tenant->name}): {$count} anomali baru.");
                }
            } catch (\Throwable $e) {
                $this->error("Tenant #{$tenant->id}: ".$e->getMessage());
            }
        }

        $this->info("Selesai. Total {$total} anomali baru dari {$tenants->count()} tenant.");

        return self::SUCCESS;
    }
}
