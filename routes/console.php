<?php

use App\Jobs\AnalyzeUserPatterns;
use App\Jobs\CalculatePriceElasticity;
use App\Jobs\CheckAiCostThresholds;
use App\Jobs\CheckAiFallbackRates;
use App\Jobs\CheckTrialExpiry;
use App\Jobs\ExpireLoyaltyPoints;
use App\Jobs\GenerateAiAdvisorRecommendations;
use App\Jobs\GenerateAiInsights;
use App\Jobs\GenerateProactiveInsightsJob;
use App\Jobs\GenerateTenantReport;
use App\Jobs\Integrations\RetryWebhookDeliveriesJob;
use App\Jobs\Integrations\SyncInventoryJob;
use App\Jobs\Integrations\SyncOrdersJob;
use App\Jobs\Integrations\SyncProductsJob;
use App\Jobs\ProcessRecurringJournals;
use App\Jobs\RetryFailedMarketplaceSyncs;
use App\Jobs\RunAssetDepreciation;
use App\Jobs\SendAiDigest;
use App\Jobs\SendErpNotificationBatch;
use App\Jobs\SyncEcommerceOrders;
use App\Jobs\SyncMarketplacePrices;
use App\Jobs\SyncMarketplaceStock;
use App\Jobs\Telecom\CheckQuotaExpiryJob;
use App\Jobs\Telecom\PollRouterUsageJob;
use App\Jobs\Telecom\SyncHotspotUsersJob;
use App\Jobs\UpdateCurrencyRates;
use App\Models\ChatSession;
use App\Models\Integration;
use App\Models\Tenant;
use App\Models\Workflow;
use App\Services\Integrations\WebhookDeliveryService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── AI Insights ─────────────────────────────────────────────────────────────

// Generate insight harian untuk semua tenant — setiap hari jam 07:00
// (GenerateAiInsights job sudah dispatch SendAiDigest secara internal jika ada insight kritis)
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        GenerateAiInsights::dispatch($tenant->id, 'daily')
            ->delay(now()->addSeconds(rand(1, 60)));
    });
})->dailyAt('07:00')->name('generate-ai-insights-daily')->withoutOverlapping();

// Generate insight mingguan — Senin jam 08:00
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        GenerateAiInsights::dispatch($tenant->id, 'weekly')
            ->delay(now()->addSeconds(rand(1, 60)));
    });
})->weeklyOn(1, '08:00')->name('generate-ai-insights-weekly')->withoutOverlapping();

// AI Financial Advisor — rekomendasi strategis mingguan — Senin jam 09:00
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        GenerateAiAdvisorRecommendations::dispatch($tenant->id, 'weekly')
            ->delay(now()->addSeconds(rand(1, 120)));
    });
})->weeklyOn(1, '09:00')->name('ai-advisor-weekly')->withoutOverlapping();

// AI Digest harian — jam 08:00 (job SendAiDigest mengelola filter frekuensi per user)
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        SendAiDigest::dispatch($tenant->id, 'daily')
            ->delay(now()->addSeconds(rand(1, 30)));
    });
})->dailyAt('08:00')->name('send-ai-digest-daily')->withoutOverlapping();

// ─── AI Use-Case Routing Monitoring ──────────────────────────────────────────

// Cek threshold biaya AI per tenant — setiap hari jam 09:00
Schedule::job(new CheckAiCostThresholds)
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('check-ai-cost-thresholds');

// Cek persentase fallback event per use case — setiap jam
Schedule::job(new CheckAiFallbackRates)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('check-ai-fallback-rates');

// ─── Proactive Insights (ERP AI Agent) ───────────────────────────────────────

// Generate proactive insights untuk semua tenant aktif — setiap 6 jam
// Job memanggil ProactiveInsightEngine::analyze() per tenant dan mengirim
// push notification untuk insight high/critical jika push aktif.
Schedule::job(new GenerateProactiveInsightsJob)
    ->everySixHours()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('generate-proactive-insights');

// ─── ERP Notifications ────────────────────────────────────────────────────────

// Cek stok menipis setiap hari jam 08:00 & 13:00
Schedule::command('erp:check-notifications')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('erp:check-notifications')
    ->dailyAt('13:00')
    ->withoutOverlapping()
    ->onOneServer();

// Cek laporan mingguan belum masuk — Senin jam 09:00
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        SendErpNotificationBatch::dispatch($tenant->id, 'missing_reports', 'weekly');
    });
})->weeklyOn(1, '09:00')->name('check-weekly-reports')->withoutOverlapping();

