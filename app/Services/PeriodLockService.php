<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\FiscalYear;
use Illuminate\Validation\ValidationException;

/**
 * PeriodLockService
 *
 * Digunakan di semua controller transaksi untuk memastikan
 * data tidak bisa dibuat/diubah/dihapus jika periode sudah dikunci.
 *
 * Data yang dilindungi:
 * - Jurnal & GL entries
 * - Invoice & pembayaran
 * - Sales Order (completed/invoiced)
 * - Purchase Order (received)
 * - Payroll yang sudah diproses
 * - Inventory movements
 * - Piutang/hutang yang sudah settled
 */
class PeriodLockService
{
    /**
     * Lempar ValidationException jika tanggal berada di periode terkunci.
     * Panggil ini di awal store/update/destroy controller.
     */
    public function assertNotLocked(int $tenantId, string $date, string $context = 'transaksi'): void
    {
        if ($this->isLocked($tenantId, $date)) {
            $info = $this->getLockInfo($tenantId, $date);
            throw ValidationException::withMessages([
                'date' => "Periode {$info} sudah dikunci. {$context} tidak dapat dibuat, diubah, atau dihapus pada periode ini.",
            ]);
        }
    }

    /**
     * Return true jika tanggal berada di periode/fiscal year yang terkunci.
     */
    public function isLocked(int $tenantId, string $date): bool
    {
        return FiscalYear::isDateLocked($tenantId, $date);
    }

    /**
     * Deskripsi singkat mengapa terkunci (untuk pesan error).
     */
    public function getLockInfo(int $tenantId, string $date): string
    {
        $fy = FiscalYear::findForDate($tenantId, $date);
        if ($fy && $fy->isLocked()) {
            return "Tahun Fiskal {$fy->name}";
        }

        $period = AccountingPeriod::where('tenant_id', $tenantId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->whereIn('status', ['locked', 'closed'])
            ->first();

        if ($period) {
            $label = $period->isLocked() ? 'dikunci' : 'ditutup';
            return "Periode {$period->name} ({$label})";
        }

        return 'periode ini';
    }

    /**
     * Tutup semua accounting periods dalam fiscal year, lalu lock fiscal year.
     */
    public function lockFiscalYear(FiscalYear $fiscalYear, int $userId): void
    {
        // Tutup semua period yang masih open dalam FY ini
        AccountingPeriod::where('tenant_id', $fiscalYear->tenant_id)
            ->where('start_date', '>=', $fiscalYear->start_date)
            ->where('end_date', '<=', $fiscalYear->end_date)
            ->where('status', 'open')
            ->update([
                'status'    => 'locked',
                'locked_by' => $userId,
                'locked_at' => now(),
            ]);

        $fiscalYear->update([
            'status'    => 'locked',
            'locked_by' => $userId,
            'locked_at' => now(),
        ]);
    }

    /**
     * Tutup fiscal year (closed, masih bisa dibuka kembali oleh admin).
     */
    public function closeFiscalYear(FiscalYear $fiscalYear, int $userId): void
    {
        AccountingPeriod::where('tenant_id', $fiscalYear->tenant_id)
            ->where('start_date', '>=', $fiscalYear->start_date)
            ->where('end_date', '<=', $fiscalYear->end_date)
            ->where('status', 'open')
            ->update([
                'status'    => 'closed',
                'closed_by' => $userId,
                'closed_at' => now(),
            ]);

        $fiscalYear->update([
            'status'    => 'closed',
            'closed_by' => $userId,
            'closed_at' => now(),
        ]);
    }

    /**
     * Buka kembali fiscal year (hanya admin, hanya dari closed — bukan locked).
     */
    public function reopenFiscalYear(FiscalYear $fiscalYear): void
    {
        if ($fiscalYear->isLocked()) {
            throw new \RuntimeException('Tahun fiskal yang sudah dikunci (locked) tidak dapat dibuka kembali.');
        }

        $fiscalYear->update([
            'status'    => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Lock satu accounting period.
     */
    public function lockPeriod(AccountingPeriod $period, int $userId): void
    {
        $period->update([
            'status'    => 'locked',
            'locked_by' => $userId,
            'locked_at' => now(),
        ]);
    }

    /**
     * Auto-generate accounting periods bulanan untuk satu fiscal year.
     */
    public function generateMonthlyPeriods(FiscalYear $fiscalYear): int
    {
        $created = 0;
        $current = $fiscalYear->start_date->copy()->startOfMonth();
        $end     = $fiscalYear->end_date->copy()->endOfMonth();

        while ($current->lte($end)) {
            $periodStart = $current->copy()->startOfMonth();
            $periodEnd   = $current->copy()->endOfMonth();

            // Clamp ke batas fiscal year
            if ($periodStart->lt($fiscalYear->start_date)) $periodStart = $fiscalYear->start_date->copy();
            if ($periodEnd->gt($fiscalYear->end_date))     $periodEnd   = $fiscalYear->end_date->copy();

            $exists = AccountingPeriod::where('tenant_id', $fiscalYear->tenant_id)
                ->where('start_date', $periodStart)
                ->where('end_date', $periodEnd)
                ->exists();

            if (! $exists) {
                AccountingPeriod::create([
                    'tenant_id'      => $fiscalYear->tenant_id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'name'           => $current->translatedFormat('F Y'),
                    'start_date'     => $periodStart,
                    'end_date'       => $periodEnd,
                    'status'         => 'open',
                ]);
                $created++;
            }

            $current->addMonth();
        }

        return $created;
    }
}
