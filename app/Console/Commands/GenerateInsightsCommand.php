<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAiInsights;
use App\Models\Tenant;
use App\Services\AiInsightService;
use Illuminate\Console\Command;

/**
 * Artisan command untuk generate AI insights secara manual via CLI.
 *
 * Untuk scheduled/otomatis, gunakan GenerateAiInsights job yang sudah
 * terdaftar di routes/console.php (bukan command ini).
 *
 * Usage:
 *   php artisan erp:generate-insights                    # semua tenant, daily, via queue
 *   php artisan erp:generate-insights --sync             # sinkron, tanpa queue
 *   php artisan erp:generate-insights --tenant=5         # tenant tertentu
 *   php artisan erp:generate-insights --period=weekly    # periode mingguan
 */
class GenerateInsightsCommand extends Command
{
    protected $signature = 'erp:generate-insights
                            {--tenant= : ID tenant spesifik (kosong = semua tenant aktif)}
                            {--period=daily : Periode analisis (daily|weekly)}
                            {--sync : Jalankan sinkron tanpa queue}';

    protected $description = 'Generate AI insights untuk semua tenant atau tenant tertentu';

    public function handle(AiInsightService $service): int
    {
        $period = $this->option('period');
        $tenantId = $this->option('tenant');
        $sync = $this->option('sync');

        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->where('is_active', true)->get()
            : Tenant::where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant aktif ditemukan.');

            return self::SUCCESS;
        }

        $this->info("Generating AI insights ({$period}) untuk {$tenants->count()} tenant...");
        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            if ($sync) {
                $insights = $service->generateAndSave($tenant->id);
                $this->line(" Tenant [{$tenant->id}] {$tenant->name}: ".count($insights).' insights');
            } else {
                GenerateAiInsights::dispatch($tenant->id, $period);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $mode = $sync ? 'sinkron' : 'via queue';
        $this->info("Selesai! Insights di-generate {$mode}.");

        return self::SUCCESS;
    }
}
