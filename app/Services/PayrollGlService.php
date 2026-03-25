<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\PayrollRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PayrollGlService — Rekonsiliasi Payroll ke General Ledger.
 *
 * Saat payroll diproses, otomatis membuat jurnal akuntansi:
 *
 *   Dr  5201  Beban Gaji                  (total gross)
 *   Dr  5209  Beban BPJS Perusahaan       (BPJS employer share — 4%)
 *   ─────────────────────────────────────────────────────────────
 *   Cr  2108  Hutang Gaji                 (total net — gaji yang harus dibayar ke karyawan)
 *   Cr  2104  PPh 21 Terutang             (total PPh 21)
 *   Cr  2109  Hutang BPJS                 (BPJS employee + employer)
 *
 * Akun di-resolve by code dari COA tenant. Jika akun tidak ditemukan,
 * service akan mencoba membuat akun default, atau melempar exception.
 */
class PayrollGlService
{
    // COA codes yang digunakan — sesuai DefaultCoaSeeder
    const COA_BEBAN_GAJI       = '5201';
    const COA_BEBAN_BPJS       = '5209';
    const COA_HUTANG_GAJI      = '2108';
    const COA_PPH21_TERUTANG   = '2104';
    const COA_HUTANG_BPJS      = '2109';
    const COA_KAS_BANK         = '1102'; // Bank (default untuk transfer gaji)

    public function __construct(private DocumentNumberService $docNumber) {}

    /**
     * Buat jurnal GL untuk satu PayrollRun.
     * Dipanggil setelah payroll selesai diproses.
     *
     * Jurnal: Dr Beban Gaji / Cr Hutang Gaji + PPh 21 + BPJS
     *
     * @throws \RuntimeException jika akun COA tidak ditemukan dan tidak bisa dibuat
     */
    public function createJournal(PayrollRun $run, int $userId): JournalEntry
    {
        // Jangan buat duplikat
        if ($run->journal_entry_id) {
            return JournalEntry::findOrFail($run->journal_entry_id);
        }

        $tid   = $run->tenant_id;
        $items = $run->items()->get();

        // Aggregate totals dari items
        $totalGross      = (float) $items->sum('gross_salary');
        $totalPph21      = (float) $items->sum('tax_pph21');
        $totalBpjsEmp    = (float) $items->sum('bpjs_employee');   // potongan karyawan (3%)
        $totalBpjsEr     = round($totalGross * 0.04);              // employer share (4%)
        $totalBpjs       = $totalBpjsEmp + $totalBpjsEr;
        $totalNet        = (float) $items->sum('net_salary');

        // Resolve COA accounts
        $accounts = $this->resolveAccounts($tid);

        // Tanggal jurnal = akhir bulan periode payroll
        [$year, $month] = explode('-', $run->period);
        $journalDate = \Carbon\Carbon::create($year, $month)->endOfMonth()->toDateString();

        // Cari accounting period
        $period = AccountingPeriod::findForDate($tid, $journalDate);

        return DB::transaction(function () use (
            $run, $tid, $userId, $accounts, $journalDate, $period,
            $totalGross, $totalPph21, $totalBpjsEmp, $totalBpjsEr, $totalBpjs, $totalNet
        ) {
            $number = $this->docNumber->generate($tid, 'payroll', 'PAY');

            $journal = JournalEntry::create([
                'tenant_id'      => $tid,
                'period_id'      => $period?->id,
                'user_id'        => $userId,
                'number'         => $number,
                'date'           => $journalDate,
                'description'    => "Beban Gaji Periode {$run->period}",
                'reference'      => $run->period,
                'reference_type' => 'payroll',
                'reference_id'   => $run->id,
                'currency_code'  => 'IDR',
                'currency_rate'  => 1,
                'status'         => 'draft',
            ]);

            // ── DEBIT lines ───────────────────────────────────────
            // Dr Beban Gaji (gross salary seluruh karyawan)
            $journal->lines()->create([
                'account_id'  => $accounts[self::COA_BEBAN_GAJI]->id,
                'debit'       => $totalGross,
                'credit'      => 0,
                'description' => "Beban gaji {$run->period} ({$run->items()->count()} karyawan)",
            ]);

            // Dr Beban BPJS Perusahaan (employer share 4%)
            if ($totalBpjsEr > 0) {
                $journal->lines()->create([
                    'account_id'  => $accounts[self::COA_BEBAN_BPJS]->id,
                    'debit'       => $totalBpjsEr,
                    'credit'      => 0,
                    'description' => "Beban BPJS perusahaan {$run->period} (4%)",
                ]);
            }

            // ── CREDIT lines ──────────────────────────────────────
            // Cr Hutang Gaji (net salary — yang harus ditransfer ke karyawan)
            $journal->lines()->create([
                'account_id'  => $accounts[self::COA_HUTANG_GAJI]->id,
                'debit'       => 0,
                'credit'      => $totalNet,
                'description' => "Hutang gaji bersih {$run->period}",
            ]);

            // Cr PPh 21 Terutang
            if ($totalPph21 > 0) {
                $journal->lines()->create([
                    'account_id'  => $accounts[self::COA_PPH21_TERUTANG]->id,
                    'debit'       => 0,
                    'credit'      => $totalPph21,
                    'description' => "PPh 21 terutang {$run->period}",
                ]);
            }

            // Cr Hutang BPJS (employee + employer)
            if ($totalBpjs > 0) {
                $journal->lines()->create([
                    'account_id'  => $accounts[self::COA_HUTANG_BPJS]->id,
                    'debit'       => 0,
                    'credit'      => $totalBpjs,
                    'description' => "Hutang BPJS {$run->period} (karyawan 3% + perusahaan 4%)",
                ]);
            }

            // Auto-post jika balanced
            if ($journal->isBalanced()) {
                $journal->post($userId);
            } else {
                Log::warning("PayrollGL: jurnal {$number} tidak balance. Debit={$journal->totalDebit()} Credit={$journal->totalCredit()}");
            }

            // Link journal ke payroll run
            $run->update(['journal_entry_id' => $journal->id]);

            return $journal->fresh();
        });
    }

