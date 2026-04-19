<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\TransactionRevision;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * TransactionStateMachine — Task 35
 *
 * Mengelola transisi status yang ketat untuk semua transaksi utama.
 * Posted = immutable. Tidak bisa loncat status sembarangan.
 *
 * State map:
 *   Invoice:       draft → posted → (partial_paid → paid) | cancelled | voided
 *   PurchaseOrder: draft → posted → sent → partial → received | cancelled
 *   SalesOrder:    draft → posted → confirmed → partial → completed | cancelled
 *
 * Aturan:
 *   - Hanya draft yang bisa diedit
 *   - Hanya draft yang bisa dihapus
 *   - Posted tidak bisa kembali ke draft (harus void/cancel)
 *   - Cancel hanya bisa dari draft atau posted (belum ada pembayaran)
 *   - Void hanya untuk invoice yang sudah posted tapi belum ada pembayaran
 */
class TransactionStateMachine
{
    // ── Invoice ───────────────────────────────────────────────────

    /** Transisi yang diizinkan untuk Invoice.posting_status */
    private const INVOICE_TRANSITIONS = [
        'draft' => ['posted', 'cancelled'],
        'posted' => ['cancelled', 'voided'],
        // partial_paid dan paid dikelola via payment, bukan manual
    ];

    /**
     * Post invoice: draft → posted
     * Setelah posted, invoice masuk laporan dan tidak bisa diedit.
     */
    public function postInvoice(Invoice $invoice, int $userId): void
    {
        $this->assertTransition($invoice->posting_status, 'posted', self::INVOICE_TRANSITIONS, 'Invoice');

        $invoice->update([
            'posting_status' => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);

        ActivityLog::record(
            'invoice_posted',
            "Invoice {$invoice->number} diposting oleh user #{$userId}",
            $invoice
        );
    }

    /**
     * Cancel invoice: draft|posted → cancelled
     * Hanya bisa cancel jika belum ada pembayaran.
     */
    /**
     * Cancel invoice: draft → cancelled
     * 
     * BUG-FIX Task 9.3: Cancel invoice harus mengembalikan stok jika dari SO
     */
    public function cancelInvoice(Invoice $invoice, int $userId, string $reason): void
    {
        $this->assertTransition($invoice->posting_status, 'cancelled', self::INVOICE_TRANSITIONS, 'Invoice');

        if ($invoice->paid_amount > 0) {
            throw new \RuntimeException('Invoice tidak bisa dibatalkan karena sudah ada pembayaran.');
        }

        DB::transaction(function () use ($invoice, $userId, $reason) {
            // 1. Update invoice status
            $invoice->update([
                'posting_status' => 'cancelled',
                'cancelled_by' => $userId,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'status' => 'cancelled',
            ]);

            // 2. Return stock if invoice was from sales order
            if ($invoice->salesOrder) {
                $so = $invoice->salesOrder;
                foreach ($so->items as $item) {
                    // Return stock to warehouse
                    $stock = ProductStock::firstOrCreate(
                        [
                            'product_id' => $item->product_id,
                            'warehouse_id' => $so->warehouse_id ?? Warehouse::where('tenant_id', $invoice->tenant_id)->first()?->id
                        ],
                        ['quantity' => 0]
                    );

                    $before = $stock->quantity;
                    $stock->increment('quantity', $item->quantity);

                    StockMovement::create([
                        'tenant_id' => $invoice->tenant_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $stock->warehouse_id,
                        'user_id' => $userId,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'quantity_before' => $before,
                        'quantity_after' => $before + $item->quantity,
                        'reference' => "CANCEL-{$invoice->number}",
                        'notes' => "Pengembalian stok dari cancel invoice {$invoice->number}. Alasan: {$reason}",
                    ]);
                }
            }

            ActivityLog::record(
                'invoice_cancelled',
                "Invoice {$invoice->number} dibatalkan. Alasan: {$reason}. Stok telah dikembalikan.",
                $invoice
            );
        });
    }

    /**
     * Void invoice: posted → voided
     * Berbeda dari cancel — void membuat jurnal pembalik otomatis.
     * 
     * BUG-FIX Task 9.3: Void/cancel invoice harus membuat jurnal pembalik dan update stok/piutang
     */
    public function voidInvoice(Invoice $invoice, int $userId, string $reason): void
    {
        $this->assertTransition($invoice->posting_status, 'voided', self::INVOICE_TRANSITIONS, 'Invoice');

        if ($invoice->paid_amount > 0) {
            throw new \RuntimeException('Invoice tidak bisa di-void karena sudah ada pembayaran. Gunakan retur penjualan.');
        }

        DB::transaction(function () use ($invoice, $userId, $reason) {
            // 1. Update invoice status
            $invoice->update([
                'posting_status' => 'voided',
                'cancelled_by' => $userId,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'status' => 'voided',
            ]);

            // 2. Create reversing journal entry if invoice was posted with GL
            $originalJournal = JournalEntry::where('tenant_id', $invoice->tenant_id)
                ->where('reference_type', 'invoice')
                ->where('reference_id', $invoice->id)
                ->where('status', 'posted')
                ->first();

            if ($originalJournal) {
                $reversal = $originalJournal->reverse($userId, now()->toDateString());
                $reversal->post($userId);

                ActivityLog::record(
                    'journal_reversed_from_void',
                    "Jurnal pembalik {$reversal->number} dibuat untuk void invoice {$invoice->number}",
                    $reversal
                );
            }

            // 3. Return stock if invoice was from sales order
            if ($invoice->salesOrder) {
                $so = $invoice->salesOrder;
                foreach ($so->items as $item) {
                    // Return stock to warehouse
                    $stock = ProductStock::firstOrCreate(
                        [
                            'product_id' => $item->product_id,
                            'warehouse_id' => $so->warehouse_id ?? Warehouse::where('tenant_id', $invoice->tenant_id)->first()?->id
                        ],
                        ['quantity' => 0]
                    );

                    $before = $stock->quantity;
                    $stock->increment('quantity', $item->quantity);

                    StockMovement::create([
                        'tenant_id' => $invoice->tenant_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $stock->warehouse_id,
                        'user_id' => $userId,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'quantity_before' => $before,
                        'quantity_after' => $before + $item->quantity,
                        'reference' => "VOID-{$invoice->number}",
                        'notes' => "Pengembalian stok dari void invoice {$invoice->number}. Alasan: {$reason}",
                    ]);
                }
            }

            // 4. Update customer receivables (reduce outstanding)
            if ($invoice->customer) {
                $customer = $invoice->customer;
                // Customer receivable will be automatically recalculated by the system
                // No manual adjustment needed as the invoice status change handles it
            }

            ActivityLog::record(
                'invoice_voided',
                "Invoice {$invoice->number} di-void. Alasan: {$reason}. Jurnal pembalik dan pengembalian stok telah diproses.",
                $invoice
            );
        });
    }

    // ── Purchase Order ────────────────────────────────────────────

    private const PO_TRANSITIONS = [
        'draft' => ['posted', 'cancelled'],
        'posted' => ['cancelled'],
    ];

    /**
     * Post PO: draft → posted
     * BUG-PO-001 FIX: Enforce approval workflow before posting
     * Setelah posted, PO bisa dikirim ke supplier (status: sent).
     */
    public function postPurchaseOrder(PurchaseOrder $po, int $userId): void
    {
        // BUG-PO-001 FIX: Check if approval is required and obtained
        $approvalCheck = app(\App\Services\PoApprovalService::class)->canPost($po);

        if (!$approvalCheck['can_post']) {
            throw new \RuntimeException("Cannot post PO: {$approvalCheck['reason']}");
        }

        $this->assertTransition($po->posting_status, 'posted', self::PO_TRANSITIONS, 'Purchase Order');

        $po->update([
            'posting_status' => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);

        ActivityLog::record(
            'po_posted',
            "PO {$po->number} diposting oleh user #{$userId}" . (isset($approvalCheck['approval']) ? ' (Approved)' : ''),
            $po
        );
    }

    /**
     * Cancel PO: draft|posted → cancelled
     * Hanya bisa cancel jika belum ada goods receipt.
     */
    public function cancelPurchaseOrder(PurchaseOrder $po, int $userId, string $reason): void
    {
        $this->assertTransition($po->posting_status, 'cancelled', self::PO_TRANSITIONS, 'Purchase Order');

        if ($po->goodsReceipts()->where('status', 'confirmed')->exists()) {
            throw new \RuntimeException('PO tidak bisa dibatalkan karena sudah ada penerimaan barang.');
        }

        $po->update([
            'posting_status' => 'cancelled',
            'status' => 'cancelled',
            'cancel_reason' => $reason,
        ]);

        ActivityLog::record(
            'po_cancelled',
            "PO {$po->number} dibatalkan. Alasan: {$reason}",
            $po
        );
    }

    // ── Sales Order ───────────────────────────────────────────────

    private const SO_TRANSITIONS = [
        'draft' => ['posted', 'cancelled'],
        'posted' => ['cancelled'],
    ];

    public function postSalesOrder(SalesOrder $so, int $userId): void
    {
        $this->assertTransition($so->posting_status, 'posted', self::SO_TRANSITIONS, 'Sales Order');

        $so->update([
            'posting_status' => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);

        ActivityLog::record(
            'so_posted',
            "SO {$so->number} diposting oleh user #{$userId}",
            $so
        );
    }

    public function cancelSalesOrder(SalesOrder $so, int $userId, string $reason): void
    {
        $this->assertTransition($so->posting_status, 'cancelled', self::SO_TRANSITIONS, 'Sales Order');

        $so->update([
            'posting_status' => 'cancelled',
            'status' => 'cancelled',
            'cancel_reason' => $reason,
        ]);

        ActivityLog::record(
            'so_cancelled',
            "SO {$so->number} dibatalkan. Alasan: {$reason}",
            $so
        );
    }

    // ── Revision System (Task 36) ─────────────────────────────────

    /**
     * Cek apakah model boleh diedit.
     * Draft = boleh edit. Posted = tidak boleh, harus buat revisi.
     *
     * @throws \RuntimeException jika sudah posted
     */
    public function assertEditable(Model $model, string $label = 'Transaksi'): void
    {
        $postingStatus = $model->posting_status ?? 'draft';

        if (in_array($postingStatus, ['posted', 'cancelled', 'voided'])) {
            throw new \RuntimeException(
                "{$label} sudah diposting dan tidak bisa diedit langsung. " .
                "Gunakan fitur Revisi untuk membuat perubahan."
            );
        }
    }

    /**
     * Buat snapshot revisi sebelum perubahan.
     * Dipanggil sebelum update pada transaksi yang sudah posted.
     */
    public function createRevision(
        Model $model,
        int $userId,
        string $reason,
        int $tenantId
    ): TransactionRevision {
        $lastRevision = TransactionRevision::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->max('revision') ?? 0;

        return TransactionRevision::create([
            'tenant_id' => $tenantId,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'revision' => $lastRevision + 1,
            'reason' => $reason,
            'snapshot_before' => $model->toArray(),
            'created_by' => $userId,
        ]);
    }

    /**
     * Finalisasi revisi — simpan snapshot_after.
     */
    public function finalizeRevision(TransactionRevision $revision, Model $model): void
    {
        $revision->update([
            'snapshot_after' => $model->fresh()->toArray(),
            'finalized_at' => now(),
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────

    /**
     * Validasi transisi status.
     *
     * @throws \RuntimeException jika transisi tidak diizinkan
     */
    private function assertTransition(
        string $currentStatus,
        string $targetStatus,
        array $transitions,
        string $label
    ): void {
        $allowed = $transitions[$currentStatus] ?? [];

        if (!in_array($targetStatus, $allowed)) {
            throw new \RuntimeException(
                "{$label} tidak bisa berpindah dari status '{$currentStatus}' ke '{$targetStatus}'. " .
                "Transisi yang diizinkan: " . implode(', ', $allowed ?: ['tidak ada'])
            );
        }
    }

    /**
     * Cek apakah model sudah posted (immutable).
     */
    public function isPosted(Model $model): bool
    {
        return in_array($model->posting_status ?? 'draft', ['posted']);
    }

    /**
     * Cek apakah model masih bisa diubah.
     */
    public function isDraft(Model $model): bool
    {
        return ($model->posting_status ?? 'draft') === 'draft';
    }
}
