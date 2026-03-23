<?php

namespace App\Console\Commands;

use App\Models\PeriodBackup;
use App\Models\Tenant;
use App\Services\PeriodBackupService;
use Illuminate\Console\Command;

class BackupPeriodData extends Command
{
    protected $signature = 'backup:period
                            {--type=monthly : Tipe backup: monthly atau yearly}
                            {--tenant= : ID tenant tertentu (kosong = semua tenant aktif)}';

    protected $description = 'Buat backup data transaksional per periode untuk semua tenant aktif';

    public function handle(PeriodBackupService $service): int
    {
        $type   = $this->option('type');
        $tid    = $this->option('tenant');

        $query = Tenant::where('is_active', true);
        if ($tid) $query->where('id', $tid);
        $tenants = $query->get();

        if ($type === 'monthly') {
            $label       = now()->subMonth()->translatedFormat('F Y');
            $periodStart = now()->subMonth()->startOfMonth()->toDateString();
            $periodEnd   = now()->subMonth()->endOfMonth()->toDateString();
        } else {
            // yearly — tahun lalu
            $label       = 'Tahun ' . now()->subYear()->year;
            $periodStart = now()->subYear()->startOfYear()->toDateString();
            $periodEnd   = now()->subYear()->endOfYear()->toDateString();
        }

        $this->info("Memulai backup {$type}: {$label} untuk {$tenants->count()} tenant...");
        $success = 0;
        $failed  = 0;

        foreach ($tenants as $tenant) {
            // Skip jika sudah ada backup untuk periode ini
            $exists = PeriodBackup::where('tenant_id', $tenant->id)
                ->where('type', $type)
                ->where('period_start', $periodStart)
                ->where('status', 'completed')
                ->exists();

            if ($exists) {
                $this->line("  [skip] Tenant #{$tenant->id} {$tenant->name} — sudah ada backup.");
                continue;
            }

            $backup = PeriodBackup::create([
                'tenant_id'    => $tenant->id,
                'type'         => $type,
                'label'        => $label,
                'period_start' => $periodStart,
                'period_end'   => $periodEnd,
                'status'       => 'pending',
                'created_by'   => 1, // system user
            ]);

            try {
                $service->generate($backup);
                $this->info("  [ok] Tenant #{$tenant->id} {$tenant->name}");
                $success++;
            } catch (\Throwable $e) {
                $this->error("  [fail] Tenant #{$tenant->id} {$tenant->name}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Selesai. Berhasil: {$success}, Gagal: {$failed}");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
