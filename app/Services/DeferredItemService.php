<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\DeferredItem;
use App\Models\DeferredItemSchedule;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeferredItemService
{
    /**
     * Buat deferred item + generate amortization schedule otomatis.
     */
    public function create(array $data, int $tenantId, int $userId): DeferredItem
    {
        return DB::transaction(function () use ($data, $tenantId, $userId) {
            $startDate = Carbon::parse($data['start_date'])->startOfMonth();
            $endDate = Carbon::parse($data['end_date'])->endOfMonth();

            // Hitung jumlah bulan
            $totalPeriods = (int) $startDate->diffInMonths($endDate) + 1;

            $item = DeferredItem::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'type' => $data['type'],
                'number' => $this->generateNumber($tenantId, $data['type']),
                'description' => $data['description'],
                'total_amount' => $data['total_amount'],
                'recognized_amount' => 0,
                'remaining_amount' => $data['total_amount'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_periods' => $totalPeriods,
                'recognized_periods' => 0,
                'status' => 'active',
                'deferred_account_id' => $data['deferred_account_id'],
                'recognition_account_id' => $data['recognition_account_id'],
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
            ]);

            $this->generateSchedule($item);

            return $item;
        });
    }

    /**
     * Generate amortization schedule (straight-line per bulan).
     * Sisa amount dialokasikan ke periode terakhir untuk menghindari rounding error.
     */
    public function generateSchedule(DeferredItem $item): void
    {
        $item->schedules()->delete();

        $total = (float) $item->total_amount;
        $periods = $item->total_periods;
        $perPeriod = round($total / $periods, 2);
        $allocated = 0;

        $date = Carbon::parse($item->start_date)->startOfMonth();

        for ($i = 1; $i <= $periods; $i++) {
            $isLast = ($i === $periods);
            $amount = $isLast ? round($total - $allocated, 2) : $perPeriod;
            $allocated += $amount;

            DeferredItemSchedule::create([
                'deferred_item_id' => $item->id,
                'period_number' => $i,
                'recognition_date' => $date->copy()->endOfMonth()->toDateString(),
                'amount' => $amount,
                'status' => 'pending',
            ]);

            $date->addMonth();
        }
    }

    /**
     * Post jurnal amortisasi untuk schedule yang jatuh tempo hari ini atau sebelumnya.
     * Dipanggil oleh scheduled command.
     */
    public function processAutoAmortization(int $tenantId, int $systemUserId): int
    {
        $posted = 0;

        $pendingSchedules = DeferredItemSchedule::whereHas(
            'deferredItem',
            fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'active')
        )
            ->where('status', 'pending')
            ->where('recognition_date', '<=', today()->toDateString())
            ->with('deferredItem')
            ->get();

        foreach ($pendingSchedules as $schedule) {
            if ($this->postSchedule($schedule, $systemUserId)) {
                $posted++;
            }
        }

        return $posted;
    }

    /**
     * Post jurnal untuk satu schedule secara manual.
     */
    public function postSchedule(DeferredItemSchedule $schedule, int $userId): bool
    {
        if ($schedule->isPosted()) {
            return false;
        }

        $item = $schedule->deferredItem;

        try {
            DB::transaction(function () use ($schedule, $item, $userId) {
                $date = $schedule->recognition_date->toDateString();
                $amount = (float) $schedule->amount;

                // BUG-FIN-002 FIX: Check period lock before creating journal
                $periodLockService = app(PeriodLockService::class);
                if ($periodLockService->isLocked($item->tenant_id, $date)) {
                    $lockInfo = $periodLockService->getLockInfo($item->tenant_id, $date);
                    throw new \RuntimeException(
                        "Periode {$lockInfo} sudah dikunci. Tidak dapat memposting amortisasi untuk tanggal {$date}."
                    );
                }

                // Deferred Revenue: Dr Deferred Revenue / Cr Revenue
                // Prepaid Expense:  Dr Expense / Cr Prepaid Expense
                if ($item->type === 'deferred_revenue') {
                    $debitAccountId = $item->deferred_account_id;
                    $creditAccountId = $item->recognition_account_id;
                    $desc = "Pengakuan pendapatan: {$item->description} (periode {$schedule->period_number}/{$item->total_periods})";
                } else {
                    $debitAccountId = $item->recognition_account_id;
                    $creditAccountId = $item->deferred_account_id;
                    $desc = "Amortisasi biaya dibayar di muka: {$item->description} (periode {$schedule->period_number}/{$item->total_periods})";
                }

                $period = AccountingPeriod::findForDate($item->tenant_id, $date);

                $je = JournalEntry::create([
                    'tenant_id' => $item->tenant_id,
                    'period_id' => $period?->id,
                    'user_id' => $userId,
                    'number' => JournalEntry::generateNumber($item->tenant_id, 'AUTO'),
                    'date' => $date,
                    'description' => $desc,
                    'reference' => $item->number,
                    'reference_type' => 'deferred_item',
                    'reference_id' => $item->id,
                    'currency_code' => 'IDR',
                    'currency_rate' => 1,
                    'status' => 'draft',
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $debitAccountId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => $desc,
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $creditAccountId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => $desc,
                ]);

                $je->post($userId);

                // Update schedule
                $schedule->update([
                    'status' => 'posted',
                    'journal_entry_id' => $je->id,
                ]);

                // Update deferred item totals
                $newRecognized = (float) $item->recognized_amount + $amount;
                $newRemaining = (float) $item->total_amount - $newRecognized;
                $newPeriods = $item->recognized_periods + 1;
                $newStatus = ($newPeriods >= $item->total_periods) ? 'completed' : 'active';

                $item->update([
                    'recognized_amount' => $newRecognized,
                    'remaining_amount' => $newRemaining,
                    'recognized_periods' => $newPeriods,
                    'status' => $newStatus,
                ]);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("DeferredItemService::postSchedule failed for schedule {$schedule->id}: ".$e->getMessage());

            return false;
        }
    }

    private function generateNumber(int $tenantId, string $type): string
    {
        $prefix = $type === 'deferred_revenue' ? 'DR' : 'PE';

        return app(DocumentNumberService::class)->generate($tenantId, 'deferred_'.$type, $prefix);
    }
}
