<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\Log;

/**
 * GlPostingService — Auto-posting jurnal GL dari transaksi bisnis.
 *
 * Semua method public sekarang return GlPostingResult (bukan ?JournalEntry).
 * Caller HARUS cek ->isFailed() dan flash warning ke user jika perlu.
 *
 * Dipanggil saat:
 *  - SO dibuat (confirmed)          → Dr Piutang Usaha / Cr Pendapatan Penjualan + HPP
 *  - SO dibayar (cash payment)      → Dr Kas/Bank / Cr Piutang Usaha
 *  - Invoice dibayar                → Dr Kas/Bank / Cr Piutang Usaha
 *  - PO diterima (received)         → Dr Persediaan / Cr Hutang Usaha
 *  - PO dibayar (cash)              → Dr Hutang Usaha / Cr Kas/Bank
 *  - Depresiasi aset                → Dr Beban Penyusutan / Cr Akumulasi Penyusutan
 */
class GlPostingService
{
    // ─── Asset Depreciation ───────────────────────────────────────

    /**
     * Depresiasi aset (per periode, bisa batch banyak aset sekaligus).
     *
     *   Dr  5204  Beban Penyusutan        (total depresiasi periode ini)
     *   ──────────────────────────────────────────────────────────────
     *   Cr  1202  Akumulasi Penyusutan    (total depresiasi periode ini)
     *
     * @param  int    $tenantId
     * @param  int    $userId
     * @param  string $period     Format Y-m, e.g. "2026-03"
     * @param  float  $totalAmount Total depresiasi semua aset periode ini
     * @param  array  $assetLines  [['asset_name' => ..., 'amount' => ...], ...]
     */
    public function postDepreciation(
        int    $tenantId,
        int    $userId,
        string $period,
        float  $totalAmount,
        array  $assetLines = []
    ): GlPostingResult {
        if ($totalAmount <= 0) {
            return GlPostingResult::skipped('Total depresiasi 0, tidak perlu jurnal.');
        }

        // Build description lines
        $desc = count($assetLines) > 0
            ? implode('; ', array_map(fn($l) => "{$l['asset_name']} Rp " . number_format($l['amount'], 0, ',', '.'), array_slice($assetLines, 0, 5)))
            : "Depresiasi {$period}";

        if (count($assetLines) > 5) {
            $desc .= ' ... (+' . (count($assetLines) - 5) . ' aset lainnya)';
        }

        // Use last day of the period as journal date
        [$year, $month] = explode('-', $period);
        $date = \Carbon\Carbon::create((int)$year, (int)$month)->endOfMonth()->toDateString();

        return $this->createAndPost(
            refType:     'asset_depreciation',
            reference:   "DEP-{$period}",
            refId:       0, // batch — no single ref ID
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Beban Penyusutan Aset {$period}",
            lines: [
                ['code' => '5204', 'debit' => $totalAmount, 'credit' => 0,           'desc' => "Beban penyusutan {$period}: {$desc}"],
                ['code' => '1202', 'debit' => 0,            'credit' => $totalAmount, 'desc' => "Akumulasi penyusutan {$period}"],
            ]
        );
    }

    // ─── Sales Order ──────────────────────────────────────────────

    public function postSalesOrder(
        int    $tenantId,
        int    $userId,
        string $soNumber,
        int    $soId,
        float  $subtotal,
        float  $taxAmount,
        float  $total,
        float  $cogs = 0,
        string $paymentType = 'credit',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $lines = [];

        if ($paymentType === 'cash') {
            $lines[] = ['code' => '1101', 'debit' => $total,    'credit' => 0,        'desc' => "Penerimaan kas SO {$soNumber}"];
        } else {
            $lines[] = ['code' => '1103', 'debit' => $total,    'credit' => 0,        'desc' => "Piutang SO {$soNumber}"];
        }
        $lines[] = ['code' => '4101', 'debit' => 0, 'credit' => $subtotal, 'desc' => "Pendapatan penjualan SO {$soNumber}"];

        if ($taxAmount > 0) {
            $lines[] = ['code' => '2103', 'debit' => 0, 'credit' => $taxAmount, 'desc' => "PPN Keluaran SO {$soNumber}"];
        }
        if ($cogs > 0) {
            $lines[] = ['code' => '5101', 'debit' => $cogs, 'credit' => 0,    'desc' => "HPP SO {$soNumber}"];
            $lines[] = ['code' => '1105', 'debit' => 0,     'credit' => $cogs, 'desc' => "Keluar persediaan SO {$soNumber}"];
        }

        return $this->createAndPost('sales_order', $soNumber, $soId, $tenantId, $userId, $date,
            "Auto: Sales Order {$soNumber}", $lines);
    }