// Cek invoice overdue — setiap hari jam 09:00
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        SendErpNotificationBatch::dispatch($tenant->id, 'invoice_overdue');
    });
})->dailyAt('09:00')->name('check-invoice-overdue')->withoutOverlapping();

// Cek asset maintenance due — setiap hari jam 08:30
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        SendErpNotificationBatch::dispatch($tenant->id, 'asset_maintenance_due');
    });
})->dailyAt('08:30')->name('check-asset-maintenance')->withoutOverlapping();

// Cek budget exceeded — setiap hari jam 10:00
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        SendErpNotificationBatch::dispatch($tenant->id, 'budget_exceeded');
    });
})->dailyAt('10:00')->name('check-budget-exceeded')->withoutOverlapping();

// Cek expiry produk — setiap hari jam 07:30 (sebelum jam kerja)
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        SendErpNotificationBatch::dispatch($tenant->id, 'product_expiry');
    });
})->dailyAt('07:30')->name('check-product-expiry')->withoutOverlapping();

// ─── Notification Escalations ───────────────────────────────────────────────

// Process notification escalations — setiap 15 menit
Schedule::command('notifications:process-escalations')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// ─── Notification Digest Emails ─────────────────────────────────────────────

// Send daily notification digest — setiap hari jam 07:00
Schedule::command('notifications:send-digest --daily')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->onOneServer();

// Send weekly notification digest — setiap Senin jam 08:00
Schedule::command('notifications:send-digest --weekly')
    ->weeklyOn(1, '08:00')
    ->withoutOverlapping()
    ->onOneServer();

// ─── Trial & Plan Expiry ──────────────────────────────────────────────────────

// Cek trial/plan akan berakhir — setiap hari jam 07:00
Schedule::job(new CheckTrialExpiry)
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->onOneServer();

// ─── Invoice Overdue — ditangani oleh SendErpNotificationBatch (lihat bagian ERP Notifications) ──

// ─── Asset Depreciation ───────────────────────────────────────────────────────

// Depresiasi aset bulanan — tanggal 1 setiap bulan jam 00:30
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        RunAssetDepreciation::dispatch($tenant->id)
            ->delay(now()->addSeconds(rand(1, 60)));
    });
})->monthlyOn(1, '00:30')->name('run-asset-depreciation')->withoutOverlapping();

// ─── Currency Rates ───────────────────────────────────────────────────────────

// Update kurs mata uang — setiap hari jam 06:00
Schedule::job(new UpdateCurrencyRates)
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onOneServer();

// ─── Loyalty Points Expiry ────────────────────────────────────────────────────

// Expire poin loyalitas yang kadaluarsa — setiap hari jam 01:00
Schedule::job(new ExpireLoyaltyPoints)
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// ─── E-Commerce Sync ─────────────────────────────────────────────────────────

// Sinkronisasi order e-commerce — setiap 30 menit
Schedule::job(new SyncEcommerceOrders)
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Sinkronisasi stok ke marketplace — setiap jam
Schedule::job(new SyncMarketplaceStock)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Sinkronisasi harga ke marketplace — setiap 6 jam
Schedule::job(new SyncMarketplacePrices)
    ->everySixHours()
    ->withoutOverlapping()
    ->onOneServer();

// Retry sync marketplace yang gagal — setiap 5 menit
Schedule::job(new RetryFailedMarketplaceSyncs)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Hitung elastisitas harga — setiap hari jam 04:00
Schedule::job(new CalculatePriceElasticity)
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer();

// ─── Monthly Reports ──────────────────────────────────────────────────────────

// Generate laporan bulanan untuk semua tenant — tanggal 1 setiap bulan jam 01:00
Schedule::call(function () {
    $period = now()->subMonth()->format('Y-m'); // laporan bulan lalu
    Tenant::where('is_active', true)->each(function ($tenant) use ($period) {
        GenerateTenantReport::dispatch($tenant->id, 'monthly_summary', $period)
            ->delay(now()->addSeconds(rand(1, 30))); // spread load
    });
})->monthlyOn(1, '01:00')->name('generate-monthly-reports')->withoutOverlapping();

// ─── Recurring Journals ───────────────────────────────────────────────────────

// Proses jurnal berulang — setiap hari jam 00:05
Schedule::job(new ProcessRecurringJournals)
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->onOneServer();

// ─── Reminders ───────────────────────────────────────────────────────────────

// Proses reminder yang jatuh tempo — setiap 5 menit
Schedule::command('reminders:process')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// ─── Cleanup ──────────────────────────────────────────────────────────────────

// Prune AI model switch logs beyond retention period — setiap hari jam 02:00
Schedule::command('ai:prune-switch-logs')
    ->daily()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('ai-prune-switch-logs-daily');

