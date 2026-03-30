<?php

use App\Jobs\CheckTrialExpiry;
use App\Jobs\ProcessRecurringJournals;
use App\Jobs\ExpireLoyaltyPoints;
use App\Jobs\GenerateAiInsights;
use App\Jobs\GenerateTenantReport;
use App\Jobs\RunAssetDepreciation;
use App\Jobs\SendAiDigest;
use App\Jobs\SyncEcommerceOrders;
use App\Jobs\UpdateCurrencyRates;
use App\Models\AiUsageLog;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Tenant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
        \App\Jobs\GenerateAiAdvisorRecommendations::dispatch($tenant->id, 'weekly')
            ->delay(now()->addSeconds(rand(1, 120)));
    });
})->weeklyOn(1, '09:00')->name('ai-advisor-weekly')->withoutOverlapping();

// AI Digest mingguan — Jumat jam 17:00 (ringkasan akhir pekan)
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        SendAiDigest::dispatch($tenant->id, 'weekly')
            ->delay(now()->addSeconds(rand(1, 30)));
    });
})->weeklyOn(5, '17:00')->name('send-ai-digest-weekly')->withoutOverlapping();

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
        \App\Jobs\SendErpNotificationBatch::dispatch($tenant->id, 'missing_reports', 'weekly');
    });
})->weeklyOn(1, '09:00')->name('check-weekly-reports')->withoutOverlapping();

// Cek invoice overdue — setiap hari jam 09:00
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        \App\Jobs\SendErpNotificationBatch::dispatch($tenant->id, 'invoice_overdue');
    });
})->dailyAt('09:00')->name('check-invoice-overdue')->withoutOverlapping();

// Cek asset maintenance due — setiap hari jam 08:30
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        \App\Jobs\SendErpNotificationBatch::dispatch($tenant->id, 'asset_maintenance_due');
    });
})->dailyAt('08:30')->name('check-asset-maintenance')->withoutOverlapping();

// Cek budget exceeded — setiap hari jam 10:00
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        \App\Jobs\SendErpNotificationBatch::dispatch($tenant->id, 'budget_exceeded');
    });
})->dailyAt('10:00')->name('check-budget-exceeded')->withoutOverlapping();

// Cek expiry produk — setiap hari jam 07:30 (sebelum jam kerja)
Schedule::call(function () {
    Tenant::where('is_active', true)->each(function ($tenant) {
        \App\Jobs\SendErpNotificationBatch::dispatch($tenant->id, 'product_expiry');
    });
})->dailyAt('07:30')->name('check-product-expiry')->withoutOverlapping();

// ─── Trial & Plan Expiry ──────────────────────────────────────────────────────

// Cek trial/plan akan berakhir — setiap hari jam 07:00
Schedule::job(new CheckTrialExpiry())
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
Schedule::job(new UpdateCurrencyRates())
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onOneServer();

// ─── Loyalty Points Expiry ────────────────────────────────────────────────────

// Expire poin loyalitas yang kadaluarsa — setiap hari jam 01:00
Schedule::job(new ExpireLoyaltyPoints())
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// ─── E-Commerce Sync ─────────────────────────────────────────────────────────

// Sinkronisasi order e-commerce — setiap 30 menit
Schedule::job(new SyncEcommerceOrders())
    ->everyThirtyMinutes()
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
Schedule::job(new ProcessRecurringJournals())
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

// Hapus chat sessions tidak aktif lebih dari 90 hari — setiap minggu Minggu jam 02:00
Schedule::call(function () {
    $deleted = ChatSession::where('is_active', false)
        ->where('updated_at', '<', now()->subDays(90))
        ->delete();
    \Illuminate\Support\Facades\Log::info("Cleanup: deleted {$deleted} old chat sessions.");
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
