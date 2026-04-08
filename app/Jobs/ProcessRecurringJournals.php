<?php

namespace App\Jobs;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\RecurringJournal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessRecurringJournals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = today();

        RecurringJournal::where('is_active', true)
            ->where('next_run_date', '<=', $today)
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->get()
            ->each(function (RecurringJournal $recurring) use ($today) {
                try {
                    DB::transaction(function () use ($recurring, $today) {
                        $date = $today->toDateString();

                        // BUG-FIN-002 FIX: Check period lock before auto-creating journal
                        $periodLockService = app(\App\Services\PeriodLockService::class);
                        if ($periodLockService->isLocked($recurring->tenant_id, $date)) {
                            $lockInfo = $periodLockService->getLockInfo($recurring->tenant_id, $date);
                            Log::warning(
                                "Recurring journal skipped: Periode {$lockInfo} sudah dikunci. " .
                                "RecurringJournal ID: {$recurring->id}, Date: {$date}"
                            );
                            // Skip this run, update next_run_date
                            $recurring->update([
                                'last_run_date' => $today,
                                'next_run_date' => $recurring->calculateNextRun(),
                            ]);
                            return;
                        }

                        $period = AccountingPeriod::findForDate($recurring->tenant_id, $date);

                        $journal = JournalEntry::create([
                            'tenant_id' => $recurring->tenant_id,
                            'period_id' => $period?->id,
                            'user_id' => $recurring->user_id,
                            'number' => JournalEntry::generateNumber($recurring->tenant_id, 'JRE'),
                            'date' => $today,
                            'description' => $recurring->name . ' (Otomatis)',
                            'currency_code' => 'IDR',
                            'currency_rate' => 1,
                            'status' => 'draft',
                            'is_recurring' => true,
                            'recurring_journal_id' => $recurring->id,
                        ]);

                        foreach ($recurring->lines as $line) {
                            $journal->lines()->create([
                                'account_id' => $line['account_id'],
                                'debit' => (float) ($line['debit'] ?? 0),
                                'credit' => (float) ($line['credit'] ?? 0),
                                'description' => $line['description'] ?? $recurring->name,
                            ]);
                        }

                        // Auto-post jika balance
                        if ($journal->isBalanced()) {
                            $journal->post($recurring->user_id);
                        }

                        $recurring->update([
                            'last_run_date' => $today,
                            'next_run_date' => $recurring->calculateNextRun(),
                        ]);
                    });
                } catch (\Throwable $e) {
                    Log::error("ProcessRecurringJournals failed for ID {$recurring->id}: " . $e->getMessage());
                }
            });
    }
}