// Clean up expired API tokens — setiap hari jam 02:30 untuk keamanan
Schedule::command('api:cleanup-tokens --older-than=30')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/api-token-cleanup.log'))
    ->name('api-cleanup-tokens-daily');

// Hapus chat sessions tidak aktif lebih dari 90 hari — setiap minggu Minggu jam 02:00
Schedule::call(function () {
    $deleted = ChatSession::where('is_active', false)
        ->where('updated_at', '<', now()->subDays(90))
        ->delete();
    Log::info("Cleanup: deleted {$deleted} old chat sessions.");
})->weeklyOn(0, '02:00')->name('cleanup-old-sessions');

// Hapus failed jobs lebih dari 7 hari — setiap hari jam 03:00
Schedule::call(function () {
    DB::table('failed_jobs')
        ->where('failed_at', '<', now()->subDays(7))
        ->delete();
})->dailyAt('03:00')->name('cleanup-failed-jobs');

// Prune job batches & telescope (jika ada) — setiap hari
Schedule::command('queue:prune-failed --hours=168')->dailyAt('03:30');

// ─── Period Backup ────────────────────────────────────────────────────────────

// Backup data bulanan — tanggal 2 setiap bulan jam 02:00 (setelah bulan tutup)
Schedule::command('backup:period --type=monthly')
    ->monthlyOn(2, '02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('backup-period-monthly');

// Backup data tahunan — 2 Januari jam 03:00 (setelah tahun tutup)
Schedule::command('backup:period --type=yearly')
    ->yearlyOn(1, 2, '03:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('backup-period-yearly');

// ─── Deferred Amortization (Task 47) ─────────────────────────────────────────

// Post jurnal amortisasi deferred revenue & prepaid expense — setiap hari jam 00:10
Schedule::command('deferred:amortize')
    ->dailyAt('00:10')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('deferred-amortize-daily');

// ─── Anomaly Detection (Task 51) ─────────────────────────────────────────────

// Deteksi anomali otomatis — setiap hari jam 06:30
Schedule::command('anomalies:detect')
    ->dailyAt('06:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('detect-anomalies-daily');

// ─── Audit Trail Retention ───────────────────────────────────────────────

// Purge old audit logs beyond retention period — setiap hari jam 02:00
Schedule::command('audit:purge --no-interaction')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/audit-purge.log'))
    ->name('audit-purge-daily');

//  AI User Pattern Analysis

// Analisis pola perilaku user dari data transaksi  setiap hari jam 03:00
Schedule::job(new AnalyzeUserPatterns)
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('analyze-user-patterns');

// ─── Telecom Module ──────────────────────────────────────────────────────

// Poll router usage data — setiap 10 menit
Schedule::job(new PollRouterUsageJob)
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('telecom-poll-router-usage');

// Sync hotspot users online status — setiap jam
Schedule::job(new SyncHotspotUsersJob)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('telecom-sync-hotspot-users');

// Check quota expiry & reset quotas — setiap hari jam 00:30
Schedule::job(new CheckQuotaExpiryJob)
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('telecom-check-quota-expiry');

// Check geofence violations — setiap 5 menit
Schedule::command('geofencing:check')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/telecom/geofencing-check.log'))
    ->name('telecom-geofencing-check');

// ─── Automation & Workflow Builder ──────────────────────────────────────

// Process scheduled workflows — every minute
Schedule::command('workflows:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('workflows-process-scheduled');

// Invoice overdue check — daily at 9 AM (triggers workflow)
Schedule::call(function () {
    Workflow::where('trigger_type', 'schedule')
        ->where('is_active', true)
        ->whereJsonContains('trigger_config->schedule', 'invoice_overdue_check')
        ->each(function ($workflow) {
            $workflow->execute(['triggered_by' => 'schedule:invoice_overdue_check']);
        });
})->dailyAt('09:00')->name('workflow-invoice-overdue-check')->withoutOverlapping();

// Monthly bonus calculation — 1st of month at midnight
Schedule::call(function () {
    Workflow::where('trigger_type', 'schedule')
        ->where('is_active', true)
        ->whereJsonContains('trigger_config->schedule', 'monthly_bonus_calculation')
        ->each(function ($workflow) {
            $workflow->execute(['triggered_by' => 'schedule:monthly_bonus_calculation']);
        });
})->monthlyOn(1, '00:00')->name('workflow-monthly-bonus')->withoutOverlapping();

// ─── ERROR HANDLING & RECOVERY ──────────────────────────────────────────────

// Daily backup — setiap hari jam 02:00
Schedule::command('backup:create --type=daily')
    ->dailyAt('02:00')
    ->name('daily-backup')
    ->withoutOverlapping();

// Weekly backup — Minggu jam 03:00
Schedule::command('backup:create --type=weekly')
    ->weeklyOn(0, '03:00')
    ->name('weekly-backup')
    ->withoutOverlapping();

// Monthly backup — 1st of month jam 04:00
Schedule::command('backup:create --type=monthly')
    ->monthlyOn(1, '04:00')
    ->name('monthly-backup')
    ->withoutOverlapping();

// Cleanup old data — setiap hari jam 05:00
Schedule::command('cleanup:old-data')
    ->dailyAt('05:00')
    ->name('cleanup-old-data')
    ->withoutOverlapping();

// ─── SCHEDULED REPORTS ──────────────────────────────────────────────────────

// Process scheduled reports — setiap jam
Schedule::command('reports:process-scheduled')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('process-scheduled-reports');

// ─── INTEGRATION MARKETPLACE ─────────────────────────────────────────────

// Retry failed webhook deliveries — setiap 5 menit
Schedule::job(new RetryWebhookDeliveriesJob)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('integration-retry-webhooks');

// Auto-sync integrations based on frequency — setiap jam
Schedule::call(function () {
    Integration::where('status', 'active')
        ->where('sync_frequency', 'hourly')
        ->where(function ($query) {
            $query->whereNull('next_sync_at')
                ->orWhere('next_sync_at', '<=', now());
        })
        ->each(function ($integration) {
            SyncProductsJob::dispatch($integration);
            SyncOrdersJob::dispatch($integration);
            SyncInventoryJob::dispatch($integration);
        });
})->hourly()->name('integration-auto-sync-hourly')->withoutOverlapping();

// Daily sync for integrations — setiap hari jam 01:00
Schedule::call(function () {
    Integration::where('status', 'active')
        ->where('sync_frequency', 'daily')
        ->where(function ($query) {
            $query->whereNull('next_sync_at')
                ->orWhere('next_sync_at', '<=', now());
        })
        ->each(function ($integration) {
            SyncProductsJob::dispatch($integration);
        });
})->dailyAt('01:00')->name('integration-auto-sync-daily')->withoutOverlapping();

// Cleanup old webhook deliveries — setiap hari jam 03:00
Schedule::call(function () {
    $service = new WebhookDeliveryService;
    $deleted = $service->cleanupOldDeliveries(30);
    Log::info("Integration cleanup: deleted {$deleted} old webhook deliveries");
})->dailyAt('03:00')->name('integration-cleanup-webhooks')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────────────────────
// HEALTHCARE MODULE SCHEDULED TASKS
// ─────────────────────────────────────────────────────────────────────────────

// Record daily analytics — setiap hari jam 23:50 (sebelum tengah malam)
Schedule::command('healthcare:analytics:daily')
    ->dailyAt('23:50')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/healthcare/daily-analytics.log'))
    ->name('healthcare-daily-analytics');

// Send appointment reminders — setiap jam pada jam kerja (08:00 - 17:00)
Schedule::command('healthcare:reminders:appointments --channel=all')
    ->hourly()
    ->weekdays()
    ->between('08:00', '17:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('healthcare-appointment-reminders');

// Check expiring medical supplies — setiap hari jam 07:00
Schedule::command('healthcare:supplies:check-expiry --days=30 --notify')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/healthcare/supply-expiry.log'))
    ->name('healthcare-check-expiring-supplies');

// Poll lab equipment — setiap 30 menit
Schedule::command('healthcare:lab:poll-equipment')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('healthcare-poll-lab-equipment');

// Check BPJS claim status — setiap hari jam 10:00
Schedule::command('healthcare:bpjs:check-claims --limit=100')
    ->dailyAt('10:00')
    ->weekdays()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/healthcare/bpjs-claims.log'))
    ->name('healthcare-check-bpjs-claims');

// Create medical records backup — setiap hari jam 01:00
Schedule::command('healthcare:backup:medical-records --compress')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/healthcare/medical-backup.log'))
    ->name('healthcare-medical-backup');

// Generate monthly compliance report — tanggal 1 setiap bulan jam 02:00
Schedule::command('healthcare:compliance:generate-report --format=json')
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('healthcare-compliance-report');

// Cleanup old audit logs — setiap minggu Minggu jam 03:00
Schedule::command('healthcare:cleanup:audit-logs --archive --force')
    ->weeklyOn(0, '03:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/healthcare/audit-cleanup.log'))
    ->name('healthcare-cleanup-audit-logs');
