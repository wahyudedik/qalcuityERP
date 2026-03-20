<?php

use App\Jobs\CheckTrialExpiry;
use App\Jobs\GenerateTenantReport;
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

// ─── Trial & Plan Expiry ──────────────────────────────────────────────────────

// Cek trial/plan akan berakhir — setiap hari jam 07:00
Schedule::job(new CheckTrialExpiry())
    ->dailyAt('07:00')
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
