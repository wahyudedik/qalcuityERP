<?php

namespace Tests\Feature\Preservation;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Services\GlPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Preservation Test — Sales Order → Invoice → Payment → Journal Flow
 *
 * Memverifikasi bahwa alur transaksi end-to-end yang SUDAH BENAR tidak berubah
 * setelah fix diterapkan. Test ini harus LULUS pada kode unfixed (baseline).
 *
 * Ini adalah NON-BUGGY case — alur normal yang harus tetap berfungsi.
 *
 * Validates: Requirements 3.7
 */
class TransactionFlowPreservationTest extends TestCase
{
    use RefreshDatabase;

    private $tenant;
    private $user;
    private $customer;
    private $warehouse;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant    = $this->createTenant();
        $this->user      = $this->createAdminUser($this->tenant);
        $this->customer  = $this->createCustomer($this->tenant->id);
        $this->warehouse = $this->createWarehouse($this->tenant->id);
        $this->product   = $this->createProduct($this->tenant->id, ['price_sell' => 200000]);
        $this->setStock($this->product->id, $this->warehouse->id, 100);
        $this->seedCoa($this->tenant->id);
    }

    // ── Requirement 3.7: Sales Order → Journal ────────────────────────────────

    /**
     * @test
     * Preservation 3.7: Membuat sales order menghasilkan jurnal GL otomatis
     *
     * Alur: SO dibuat → jurnal GL diposting otomatis.
     * Ini adalah behavior yang sudah benar dan tidak boleh berubah.
     * Validates: Requirements 3.7
     */
    public function test_creating_sales_order_creates_gl_journal(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('sales.store'), [
            'customer_id'  => $this->customer->id,
            'date'         => today()->toDateString(),
            'payment_type' => 'credit',
            'due_date'     => today()->addDays(30)->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items'        => [
                ['product_id' => $this->product->id, 'quantity' => 3, 'price' => 200000, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        $so = SalesOrder::where('tenant_id', $this->tenant->id)->latest()->first();
        $this->assertNotNull($so, "Sales order harus berhasil dibuat");

        // Jurnal GL harus diposting otomatis
        $this->assertJournalPosted($this->tenant->id, 'sales_order', $so->number);
    }

    /**
     * @test
     * Preservation 3.7: Jurnal sales order harus balance (debit = credit)
     *
     * Validates: Requirements 3.7
     */
    public function test_sales_order_journal_is_balanced(): void
    {
        $this->actingAs($this->user);

        $this->post(route('sales.store'), [
            'customer_id'  => $this->customer->id,
            'date'         => today()->toDateString(),
            'payment_type' => 'credit',
            'due_date'     => today()->addDays(30)->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items'        => [
                ['product_id' => $this->product->id, 'quantity' => 2, 'price' => 200000, 'discount' => 0],
            ],
        ]);

        $so = SalesOrder::where('tenant_id', $this->tenant->id)->latest()->first();

        $journal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'sales_order')
            ->where('reference', $so->number)
            ->with('lines')
            ->first();

        $this->assertNotNull($journal, "Jurnal harus ada");

        $totalDebit  = round($journal->lines->sum('debit'), 2);
        $totalCredit = round($journal->lines->sum('credit'), 2);

        $this->assertEquals(
            $totalDebit,
            $totalCredit,
            "Jurnal sales order harus balance: debit={$totalDebit}, credit={$totalCredit}"
        );
    }

    // ── Requirement 3.7: Invoice Payment → Journal ────────────────────────────

    /**
     * @test
     * Preservation 3.7: Pembayaran invoice menghasilkan jurnal GL otomatis
     *
     * Alur: Invoice dibayar → jurnal GL diposting otomatis.
     * Validates: Requirements 3.7
     */
    public function test_invoice_payment_creates_gl_journal(): void
    {
        // Buat SO terlebih dahulu
        $so = SalesOrder::create([
            'tenant_id'    => $this->tenant->id,
            'customer_id'  => $this->customer->id,
            'user_id'      => $this->user->id,
            'number'       => 'SO-PRSV-001',
            'status'       => 'confirmed',
            'date'         => today(),
            'subtotal'     => 400000,
            'discount'     => 0,
            'tax_amount'   => 0,
            'tax'          => 0,
            'total'        => 400000,
            'payment_type' => 'credit',
            'due_date'     => today()->addDays(30),
            'source'       => 'order',
        ]);

        $invoice = Invoice::create([
            'tenant_id'        => $this->tenant->id,
            'number'           => 'INV-PRSV-001',
            'customer_id'      => $this->customer->id,
            'sales_order_id'   => $so->id,
            'subtotal_amount'  => 400000,
            'tax_amount'       => 0,
            'total_amount'     => 400000,
            'paid_amount'      => 0,
            'remaining_amount' => 400000,
            'status'           => 'unpaid',
            'due_date'         => today()->addDays(30),
            'currency_code'    => 'IDR',
            'currency_rate'    => 1,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('invoices.payment', $invoice), [
            'amount' => 400000,
            'method' => 'transfer',
        ]);

        $response->assertRedirect();

        // Jurnal pembayaran harus diposting
        $journal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'invoice_payment')
            ->where('status', 'posted')
            ->first();

        $this->assertNotNull($journal, "Jurnal pembayaran invoice harus diposting otomatis");
    }

    /**
     * @test
     * Preservation 3.7: Jurnal pembayaran invoice harus balance
     *
     * Validates: Requirements 3.7
     */
    public function test_invoice_payment_journal_is_balanced(): void
    {
        $so = SalesOrder::create([
            'tenant_id'    => $this->tenant->id,
            'customer_id'  => $this->customer->id,
            'user_id'      => $this->user->id,
            'number'       => 'SO-PRSV-002',
            'status'       => 'confirmed',
            'date'         => today(),
            'subtotal'     => 300000,
            'discount'     => 0,
            'tax_amount'   => 0,
            'tax'          => 0,
            'total'        => 300000,
            'payment_type' => 'credit',
            'due_date'     => today()->addDays(30),
            'source'       => 'order',
        ]);

        $invoice = Invoice::create([
            'tenant_id'        => $this->tenant->id,
            'number'           => 'INV-PRSV-002',
            'customer_id'      => $this->customer->id,
            'sales_order_id'   => $so->id,
            'subtotal_amount'  => 300000,
            'tax_amount'       => 0,
            'total_amount'     => 300000,
            'paid_amount'      => 0,
            'remaining_amount' => 300000,
            'status'           => 'unpaid',
            'due_date'         => today()->addDays(30),
            'currency_code'    => 'IDR',
            'currency_rate'    => 1,
        ]);

        $this->actingAs($this->user);

        $this->post(route('invoices.payment', $invoice), [
            'amount' => 300000,
            'method' => 'transfer',
        ]);

        $journal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'invoice_payment')
            ->with('lines')
            ->first();

        $this->assertNotNull($journal);

        $totalDebit  = round($journal->lines->sum('debit'), 2);
        $totalCredit = round($journal->lines->sum('credit'), 2);

        $this->assertEquals(
            $totalDebit,
            $totalCredit,
            "Jurnal pembayaran invoice harus balance"
        );
    }

    // ── Requirement 3.7: Full end-to-end flow ─────────────────────────────────

    /**
     * @test
     * Preservation 3.7: Alur lengkap SO → Invoice → Payment → Journal berfungsi
     *
     * Validates: Requirements 3.7
     */
    public function test_full_so_invoice_payment_journal_flow(): void
    {
        $this->actingAs($this->user);

        // Step 1: Buat Sales Order
        $this->post(route('sales.store'), [
            'customer_id'  => $this->customer->id,
            'date'         => today()->toDateString(),
            'payment_type' => 'credit',
            'due_date'     => today()->addDays(30)->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items'        => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'price' => 200000, 'discount' => 0],
            ],
        ]);

        $so = SalesOrder::where('tenant_id', $this->tenant->id)->latest()->first();
        $this->assertNotNull($so, "Step 1: Sales order harus berhasil dibuat");

        // Step 2: Verifikasi jurnal SO
        $soJournal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'sales_order')
            ->where('reference', $so->number)
            ->first();
        $this->assertNotNull($soJournal, "Step 2: Jurnal SO harus diposting otomatis");

        // Step 3: Buat Invoice dari SO
        $invoice = Invoice::create([
            'tenant_id'        => $this->tenant->id,
            'number'           => 'INV-FLOW-001',
            'customer_id'      => $this->customer->id,
            'sales_order_id'   => $so->id,
            'subtotal_amount'  => 1000000,
            'tax_amount'       => 0,
            'total_amount'     => 1000000,
            'paid_amount'      => 0,
            'remaining_amount' => 1000000,
            'status'           => 'unpaid',
            'due_date'         => today()->addDays(30),
            'currency_code'    => 'IDR',
            'currency_rate'    => 1,
        ]);

        // Step 4: Bayar Invoice
        $this->post(route('invoices.payment', $invoice), [
            'amount' => 1000000,
            'method' => 'transfer',
        ]);

        // Step 5: Verifikasi jurnal pembayaran
        $paymentJournal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'invoice_payment')
            ->where('status', 'posted')
            ->first();
        $this->assertNotNull($paymentJournal, "Step 5: Jurnal pembayaran harus diposting otomatis");

        // Step 6: Verifikasi invoice lunas
        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'status' => 'paid',
        ]);

        // Step 7: Verifikasi semua jurnal balance
        $allJournals = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('status', 'posted')
            ->with('lines')
            ->get();

        foreach ($allJournals as $journal) {
            $debit  = round($journal->lines->sum('debit'), 2);
            $credit = round($journal->lines->sum('credit'), 2);
            $this->assertEquals(
                $debit,
                $credit,
                "Jurnal {$journal->number} harus balance: D={$debit} C={$credit}"
            );
        }
    }

    // ── Requirement 3.7: GlPostingService direct call ─────────────────────────

    /**
     * @test
     * Preservation 3.7: GlPostingService.postSalesOrder berhasil untuk transaksi normal
     *
     * Validates: Requirements 3.7
     */
    public function test_gl_posting_service_post_sales_order_succeeds(): void
    {
        $this->actingAs($this->user);

        $gl = app(GlPostingService::class);

        $result = $gl->postSalesOrder(
            tenantId:    $this->tenant->id,
            userId:      $this->user->id,
            soNumber:    'SO-PRSV-GL-001',
            soId:        999,
            subtotal:    500000,
            taxAmount:   0,
            total:       500000,
            paymentType: 'credit',
            date:        today()->toDateString(),
        );

        $this->assertTrue(
            $result->isSuccess(),
            "GlPostingService.postSalesOrder harus berhasil untuk transaksi normal"
        );

        $this->assertNotNull($result->journal, "Jurnal harus dibuat");
        $this->assertEquals('posted', $result->journal->status);
    }

    /**
     * @test
     * Preservation 3.7: GlPostingService.postInvoicePayment berhasil untuk transaksi normal
     *
     * Validates: Requirements 3.7
     */
    public function test_gl_posting_service_post_invoice_payment_succeeds(): void
    {
        $this->actingAs($this->user);

        $gl = app(GlPostingService::class);

        $result = $gl->postInvoicePayment(
            tenantId:      $this->tenant->id,
            userId:        $this->user->id,
            invoiceNumber: 'INV-PRSV-GL-001',
            invoiceId:     999,
            amount:        300000,
            method:        'transfer',
            date:          today()->toDateString(),
        );

        $this->assertTrue(
            $result->isSuccess(),
            "GlPostingService.postInvoicePayment harus berhasil untuk transaksi normal"
        );

        $this->assertNotNull($result->journal);
        $this->assertEquals('posted', $result->journal->status);
    }
}