    /**
     * Jurnal pembayaran gaji — dipanggil saat status payroll → 'paid'.
     *
     *   Dr  2108  Hutang Gaji     (total net — lunasi hutang ke karyawan)
     *   ─────────────────────────────────────────────────────────────────
     *   Cr  1102  Bank / Kas      (kas keluar untuk bayar gaji)
     *
     * @throws \RuntimeException
     */
    public function createPaymentJournal(PayrollRun $run, int $userId): JournalEntry
    {
        // Jangan buat duplikat
        if ($run->payment_journal_entry_id) {
            return JournalEntry::findOrFail($run->payment_journal_entry_id);
        }

        $tid      = $run->tenant_id;
        $totalNet = (float) $run->total_net;

        $accounts = $this->resolveAccounts($tid, [
            self::COA_HUTANG_GAJI,
            self::COA_KAS_BANK,
        ]);

        [$year, $month] = explode('-', $run->period);
        $journalDate = \Carbon\Carbon::create($year, $month)->endOfMonth()->toDateString();
        $period      = AccountingPeriod::findForDate($tid, $journalDate);

        return DB::transaction(function () use ($run, $tid, $userId, $accounts, $journalDate, $period, $totalNet) {
            $number = $this->docNumber->generate($tid, 'payroll', 'PAY-CASH');

            $journal = JournalEntry::create([
                'tenant_id'      => $tid,
                'period_id'      => $period?->id,
                'user_id'        => $userId,
                'number'         => $number,
                'date'           => $journalDate,
                'description'    => "Pembayaran Gaji Periode {$run->period}",
                'reference'      => $run->period . '-PAY',
                'reference_type' => 'payroll_payment',
                'reference_id'   => $run->id,
                'currency_code'  => 'IDR',
                'currency_rate'  => 1,
                'status'         => 'draft',
            ]);

            // Dr Hutang Gaji (lunasi kewajiban)
            $journal->lines()->create([
                'account_id'  => $accounts[self::COA_HUTANG_GAJI]->id,
                'debit'       => $totalNet,
                'credit'      => 0,
                'description' => "Lunasi hutang gaji {$run->period}",
            ]);

            // Cr Bank (kas keluar)
            $journal->lines()->create([
                'account_id'  => $accounts[self::COA_KAS_BANK]->id,
                'debit'       => 0,
                'credit'      => $totalNet,
                'description' => "Transfer gaji {$run->period} ke karyawan",
            ]);

            if ($journal->isBalanced()) {
                $journal->post($userId);
            } else {
                Log::warning("PayrollGL payment: jurnal {$number} tidak balance.");
            }

            $run->update(['payment_journal_entry_id' => $journal->id]);

            return $journal->fresh();
        });
    }

    /**
     * Resolve semua COA accounts yang dibutuhkan.
     *
     * @param  array|null $codes  Subset kode yang dibutuhkan. Null = semua kode payroll.
     * @return array<string, ChartOfAccount>
     */
    private function resolveAccounts(int $tenantId, ?array $codes = null): array
    {
        $codes ??= [
            self::COA_BEBAN_GAJI,
            self::COA_BEBAN_BPJS,
            self::COA_HUTANG_GAJI,
            self::COA_PPH21_TERUTANG,
            self::COA_HUTANG_BPJS,
        ];

        $found = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('code', $codes)
            ->where('is_active', true)
            ->get()
            ->keyBy('code');

        // Auto-create missing accounts
        $defaults = $this->defaultPayrollAccounts();
        foreach ($codes as $code) {
            if (!$found->has($code)) {
                $def = $defaults[$code];
                $parent = $def['parent_code']
                    ? ChartOfAccount::where('tenant_id', $tenantId)->where('code', $def['parent_code'])->first()
                    : null;

                $account = ChartOfAccount::create([
                    'tenant_id'      => $tenantId,
                    'code'           => $code,
                    'name'           => $def['name'],
                    'type'           => $def['type'],
                    'normal_balance' => $def['normal_balance'],
                    'level'          => $def['level'],
                    'is_header'      => false,
                    'is_active'      => true,
                    'parent_id'      => $parent?->id,
                ]);
                $found->put($code, $account);
            }
        }

        return $found->all();
    }

    private function defaultPayrollAccounts(): array
    {
        return [
            self::COA_BEBAN_GAJI     => ['name' => 'Beban Gaji',            'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            self::COA_BEBAN_BPJS     => ['name' => 'Beban BPJS Perusahaan', 'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            self::COA_HUTANG_GAJI    => ['name' => 'Hutang Gaji',           'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            self::COA_PPH21_TERUTANG => ['name' => 'PPh 21 Terutang',       'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            self::COA_HUTANG_BPJS    => ['name' => 'Hutang BPJS',           'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            self::COA_KAS_BANK       => ['name' => 'Bank',                  'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'parent_code' => '1100'],
        ];
    }
}
