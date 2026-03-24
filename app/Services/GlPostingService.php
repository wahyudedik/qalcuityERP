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
 * Dipanggil saat:
 *  - SO dibuat (confirmed)          → Dr Piutang Usaha / Cr Pendapatan Penjualan + HPP
 *  - SO dibayar (cash payment)      → Dr Kas/Bank / Cr Piutang Usaha
 *  - Invoice dibayar                → Dr Kas/Bank / Cr Piutang Usaha
 *  - PO diterima (received)         → Dr Persediaan / Cr Hutang Usaha
 *  - PO dibayar (cash)              → Dr Hutang Usaha / Cr Kas/Bank
 *
 * Jika COA tidak ditemukan atau periode tidak ada/terkunci → log warning, tidak throw exception
 * agar transaksi utama tetap berhasil.
 */
class GlPostingService
{
    // ─── Sales Order ──────────────────────────────────────────────

    /**
     * SO Confirmed → Debit Piutang Usaha / Kredit Pendapatan Penjualan
     * Jika ada pajak → Kredit PPN Keluaran
     * Jika ada HPP   → Debit HPP / Kredit Persediaan
     */
    public function postSalesOrder(
        int    $tenantId,
        int    $userId,
        string $soNumber,
        int    $soId,
        float  $subtotal,
        float  $taxAmount,
        float  $total,
        float  $cogs = 0,        // Harga Pokok Penjualan (opsional)
        string $paymentType = 'credit',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();

        $lines = [];

        if ($paymentType === 'cash') {
            // Cash sale: Dr Kas / Cr Pendapatan
            $lines[] = ['code' => '1101', 'debit' => $total,    'credit' => 0,        'desc' => "Penerimaan kas SO {$soNumber}"];
        } else {
            // Credit sale: Dr Piutang Usaha / Cr Pendapatan
            $lines[] = ['code' => '1103', 'debit' => $total,    'credit' => 0,        'desc' => "Piutang SO {$soNumber}"];
        }

        $lines[] = ['code' => '4101', 'debit' => 0,         'credit' => $subtotal, 'desc' => "Pendapatan penjualan SO {$soNumber}"];

        if ($taxAmount > 0) {
            $lines[] = ['code' => '2103', 'debit' => 0, 'credit' => $taxAmount, 'desc' => "PPN Keluaran SO {$soNumber}"];
        }

        // HPP entry (jika ada cost data)
        if ($cogs > 0) {
            $lines[] = ['code' => '5101', 'debit' => $cogs, 'credit' => 0,    'desc' => "HPP SO {$soNumber}"];
            $lines[] = ['code' => '1105', 'debit' => 0,     'credit' => $cogs, 'desc' => "Keluar persediaan SO {$soNumber}"];
        }

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Sales Order {$soNumber}",
            reference:   $soNumber,
            refType:     'sales_order',
            refId:       $soId,
            lines:       $lines
        );
    }

    /**
     * Pembayaran SO (cash SO yang belum di-post, atau pelunasan piutang SO)
     * Dr Kas/Bank / Cr Piutang Usaha
     */
    public function postSalesPayment(
        int    $tenantId,
        int    $userId,
        string $reference,
        int    $refId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Pembayaran {$reference}",
            reference:   $reference,
            refType:     'payment',
            refId:       $refId,
            lines: [
                ['code' => $cashCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Terima pembayaran {$reference}"],
                ['code' => '1103',    'debit' => 0,        'credit' => $amount, 'desc' => "Lunasi piutang {$reference}"],
            ]
        );
    }

    // ─── Invoice ──────────────────────────────────────────────────

    /**
     * Invoice dibuat (standalone, bukan dari SO)
     * Dr Piutang Usaha / Cr Pendapatan Penjualan [+ PPN]
     */
    public function postInvoiceCreated(
        int    $tenantId,
        int    $userId,
        string $invoiceNumber,
        int    $invoiceId,
        float  $subtotal,
        float  $taxAmount,
        float  $total,
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();

        $lines = [
            ['code' => '1103', 'debit' => $total,    'credit' => 0,        'desc' => "Piutang invoice {$invoiceNumber}"],
            ['code' => '4101', 'debit' => 0,         'credit' => $subtotal, 'desc' => "Pendapatan invoice {$invoiceNumber}"],
        ];

        if ($taxAmount > 0) {
            $lines[] = ['code' => '2103', 'debit' => 0, 'credit' => $taxAmount, 'desc' => "PPN Keluaran {$invoiceNumber}"];
        }

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Invoice {$invoiceNumber}",
            reference:   $invoiceNumber,
            refType:     'invoice',
            refId:       $invoiceId,
            lines:       $lines
        );
    }

    /**
     * Invoice dibayar (sebagian atau lunas)
     * Dr Kas/Bank / Cr Piutang Usaha
     */
    public function postInvoicePayment(
        int    $tenantId,
        int    $userId,
        string $invoiceNumber,
        int    $invoiceId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Pembayaran Invoice {$invoiceNumber}",
            reference:   $invoiceNumber,
            refType:     'invoice_payment',
            refId:       $invoiceId,
            lines: [
                ['code' => $cashCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Terima bayar invoice {$invoiceNumber}"],
                ['code' => '1103',    'debit' => 0,        'credit' => $amount, 'desc' => "Lunasi piutang invoice {$invoiceNumber}"],
            ]
        );
    }

    // ─── Purchase Order ───────────────────────────────────────────

    /**
     * PO Diterima (received) → Dr Persediaan / Cr Hutang Usaha
     */
    public function postPurchaseReceived(
        int    $tenantId,
        int    $userId,
        string $poNumber,
        int    $poId,
        float  $total,
        float  $taxAmount = 0,
        string $paymentType = 'credit',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();

        $inventoryAmount = $total - $taxAmount;
        $lines = [];

        $lines[] = ['code' => '1105', 'debit' => $inventoryAmount, 'credit' => 0, 'desc' => "Terima barang PO {$poNumber}"];

        if ($taxAmount > 0) {
            $lines[] = ['code' => '1107', 'debit' => $taxAmount, 'credit' => 0, 'desc' => "PPN Masukan PO {$poNumber}"];
        }

        if ($paymentType === 'cash') {
            $lines[] = ['code' => '1101', 'debit' => 0, 'credit' => $total, 'desc' => "Bayar tunai PO {$poNumber}"];
        } else {
            $lines[] = ['code' => '2101', 'debit' => 0, 'credit' => $total, 'desc' => "Hutang usaha PO {$poNumber}"];
        }

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Penerimaan PO {$poNumber}",
            reference:   $poNumber,
            refType:     'purchase_order',
            refId:       $poId,
            lines:       $lines
        );
    }

    /**
     * Pembayaran PO (pelunasan hutang usaha)
     * Dr Hutang Usaha / Cr Kas/Bank
     */
    public function postPurchasePayment(
        int    $tenantId,
        int    $userId,
        string $poNumber,
        int    $poId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Bayar PO {$poNumber}",
            reference:   $poNumber,
            refType:     'purchase_payment',
            refId:       $poId,
            lines: [
                ['code' => '2101',    'debit' => $amount, 'credit' => 0,      'desc' => "Lunasi hutang PO {$poNumber}"],
                ['code' => $cashCode, 'debit' => 0,        'credit' => $amount, 'desc' => "Bayar PO {$poNumber}"],
            ]
        );
    }

    // ─── Sales Return ─────────────────────────────────────────────

    /**
     * Retur Penjualan:
     *   Dr Retur Penjualan (4102) / Cr Piutang Usaha (1103)
     *   Dr Persediaan (1105)      / Cr HPP (5101)
     */
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
    ): ?JournalEntry {
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

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Retur Penjualan {$returnNumber}",
            reference:   $returnNumber,
            refType:     'sales_return',
            refId:       $returnId,
            lines:       $lines
        );
    }

    // ─── Purchase Return ───────────────────────────────────────────

    /**
     * Retur Pembelian:
     *   Dr Hutang Usaha (2101) / Cr Retur Pembelian (5102)
     *   Dr HPP (5101)          / Cr Persediaan (1105)
     */
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
    ): ?JournalEntry {
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

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Retur Pembelian {$returnNumber}",
            reference:   $returnNumber,
            refType:     'purchase_return',
            refId:       $returnId,
            lines:       $lines
        );
    }

    // ─── Down Payment ──────────────────────────────────────────────

    /**
     * Uang Muka Customer diterima:
     *   Dr Kas/Bank (1101/1102) / Cr Uang Muka Customer (2104)
     */
    public function postDownPaymentReceived(
        int    $tenantId,
        int    $userId,
        string $dpNumber,
        int    $dpId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Uang Muka Customer {$dpNumber}",
            reference:   $dpNumber,
            refType:     'down_payment_customer',
            refId:       $dpId,
            lines: [
                ['code' => $cashCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Terima DP customer {$dpNumber}"],
                ['code' => '2104',    'debit' => 0,        'credit' => $amount, 'desc' => "Uang muka customer {$dpNumber}"],
            ]
        );
    }

    /**
     * Uang Muka Supplier dibayar:
     *   Dr Uang Muka Supplier (1108) / Cr Kas/Bank (1101/1102)
     */
    public function postDownPaymentPaid(
        int    $tenantId,
        int    $userId,
        string $dpNumber,
        int    $dpId,
        float  $amount,
        string $method = 'transfer',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Uang Muka Supplier {$dpNumber}",
            reference:   $dpNumber,
            refType:     'down_payment_supplier',
            refId:       $dpId,
            lines: [
                ['code' => '1108',    'debit' => $amount, 'credit' => 0,      'desc' => "DP ke supplier {$dpNumber}"],
                ['code' => $cashCode, 'debit' => 0,        'credit' => $amount, 'desc' => "Bayar DP supplier {$dpNumber}"],
            ]
        );
    }

    /**
     * DP Customer diaplikasikan ke invoice:
     *   Dr Uang Muka Customer (2104) / Cr Piutang Usaha (1103)
     */
    public function postDownPaymentApplied(
        int    $tenantId,
        int    $userId,
        string $reference,
        int    $dpId,
        float  $amount,
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Aplikasi DP {$reference}",
            reference:   $reference . '-APP',
            refType:     'down_payment_applied',
            refId:       $dpId,
            lines: [
                ['code' => '2104', 'debit' => $amount, 'credit' => 0,      'desc' => "Pakai DP customer {$reference}"],
                ['code' => '1103', 'debit' => 0,        'credit' => $amount, 'desc' => "Kurangi piutang via DP {$reference}"],
            ]
        );
    }

    // ─── Bulk Payment ──────────────────────────────────────────────

    /**
     * Bulk Payment (1 pembayaran untuk banyak invoice):
     *   Dr Kas/Bank / Cr Piutang Usaha (per invoice)
     *   Jika overpayment: Cr Saldo Customer (2105)
     */
    public function postBulkPayment(
        int    $tenantId,
        int    $userId,
        string $bpNumber,
        int    $bpId,
        float  $totalPaid,
        array  $invoiceLines,  // [['invoice_number' => ..., 'amount' => ...], ...]
        float  $overpayment = 0,
        string $method = 'transfer',
        string $date = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();
        $cashCode = $method === 'cash' ? '1101' : '1102';

        $lines = [
            ['code' => $cashCode, 'debit' => $totalPaid, 'credit' => 0, 'desc' => "Bulk payment {$bpNumber}"],
        ];

        foreach ($invoiceLines as $il) {
            $lines[] = ['code' => '1103', 'debit' => 0, 'credit' => $il['amount'], 'desc' => "Lunasi invoice {$il['invoice_number']}"];
        }

        if ($overpayment > 0) {
            $lines[] = ['code' => '2105', 'debit' => 0, 'credit' => $overpayment, 'desc' => "Saldo lebih customer {$bpNumber}"];
        }

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Bulk Payment {$bpNumber}",
            reference:   $bpNumber,
            refType:     'bulk_payment',
            refId:       $bpId,
            lines:       $lines
        );
    }

    // ─── Expense ──────────────────────────────────────────────────

    /**
     * Pengeluaran dicatat → Dr Beban / Cr Kas atau Bank
     *
     * Kode akun beban ditentukan dari:
     * 1. expense_categories.coa_account_code (jika diset manual)
     * 2. Default berdasarkan category.type
     */
    public function postExpense(
        int    $tenantId,
        int    $userId,
        string $expenseNumber,
        int    $expenseId,
        float  $amount,
        string $paymentMethod,   // cash, transfer, card, other
        string $categoryType,    // operational, cogs, marketing, hr, admin, other
        string $categoryName,
        string $date = null,
        ?string $coaAccountCode = null
    ): ?JournalEntry {
        $date ??= today()->toDateString();

        // Resolve beban account code
        $expenseCode = $coaAccountCode ?? $this->defaultExpenseCode($categoryType);

        // Resolve kas/bank account
        $cashCode = match($paymentMethod) {
            'cash'  => '1101',
            default => '1102', // transfer, card, other → Bank
        };

        return $this->createAndPost(
            tenantId:    $tenantId,
            userId:      $userId,
            date:        $date,
            description: "Auto: Pengeluaran {$expenseNumber} ({$categoryName})",
            reference:   $expenseNumber,
            refType:     'expense',
            refId:       $expenseId,
            lines: [
                ['code' => $expenseCode, 'debit' => $amount, 'credit' => 0,      'desc' => "Beban {$categoryName} — {$expenseNumber}"],
                ['code' => $cashCode,    'debit' => 0,        'credit' => $amount, 'desc' => "Bayar {$expenseNumber} via {$paymentMethod}"],
            ]
        );
    }

    /**
     * Default COA code berdasarkan tipe kategori pengeluaran.
     */
    private function defaultExpenseCode(string $categoryType): string
    {
        return match($categoryType) {
            'cogs'        => '5101', // HPP Barang
            'marketing'   => '5205', // Beban Pemasaran
            'hr'          => '5201', // Beban Gaji / SDM
            'admin'       => '5206', // Beban Administrasi
            'operational' => '5202', // Beban Sewa / Operasional
            default       => '5208', // Beban Lain-lain
        };
    }

    // ─── Core Engine ──────────────────────────────────────────────

    /**
     * Buat JournalEntry + lines, lalu langsung post.
     * Jika COA tidak ditemukan atau periode terkunci → log warning, return null.
     * Tidak pernah throw exception agar transaksi utama tidak gagal.
     */
    private function createAndPost(
        int    $tenantId,
        int    $userId,
        string $date,
        string $description,
        string $reference,
        string $refType,
        int    $refId,
        array  $lines
    ): ?JournalEntry {
        try {
            // Cek apakah sudah ada jurnal untuk referensi ini (idempotent)
            $exists = JournalEntry::where('tenant_id', $tenantId)
                ->where('reference', $reference)
                ->where('reference_type', $refType)
                ->where('status', '!=', 'reversed')
                ->exists();

            if ($exists) {
                Log::info("GL Auto-Post skipped (already exists): {$refType} {$reference}");
                return null;
            }

            // Resolve account IDs
            $resolvedLines = [];
            foreach ($lines as $line) {
                $accountId = $this->resolveAccount($tenantId, $line['code']);
                if (! $accountId) {
                    Log::warning("GL Auto-Post: akun {$line['code']} tidak ditemukan untuk tenant {$tenantId}. Jurnal dibatalkan.");
                    return null;
                }
                $resolvedLines[] = [
                    'account_id'  => $accountId,
                    'debit'       => round((float) $line['debit'], 2),
                    'credit'      => round((float) $line['credit'], 2),
                    'description' => $line['desc'] ?? $description,
                ];
            }

            // Validasi balance
            $totalDebit  = array_sum(array_column($resolvedLines, 'debit'));
            $totalCredit = array_sum(array_column($resolvedLines, 'credit'));
            if (abs($totalDebit - $totalCredit) > 0.01) {
                Log::warning("GL Auto-Post: jurnal tidak balance untuk {$refType} {$reference}. D={$totalDebit} C={$totalCredit}");
                return null;
            }

            // Cari periode akuntansi
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

            // Auto-post langsung
            $je->post($userId);

            Log::info("GL Auto-Post success: {$refType} {$reference} → JE {$je->number}");

            return $je;

        } catch (\Throwable $e) {
            Log::error("GL Auto-Post failed for {$refType} {$reference}: " . $e->getMessage());
            return null;
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
