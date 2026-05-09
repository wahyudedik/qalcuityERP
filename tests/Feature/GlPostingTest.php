<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\GlPostingResult;
use App\Services\GlPostingService;
use Tests\TestCase;

class GlPostingTest extends TestCase
{
    private $tenant;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
        $this->seedCoa($this->tenant->id);
    }

    // ── GlPostingResult ───────────────────────────────────────────

    public function test_gl_posting_result_success_returns_correct_state(): void
    {
        $je = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'number' => 'TEST-001',
            'date' => today(),
            'description' => 'Test',
            'reference' => 'REF-001',
            'reference_type' => 'test',
            'currency_code' => 'IDR',
            'currency_rate' => 1,
            'status' => 'posted',
        ]);

        $result = GlPostingResult::success($je);

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailed());
        $this->assertFalse($result->isSkipped());
        $this->assertNull($result->warningMessage());
        $this->assertSame($je, $result->journal);
    }

    public function test_gl_posting_result_failed_returns_warning_with_missing_coa(): void
    {
        $result = GlPostingResult::failed('Akun tidak ditemukan', ['1101', '4101']);

        $this->assertTrue($result->isFailed());
        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->warningMessage());
        $this->assertStringContainsString('1101', $result->warningMessage());
        $this->assertStringContainsString('4101', $result->warningMessage());
        $this->assertEquals(['1101', '4101'], $result->missingCoa);
    }

    public function test_gl_posting_result_skipped_has_no_warning(): void
    {
        $result = GlPostingResult::skipped('Already exists');

        $this->assertTrue($result->isSkipped());
        $this->assertNull($result->warningMessage());
    }

    // ── GlPostingService — Sales Order ────────────────────────────

    public function test_posts_credit_sales_order_journal(): void
    {
        $gl = app(GlPostingService::class);

        $result = $gl->postSalesOrder(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            soNumber: 'SO-TEST-001',
            soId: 1,
            subtotal: 500000,
            taxAmount: 0,
            total: 500000,
            paymentType: 'credit',
            date: today()->toDateString(),
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertEquals('posted', $result->journal->status);

        // Dr Piutang (1103) = 500k
        $debitLine = $result->journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('1103', $debitLine->account->code);
        $this->assertEquals(500000, $debitLine->debit);

        // Cr Pendapatan (4101) = 500k
        $creditLine = $result->journal->lines->where('credit', '>', 0)->first();
        $this->assertEquals('4101', $creditLine->account->code);
        $this->assertEquals(500000, $creditLine->credit);
    }

    public function test_posts_cash_sales_order_journal(): void
    {
        $gl = app(GlPostingService::class);

        $result = $gl->postSalesOrder(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            soNumber: 'SO-CASH-001',
            soId: 1,
            subtotal: 200000,
            taxAmount: 0,
            total: 200000,
            paymentType: 'cash',
        );

        $this->assertTrue($result->isSuccess());

        // Dr Kas (1101) bukan Piutang
        $debitLine = $result->journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('1101', $debitLine->account->code);
    }

    public function test_returns_failed_when_coa_missing(): void
    {
        ChartOfAccount::where('tenant_id', $this->tenant->id)->delete();

        $gl = app(GlPostingService::class);

        $result = $gl->postSalesOrder(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            soNumber: 'SO-NOCOA-001',
            soId: 1,
            subtotal: 100000,
            taxAmount: 0,
            total: 100000,
            paymentType: 'credit',
        );

        $this->assertTrue($result->isFailed());
        $this->assertNotEmpty($result->missingCoa);
        $this->assertNotNull($result->warningMessage());

        // Tidak ada jurnal di DB
        $this->assertDatabaseMissing('journal_entries', [
            'tenant_id' => $this->tenant->id,
            'reference' => 'SO-NOCOA-001',
        ]);
    }

    public function test_is_idempotent_for_same_reference(): void
    {
        $gl = app(GlPostingService::class);

        // Post pertama
        $result1 = $gl->postSalesOrder(
            tenantId: $this->tenant->id, userId: $this->user->id,
            soNumber: 'SO-IDEM-001', soId: 1,
            subtotal: 100000, taxAmount: 0, total: 100000,
            paymentType: 'credit',
        );

        // Post kedua dengan referensi sama
        $result2 = $gl->postSalesOrder(
            tenantId: $this->tenant->id, userId: $this->user->id,
            soNumber: 'SO-IDEM-001', soId: 1,
            subtotal: 100000, taxAmount: 0, total: 100000,
            paymentType: 'credit',
        );

        $this->assertTrue($result1->isSuccess());
        $this->assertTrue($result2->isSkipped(), 'Second post should be skipped (idempotent)');

        // Hanya 1 jurnal di DB
        $this->assertEquals(1, JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference', 'SO-IDEM-001')->count());
    }

    // ── GlPostingService — Invoice Payment ───────────────────────

    public function test_posts_invoice_payment_journal(): void
    {
        $gl = app(GlPostingService::class);

        $result = $gl->postInvoicePayment(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            invoiceNumber: 'INV-PAY-001',
            invoiceId: 1,
            amount: 300000,
            method: 'transfer',
        );

        $this->assertTrue($result->isSuccess());

        // Dr Bank (1102)
        $debitLine = $result->journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('1102', $debitLine->account->code);
        $this->assertEquals(300000, $debitLine->debit);

        // Cr Piutang (1103)
        $creditLine = $result->journal->lines->where('credit', '>', 0)->first();
        $this->assertEquals('1103', $creditLine->account->code);
        $this->assertEquals(300000, $creditLine->credit);
    }

    // ── GlPostingService — Purchase Order ────────────────────────

    public function test_posts_purchase_received_journal(): void
    {
        $gl = app(GlPostingService::class);

        $result = $gl->postPurchaseReceived(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            poNumber: 'PO-TEST-001',
            poId: 1,
            total: 400000,
            taxAmount: 0,
            paymentType: 'credit',
        );

        $this->assertTrue($result->isSuccess());

        // Dr Persediaan (1105)
        $debitLine = $result->journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('1105', $debitLine->account->code);

        // Cr Hutang Usaha (2101)
        $creditLine = $result->journal->lines->where('credit', '>', 0)->first();
        $this->assertEquals('2101', $creditLine->account->code);
    }

    // ── GlPostingService — Depreciation ──────────────────────────

    public function test_posts_depreciation_journal(): void
    {
        $gl = app(GlPostingService::class);

        $result = $gl->postDepreciation(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            period: '2026-03',
            totalAmount: 150000,
            assetLines: [
                ['asset_name' => 'Laptop', 'amount' => 100000],
                ['asset_name' => 'Printer', 'amount' => 50000],
            ],
        );

        $this->assertTrue($result->isSuccess());

        // Dr Beban Penyusutan (5204)
        $debitLine = $result->journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('5204', $debitLine->account->code);
        $this->assertEquals(150000, $debitLine->debit);

        // Cr Akumulasi Penyusutan (1202)
        $creditLine = $result->journal->lines->where('credit', '>', 0)->first();
        $this->assertEquals('1202', $creditLine->account->code);
        $this->assertEquals(150000, $creditLine->credit);
    }

    public function test_skips_depreciation_when_amount_is_zero(): void
    {
        $gl = app(GlPostingService::class);

        $result = $gl->postDepreciation(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            period: '2026-03',
            totalAmount: 0,
        );

        $this->assertTrue($result->isSkipped());
        $this->assertDatabaseMissing('journal_entries', ['tenant_id' => $this->tenant->id]);
    }

    // ── Journal balance invariant ─────────────────────────────────

    public function test_all_posted_journals_are_balanced(): void
    {
        $gl = app(GlPostingService::class);

        // Post beberapa jurnal berbeda
        $gl->postSalesOrder($this->tenant->id, $this->user->id, 'SO-BAL-001', 1, 500000, 0, 500000, paymentType: 'credit');
        $gl->postInvoicePayment($this->tenant->id, $this->user->id, 'INV-BAL-001', 1, 300000, 'transfer');
        $gl->postPurchaseReceived($this->tenant->id, $this->user->id, 'PO-BAL-001', 1, 200000, 0, 'credit');

        $journals = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('status', 'posted')
            ->with('lines')
            ->get();

        foreach ($journals as $journal) {
            $debit = round($journal->lines->sum('debit'), 2);
            $credit = round($journal->lines->sum('credit'), 2);
            $this->assertEquals($debit, $credit,
                "Journal {$journal->number} is not balanced: D={$debit} C={$credit}");
        }
    }
}
