<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\SalesOrder;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
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
        $this->product   = $this->createProduct($this->tenant->id);
        $this->setStock($this->product->id, $this->warehouse->id, 100);
        $this->seedCoa($this->tenant->id);
    }

    private function createInvoice(float $total = 500000): Invoice
    {
        // Create a SO first since sales_order_id is NOT NULL
        $so = SalesOrder::create([
            'tenant_id'   => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id'     => $this->user->id,
            'number'      => 'SO-TEST-' . uniqid(),
            'status'      => 'confirmed',
            'date'        => today(),
            'subtotal'    => $total,
            'discount'    => 0,
            'tax_amount'  => 0,
            'tax'         => 0,
            'total'       => $total,
            'payment_type'=> 'credit',
            'due_date'    => today()->addDays(30),
            'source'      => 'order',
        ]);

        return Invoice::create([
            'tenant_id'        => $this->tenant->id,
            'number'           => 'INV-TEST-' . uniqid(),
            'customer_id'      => $this->customer->id,
            'sales_order_id'   => $so->id,
            'subtotal_amount'  => $total,
            'tax_amount'       => 0,
            'total_amount'     => $total,
            'paid_amount'      => 0,
            'remaining_amount' => $total,
            'status'           => 'unpaid',
            'due_date'         => today()->addDays(30),
            'currency_code'    => 'IDR',
            'currency_rate'    => 1,
        ]);
    }

    // ── Payment recording ─────────────────────────────────────────

    public function test_records_payment_and_updates_invoice_status(): void
    {
        $invoice = $this->createInvoice(500000);

        $this->actingAs($this->user);

        $response = $this->post(route('invoices.payment', $invoice), [
            'amount' => 500000,
            'method' => 'transfer',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Invoice status → paid
        $this->assertDatabaseHas('invoices', [
            'id'           => $invoice->id,
            'status'       => 'paid',
            'paid_amount'  => 500000,
            'remaining_amount' => 0,
        ]);

        // Payment record tersimpan
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $this->tenant->id,
            'amount'    => 500000,
        ]);
    }

    public function test_posts_gl_journal_on_invoice_payment(): void
    {
        $invoice = $this->createInvoice(300000);

        $this->actingAs($this->user);

        $this->post(route('invoices.payment', $invoice), [
            'amount' => 300000,
            'method' => 'transfer',
        ]);

        // Jurnal pembayaran diposting: Dr Bank / Cr Piutang
        $journal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'invoice_payment')
            ->where('status', 'posted')
            ->with('lines.account')
            ->first();

        $this->assertNotNull($journal, 'GL journal should be created on payment');

        // Balance check
        $this->assertEquals(
            round($journal->lines->sum('debit'), 2),
            round($journal->lines->sum('credit'), 2),
            'Payment journal must be balanced'
        );

        // Dr Bank (1102) karena method = transfer
        $debitLine = $journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('1102', $debitLine?->account?->code, 'Transfer payment should debit Bank (1102)');

        // Cr Piutang (1103)
        $creditLine = $journal->lines->where('credit', '>', 0)->first();
        $this->assertEquals('1103', $creditLine?->account?->code, 'Payment should credit Piutang (1103)');
    }

    public function test_posts_gl_journal_with_cash_payment_method(): void
    {
        $invoice = $this->createInvoice(200000);

        $this->actingAs($this->user);

        $this->post(route('invoices.payment', $invoice), [
            'amount' => 200000,
            'method' => 'cash',
        ]);

        $journal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'invoice_payment')
            ->with('lines.account')
            ->first();

        // Dr Kas (1101) karena method = cash
        $debitLine = $journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('1101', $debitLine?->account?->code, 'Cash payment should debit Kas (1101)');
    }

    public function test_handles_partial_payment_correctly(): void
    {
        $invoice = $this->createInvoice(1000000);

        $this->actingAs($this->user);

        // Bayar sebagian: 400k dari 1jt
        $this->post(route('invoices.payment', $invoice), [
            'amount' => 400000,
            'method' => 'transfer',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id'               => $invoice->id,
            'status'           => 'partial',
            'paid_amount'      => 400000,
            'remaining_amount' => 600000,
        ]);
    }

    public function test_still_records_payment_even_when_coa_missing(): void
    {
        \App\Models\ChartOfAccount::where('tenant_id', $this->tenant->id)->delete();

        $invoice = $this->createInvoice(100000);

        $this->actingAs($this->user);

        $response = $this->post(route('invoices.payment', $invoice), [
            'amount' => 100000,
            'method' => 'transfer',
        ]);

        // Payment tetap berhasil
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);

        // Tapi ada warning
        $response->assertSessionHas('warning');

        // Tidak ada jurnal
        $this->assertDatabaseMissing('journal_entries', [
            'tenant_id'      => $this->tenant->id,
            'reference_type' => 'invoice_payment',
        ]);
    }

    public function test_rejects_payment_exceeding_remaining_amount(): void
    {
        $invoice = $this->createInvoice(100000);

        $this->actingAs($this->user);

        $response = $this->post(route('invoices.payment', $invoice), [
            'amount' => 999999, // lebih dari remaining
            'method' => 'transfer',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'unpaid']);
    }

    public function test_prevents_accessing_other_tenant_invoice(): void
    {
        $otherTenant    = $this->createTenant(['slug' => 'other-' . uniqid()]);
        $otherUser      = $this->createAdminUser($otherTenant);
        $otherCustomer  = $this->createCustomer($otherTenant->id);
        $otherWarehouse = $this->createWarehouse($otherTenant->id);

        $otherSo = SalesOrder::create([
            'tenant_id'   => $otherTenant->id,
            'customer_id' => $otherCustomer->id,
            'user_id'     => $otherUser->id,
            'number'      => 'SO-OTHER-001',
            'status'      => 'confirmed',
            'date'        => today(),
            'subtotal'    => 100000,
            'discount'    => 0,
            'tax_amount'  => 0,
            'tax'         => 0,
            'total'       => 100000,
            'payment_type'=> 'credit',
            'due_date'    => today()->addDays(30),
            'source'      => 'order',
        ]);

        $otherInvoice  = Invoice::create([
            'tenant_id'        => $otherTenant->id,
            'number'           => 'INV-OTHER-001',
            'customer_id'      => $otherCustomer->id,
            'sales_order_id'   => $otherSo->id,
            'subtotal_amount'  => 100000,
            'tax_amount'       => 0,
            'total_amount'     => 100000,
            'paid_amount'      => 0,
            'remaining_amount' => 100000,
            'status'           => 'unpaid',
            'due_date'         => today()->addDays(30),
            'currency_code'    => 'IDR',
            'currency_rate'    => 1,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('invoices.payment', $otherInvoice), [
            'amount' => 100000,
            'method' => 'transfer',
        ]);

        $response->assertStatus(403);
    }
}
