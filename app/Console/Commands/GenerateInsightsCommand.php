<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAiInsights;
use App\Models\Tenant;
use App\Services\AiInsightService;
use Illuminate\Console\Command;

class GenerateInsightsCommand extends Command
{
    protected $signature = 'erp:generate-insights
                            {--tenant= : ID tenant spesifik (kosong = semua tenant aktif)}
                            {--period=daily : Periode analisis (daily|weekly)}
                            {--sync : Jalankan sinkron tanpa queue}';

    protected $description = 'Generate AI insights untuk semua tenant atau tenant tertentu';

    public function handle(AiInsightService $service): int
    {
        $period   = $this->option('period');
        $tenantId = $this->option('tenant');
        $sync     = $this->option('sync');

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
                $this->line(" Tenant [{$tenant->id}] {$tenant->name}: " . count($insights) . ' insights');
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