    public function postSalesPayment(
        int    $tenantId,
        int    $userId,
        string $reference,
        int    $refId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost('payment', $reference, $refId, $tenantId, $userId, $date,
            "Auto: Pembayaran {$reference}", [
                ['code' => $cashCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Terima pembayaran {$reference}"],
                ['code' => '1103',    'debit' => 0,        'credit' => $amount, 'desc' => "Lunasi piutang {$reference}"],
            ]);
    }

    // ─── Invoice ──────────────────────────────────────────────────

    public function postInvoiceCreated(
        int    $tenantId,
        int    $userId,
        string $invoiceNumber,
        int    $invoiceId,
        float  $subtotal,
        float  $taxAmount,
        float  $total,
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $lines = [
            ['code' => '1103', 'debit' => $total,    'credit' => 0,        'desc' => "Piutang invoice {$invoiceNumber}"],
            ['code' => '4101', 'debit' => 0,         'credit' => $subtotal, 'desc' => "Pendapatan invoice {$invoiceNumber}"],
        ];
        if ($taxAmount > 0) {
            $lines[] = ['code' => '2103', 'debit' => 0, 'credit' => $taxAmount, 'desc' => "PPN Keluaran {$invoiceNumber}"];
        }

        return $this->createAndPost('invoice', $invoiceNumber, $invoiceId, $tenantId, $userId, $date,
            "Auto: Invoice {$invoiceNumber}", $lines);
    }

    public function postInvoicePayment(
        int    $tenantId,
        int    $userId,
        string $invoiceNumber,
        int    $invoiceId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost('invoice_payment', $invoiceNumber, $invoiceId, $tenantId, $userId, $date,
            "Auto: Pembayaran Invoice {$invoiceNumber}", [
                ['code' => $cashCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Terima bayar invoice {$invoiceNumber}"],
                ['code' => '1103',    'debit' => 0,        'credit' => $amount, 'desc' => "Lunasi piutang invoice {$invoiceNumber}"],
            ]);
    }

    // ─── Purchase Order ───────────────────────────────────────────

    public function postPurchaseReceived(
        int    $tenantId,
        int    $userId,
        string $poNumber,
        int    $poId,
        float  $total,
        float  $taxAmount = 0,
        string $paymentType = 'credit',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $inventoryAmount = $total - $taxAmount;
        $lines = [['code' => '1105', 'debit' => $inventoryAmount, 'credit' => 0, 'desc' => "Terima barang PO {$poNumber}"]];

        if ($taxAmount > 0) {
            $lines[] = ['code' => '1107', 'debit' => $taxAmount, 'credit' => 0, 'desc' => "PPN Masukan PO {$poNumber}"];
        }
        $lines[] = $paymentType === 'cash'
            ? ['code' => '1101', 'debit' => 0, 'credit' => $total, 'desc' => "Bayar tunai PO {$poNumber}"]
            : ['code' => '2101', 'debit' => 0, 'credit' => $total, 'desc' => "Hutang usaha PO {$poNumber}"];

        return $this->createAndPost('purchase_order', $poNumber, $poId, $tenantId, $userId, $date,
            "Auto: Penerimaan PO {$poNumber}", $lines);
    }

    public function postPurchasePayment(
        int    $tenantId,
        int    $userId,
        string $poNumber,
        int    $poId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost('purchase_payment', $poNumber, $poId, $tenantId, $userId, $date,
            "Auto: Bayar PO {$poNumber}", [
                ['code' => '2101',    'debit' => $amount, 'credit' => 0,      'desc' => "Lunasi hutang PO {$poNumber}"],
                ['code' => $cashCode, 'debit' => 0,        'credit' => $amount, 'desc' => "Bayar PO {$poNumber}"],
            ]);
    }

    // ─── Sales Return ─────────────────────────────────────────────

    public function postSalesReturn(
        int    $tenantId,
        int    $userId,
        string $returnNumber,
        int    $returnId,
        float  $subtotal,
        float  $taxAmount,
        float  $total,
        float  $cogs = 0,
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $lines = [
            ['code' => '4102', 'debit' => $subtotal, 'credit' => 0,      'desc' => "Retur penjualan {$returnNumber}"],
            ['code' => '1103', 'debit' => 0,          'credit' => $total, 'desc' => "Kurangi piutang retur {$returnNumber}"],
        ];
        if ($taxAmount > 0) {
            $lines[] = ['code' => '2103', 'debit' => $taxAmount, 'credit' => 0, 'desc' => "Koreksi PPN retur {$returnNumber}"];
        }
        if ($cogs > 0) {
            $lines[] = ['code' => '1105', 'debit' => $cogs, 'credit' => 0,    'desc' => "Terima kembali persediaan {$returnNumber}"];
            $lines[] = ['code' => '5101', 'debit' => 0,     'credit' => $cogs, 'desc' => "Koreksi HPP retur {$returnNumber}"];
        }

        return $this->createAndPost('sales_return', $returnNumber, $returnId, $tenantId, $userId, $date,
            "Auto: Retur Penjualan {$returnNumber}", $lines);
    }

    // ─── Purchase Return ──────────────────────────────────────────

    public function postPurchaseReturn(
        int    $tenantId,
        int    $userId,
        string $returnNumber,
        int    $returnId,
        float  $subtotal,
        float  $taxAmount,
        float  $total,
        float  $cogs = 0,
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $lines = [
            ['code' => '2101', 'debit' => $total,    'credit' => 0,         'desc' => "Kurangi hutang retur {$returnNumber}"],
            ['code' => '5102', 'debit' => 0,          'credit' => $subtotal, 'desc' => "Retur pembelian {$returnNumber}"],
        ];
        if ($taxAmount > 0) {
            $lines[] = ['code' => '1107', 'debit' => 0, 'credit' => $taxAmount, 'desc' => "Koreksi PPN masukan retur {$returnNumber}"];
        }
        if ($cogs > 0) {
            $lines[] = ['code' => '5101', 'debit' => $cogs, 'credit' => 0,    'desc' => "Koreksi HPP retur beli {$returnNumber}"];
            $lines[] = ['code' => '1105', 'debit' => 0,     'credit' => $cogs, 'desc' => "Kurangi persediaan retur {$returnNumber}"];
        }

        return $this->createAndPost('purchase_return', $returnNumber, $returnId, $tenantId, $userId, $date,
            "Auto: Retur Pembelian {$returnNumber}", $lines);
    }

    // ─── Down Payment ─────────────────────────────────────────────

    public function postDownPaymentReceived(
        int    $tenantId,
        int    $userId,
        string $dpNumber,
        int    $dpId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost('down_payment_customer', $dpNumber, $dpId, $tenantId, $userId, $date,
            "Auto: Uang Muka Customer {$dpNumber}", [
                ['code' => $cashCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Terima DP customer {$dpNumber}"],
                ['code' => '2104',    'debit' => 0,        'credit' => $amount, 'desc' => "Uang muka customer {$dpNumber}"],
            ]);
    }

    public function postDownPaymentPaid(
        int    $tenantId,
        int    $userId,
        string $dpNumber,
        int    $dpId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost('down_payment_supplier', $dpNumber, $dpId, $tenantId, $userId, $date,
            "Auto: Uang Muka Supplier {$dpNumber}", [
                ['code' => '1108',    'debit' => $amount, 'credit' => 0,      'desc' => "DP ke supplier {$dpNumber}"],
                ['code' => $cashCode, 'debit' => 0,        'credit' => $amount, 'desc' => "Bayar DP supplier {$dpNumber}"],
            ]);
    }

    public function postDownPaymentApplied(
        int    $tenantId,
        int    $userId,
        string $reference,
        int    $dpId,
        float  $amount,
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();

        return $this->createAndPost('down_payment_applied', $reference . '-APP', $dpId, $tenantId, $userId, $date,
            "Auto: Aplikasi DP {$reference}", [
                ['code' => '2104', 'debit' => $amount, 'credit' => 0,      'desc' => "Pakai DP customer {$reference}"],
                ['code' => '1103', 'debit' => 0,        'credit' => $amount, 'desc' => "Kurangi piutang via DP {$reference}"],
            ]);
    }

    // ─── Bulk Payment ─────────────────────────────────────────────

    public function postBulkPayment(
        int    $tenantId,
        int    $userId,
        string $bpNumber,
        int    $bpId,
        float  $totalPaid,
        array  $invoiceLines,
        float  $overpayment = 0,
        string $method = 'transfer',
        string $date = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        $lines = [['code' => $cashCode, 'debit' => $totalPaid, 'credit' => 0, 'desc' => "Bulk payment {$bpNumber}"]];
        foreach ($invoiceLines as $il) {
            $lines[] = ['code' => '1103', 'debit' => 0, 'credit' => $il['amount'], 'desc' => "Lunasi invoice {$il['invoice_number']}"];
        }
        if ($overpayment > 0) {
            $lines[] = ['code' => '2105', 'debit' => 0, 'credit' => $overpayment, 'desc' => "Saldo lebih customer {$bpNumber}"];
        }

        return $this->createAndPost('bulk_payment', $bpNumber, $bpId, $tenantId, $userId, $date,
            "Auto: Bulk Payment {$bpNumber}", $lines);
    }

    // ─── Expense ──────────────────────────────────────────────────

    public function postExpense(
        int    $tenantId,
        int    $userId,
        string $expenseNumber,
        int    $expenseId,
        float  $amount,
        string $paymentMethod,
        string $categoryType,
        string $categoryName,
        string $date = null,
        ?string $coaAccountCode = null
    ): GlPostingResult {
        $date ??= today()->toDateString();
        $expenseCode = $coaAccountCode ?? $this->defaultExpenseCode($categoryType);
        $cashCode    = $paymentMethod === 'cash' ? '1101' : '1102';

        return $this->createAndPost('expense', $expenseNumber, $expenseId, $tenantId, $userId, $date,
            "Auto: Pengeluaran {$expenseNumber} ({$categoryName})", [
                ['code' => $expenseCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Beban {$categoryName} — {$expenseNumber}"],
                ['code' => $cashCode,    'debit' => 0,        'credit' => $amount, 'desc' => "Bayar {$expenseNumber} via {$paymentMethod}"],
            ]);
    }

    private function defaultExpenseCode(string $categoryType): string
    {
        return match($categoryType) {
            'cogs'        => '5101',
            'marketing'   => '5205',
            'hr'          => '5201',
            'admin'       => '5206',
            'operational' => '5202',
            default       => '5208',
        };
    }

    // ─── Core Engine ──────────────────────────────────────────────

    /**
     * Buat JournalEntry + lines, lalu langsung post.
     * Return GlPostingResult — caller HARUS cek isFailed() dan tampilkan warning ke user.
     */
    private function createAndPost(
        string $refType,
        string $reference,
        int    $refId,
        int    $tenantId,
        int    $userId,
        string $date,
        string $description,
        array  $lines
    ): GlPostingResult {
        try {
            // Idempotency check
            $exists = JournalEntry::where('tenant_id', $tenantId)
                ->where('reference', $reference)
                ->where('reference_type', $refType)
                ->where('status', '!=', 'reversed')
                ->exists();

            if ($exists) {
                Log::info("GL Auto-Post skipped (already exists): {$refType} {$reference}");
                return GlPostingResult::skipped("Jurnal sudah ada untuk {$refType} {$reference}");
            }

            // Resolve account IDs — collect ALL missing codes first
            $resolvedLines = [];
            $missingCodes  = [];

            foreach ($lines as $line) {
                $accountId = $this->resolveAccount($tenantId, $line['code']);
                if (!$accountId) {
                    $missingCodes[] = $line['code'];
                } else {
                    $resolvedLines[] = [
                        'account_id'  => $accountId,
                        'debit'       => round((float) $line['debit'], 2),
                        'credit'      => round((float) $line['credit'], 2),
                        'description' => $line['desc'] ?? $description,
                    ];
                }
            }

            if (!empty($missingCodes)) {
                $codesStr = implode(', ', $missingCodes);
                Log::warning("GL Auto-Post: akun [{$codesStr}] tidak ditemukan untuk tenant {$tenantId}. Ref: {$refType} {$reference}");
                return GlPostingResult::failed(
                    "Akun COA tidak ditemukan: {$codesStr}",
                    $missingCodes
                );
            }

            // Balance check
            $totalDebit  = array_sum(array_column($resolvedLines, 'debit'));
            $totalCredit = array_sum(array_column($resolvedLines, 'credit'));
            if (abs($totalDebit - $totalCredit) > 0.01) {
                $msg = "Jurnal tidak balance (D={$totalDebit} C={$totalCredit})";
                Log::warning("GL Auto-Post: {$msg} untuk {$refType} {$reference}");
                return GlPostingResult::failed($msg);
            }

            // Find accounting period
            $period = AccountingPeriod::findForDate($tenantId, $date);

            $je = JournalEntry::create([
                'tenant_id'      => $tenantId,
                'period_id'      => $period?->id,
                'user_id'        => $userId,
                'number'         => JournalEntry::generateNumber($tenantId, 'AUTO'),
                'date'           => $date,
                'description'    => $description,
                'reference'      => $reference,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'currency_code'  => 'IDR',
                'currency_rate'  => 1,
                'status'         => 'draft',
            ]);

            foreach ($resolvedLines as $line) {
                JournalEntryLine::create(array_merge($line, ['journal_entry_id' => $je->id]));
            }

            $je->post($userId);

            Log::info("GL Auto-Post success: {$refType} {$reference} → JE {$je->number}");

            return GlPostingResult::success($je);

        } catch (\Throwable $e) {
            Log::error("GL Auto-Post exception for {$refType} {$reference}: " . $e->getMessage());
            return GlPostingResult::failed("Exception: " . $e->getMessage());
        }
    }

    /** Resolve kode akun → account_id, dengan cache per request */
    private array $accountCache = [];

    private function resolveAccount(int $tenantId, string $code): ?int
    {
        $key = "{$tenantId}:{$code}";
        if (isset($this->accountCache[$key])) {
            return $this->accountCache[$key];
        }

        $id = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('code', $code)
            ->where('is_active', true)
            ->value('id');

        $this->accountCache[$key] = $id;
        return $id;
    }
}
