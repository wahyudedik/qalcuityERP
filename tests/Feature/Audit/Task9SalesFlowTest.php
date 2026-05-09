<?php

namespace Tests\Feature\Audit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\TransactionStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 9.3: Test void/cancel invoice creates reversing journals and returns stock
 */
class Task9SalesFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Customer $customer;

    protected Warehouse $warehouse;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tenant
        $this->tenant = Tenant::factory()->create(['name' => 'Test Tenant']);

        // Create test user
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);

        // Create test customer
        $this->customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Customer',
        ]);

        // Create test warehouse
        $this->warehouse = Warehouse::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Warehouse',
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'price' => 100000,
        ]);

        // Set initial stock
        ProductStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function void_invoice_creates_reversing_journal_and_returns_stock()
    {
        // 1. Create Sales Order with stock deduction
        $so = SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'number' => 'SO-TEST-001',
            'status' => 'confirmed',
            'date' => today(),
            'subtotal' => 100000,
            'discount' => 0,
            'tax' => 11000,
            'total' => 111000,
            'payment_type' => 'cash',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'price' => 10000,
            'total' => 100000,
        ]);

        // Deduct stock (simulating SO creation)
        $stock = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $stock->decrement('quantity', 10);

        StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'quantity' => 10,
            'quantity_before' => 100,
            'quantity_after' => 90,
            'reference' => $so->number,
            'notes' => "Sales Order {$so->number}",
        ]);

        // 2. Create Invoice from SO
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'sales_order_id' => $so->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-TEST-001',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 0,
            'remaining_amount' => 111000,
            'status' => 'unpaid',
            'posting_status' => 'posted',
            'due_date' => today()->addDays(30),
        ]);

        // 3. Create original journal entry (simulating GL posting)
        $journal = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'number' => 'JE-TEST-001',
            'date' => today(),
            'description' => "Invoice {$invoice->number}",
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'status' => 'posted',
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);

        // Add journal lines (AR debit, Sales credit)
        $journal->lines()->create([
            'account_id' => 1, // AR account
            'debit' => 111000,
            'credit' => 0,
            'description' => 'Accounts Receivable',
        ]);

        $journal->lines()->create([
            'account_id' => 2, // Sales account
            'debit' => 0,
            'credit' => 111000,
            'description' => 'Sales Revenue',
        ]);

        // Verify initial state
        $this->assertEquals(90, $stock->fresh()->quantity);
        $this->assertEquals('posted', $invoice->posting_status);
        $this->assertEquals('posted', $journal->status);

        // 4. Void the invoice
        $stateMachine = app(TransactionStateMachine::class);
        $stateMachine->voidInvoice($invoice, $this->user->id, 'Test void reason');

        // 5. Verify invoice is voided
        $invoice->refresh();
        $this->assertEquals('voided', $invoice->posting_status);
        $this->assertEquals('voided', $invoice->status);
        $this->assertEquals('Test void reason', $invoice->cancel_reason);

        // 6. Verify reversing journal was created
        $reversalJournal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'reversal')
            ->where('reference_id', $journal->id)
            ->first();

        $this->assertNotNull($reversalJournal, 'Reversing journal should be created');
        $this->assertEquals('posted', $reversalJournal->status);
        $this->assertStringContainsString('Pembalik', $reversalJournal->description);

        // 7. Verify reversing journal is balanced and reversed
        $reversalLines = $reversalJournal->lines;
        $this->assertCount(2, $reversalLines);

        // Original: AR debit 111000, Sales credit 111000
        // Reversal: AR credit 111000, Sales debit 111000
        $arLine = $reversalLines->where('account_id', 1)->first();
        $salesLine = $reversalLines->where('account_id', 2)->first();

        $this->assertEquals(0, $arLine->debit);
        $this->assertEquals(111000, $arLine->credit);
        $this->assertEquals(111000, $salesLine->debit);
        $this->assertEquals(0, $salesLine->credit);

        // 8. Verify stock was returned
        $stock->refresh();
        $this->assertEquals(100, $stock->quantity, 'Stock should be returned to original quantity');

        // 9. Verify stock movement was logged
        $returnMovement = StockMovement::where('tenant_id', $this->tenant->id)
            ->where('product_id', $this->product->id)
            ->where('type', 'in')
            ->where('reference', 'VOID-'.$invoice->number)
            ->first();

        $this->assertNotNull($returnMovement, 'Stock return movement should be logged');
        $this->assertEquals(10, $returnMovement->quantity);
        $this->assertEquals(90, $returnMovement->quantity_before);
        $this->assertEquals(100, $returnMovement->quantity_after);
        $this->assertStringContainsString('void invoice', $returnMovement->notes);
    }

    /** @test */
    public function cancel_invoice_returns_stock()
    {
        // 1. Create Sales Order with stock deduction
        $so = SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'number' => 'SO-TEST-002',
            'status' => 'confirmed',
            'date' => today(),
            'subtotal' => 50000,
            'discount' => 0,
            'tax' => 5500,
            'total' => 55500,
            'payment_type' => 'cash',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'price' => 10000,
            'total' => 50000,
        ]);

        // Deduct stock
        $stock = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $stock->decrement('quantity', 5);

        // 2. Create Invoice (draft status)
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'sales_order_id' => $so->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-TEST-002',
            'subtotal_amount' => 50000,
            'tax_amount' => 5500,
            'total_amount' => 55500,
            'paid_amount' => 0,
            'remaining_amount' => 55500,
            'status' => 'unpaid',
            'posting_status' => 'draft',
            'due_date' => today()->addDays(30),
        ]);

        // Verify initial state
        $this->assertEquals(95, $stock->fresh()->quantity);

        // 3. Cancel the invoice
        $stateMachine = app(TransactionStateMachine::class);
        $stateMachine->cancelInvoice($invoice, $this->user->id, 'Test cancel reason');

        // 4. Verify invoice is cancelled
        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->posting_status);
        $this->assertEquals('cancelled', $invoice->status);

        // 5. Verify stock was returned
        $stock->refresh();
        $this->assertEquals(100, $stock->quantity, 'Stock should be returned');

        // 6. Verify stock movement was logged
        $returnMovement = StockMovement::where('tenant_id', $this->tenant->id)
            ->where('product_id', $this->product->id)
            ->where('type', 'in')
            ->where('reference', 'CANCEL-'.$invoice->number)
            ->first();

        $this->assertNotNull($returnMovement, 'Stock return movement should be logged');
        $this->assertEquals(5, $returnMovement->quantity);
        $this->assertEquals(95, $returnMovement->quantity_before);
        $this->assertEquals(100, $returnMovement->quantity_after);
    }

    /** @test */
    public function cannot_void_invoice_with_payment()
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-TEST-003',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 50000, // Has payment
            'remaining_amount' => 61000,
            'status' => 'partial',
            'posting_status' => 'posted',
            'due_date' => today()->addDays(30),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak bisa di-void karena sudah ada pembayaran');

        $stateMachine = app(TransactionStateMachine::class);
        $stateMachine->voidInvoice($invoice, $this->user->id, 'Should fail');
    }

    /** @test */
    public function cannot_cancel_invoice_with_payment()
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-TEST-004',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 50000, // Has payment
            'remaining_amount' => 61000,
            'status' => 'partial',
            'posting_status' => 'draft',
            'due_date' => today()->addDays(30),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak bisa dibatalkan karena sudah ada pembayaran');

        $stateMachine = app(TransactionStateMachine::class);
        $stateMachine->cancelInvoice($invoice, $this->user->id, 'Should fail');
    }
}
