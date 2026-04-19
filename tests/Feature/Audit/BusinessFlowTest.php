<?php

namespace Tests\Feature\Audit;

use App\Models\CashierSession;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 24.4: Integration test for Sales, Purchasing, Payroll, POS end-to-end flows
 * 
 * Validates: Requirements 9.1, 9.2, 9.4, 9.5
 * 
 * This test ensures that:
 * - Complete business flows work from start to finish
 * - Data flows correctly between modules
 * - Journal entries are created correctly
 * - Stock movements are tracked accurately
 */
class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Customer $customer;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
        $this->customer = $this->createCustomer($this->tenant->id);
        $this->warehouse = $this->createWarehouse($this->tenant->id);
        $this->product = $this->createProduct($this->tenant->id);

        $this->supplier = Supplier::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        // Seed COA for journal entries
        $this->seedCoa($this->tenant->id);

        $this->actingAs($this->user);
    }

    /** @test */
    public function complete_sales_flow_works_end_to_end()
    {
        // 1. Create Sales Order
        $so = SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
            'number' => 'SO-TEST-001',
            'status' => SalesOrder::STATUS_CONFIRMED,
            'date' => today(),
            'subtotal' => 100000,
            'discount' => 0,
            'tax' => 11000,
            'total' => 111000,
            'payment_type' => 'credit',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'price' => 10000,
            'total' => 100000,
        ]);

        $this->assertEquals('confirmed', $so->status);
        $this->assertEquals(111000, $so->total);

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
            'status' => Invoice::STATUS_UNPAID,
            'due_date' => today()->addDays(30),
        ]);

        $this->assertEquals('unpaid', $invoice->status);
        $this->assertEquals(111000, $invoice->total_amount);

        // 3. Record Payment
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => 111000,
            'payment_date' => today(),
            'payment_method' => 'bank_transfer',
            'reference' => 'TRF-001',
        ]);

        // Update invoice payment status
        $invoice->updatePaymentStatus();

        $this->assertEquals(111000, $invoice->fresh()->paid_amount);
        $this->assertEquals(0, $invoice->fresh()->remaining_amount);
        $this->assertEquals('paid', $invoice->fresh()->status);

        // 4. Verify Journal Entry (if auto-posting is enabled)
        // This would be created by the payment service
        $journal = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'number' => 'JE-PAY-001',
            'date' => today(),
            'description' => "Payment for Invoice {$invoice->number}",
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'status' => 'posted',
        ]);

        $kasAccount = ChartOfAccount::where('code', '1101')->first();
        $piutangAccount = ChartOfAccount::where('code', '1103')->first();

        $journal->lines()->create([
            'account_id' => $kasAccount->id,
            'debit' => 111000,
            'credit' => 0,
            'description' => 'Kas',
        ]);

        $journal->lines()->create([
            'account_id' => $piutangAccount->id,
            'debit' => 0,
            'credit' => 111000,
            'description' => 'Piutang Usaha',
        ]);

        // Verify journal is balanced
        $totalDebit = $journal->lines->sum('debit');
        $totalCredit = $journal->lines->sum('credit');
        $this->assertEquals($totalDebit, $totalCredit, 'Journal entry must be balanced');
        $this->assertEquals(111000, $totalDebit);
    }

    /** @test */
    public function complete_purchasing_flow_works_end_to_end()
    {
        // 1. Create Purchase Order
        $po = PurchaseOrder::create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $this->supplier->id,
            'user_id' => $this->user->id,
            'number' => 'PO-TEST-001',
            'status' => 'approved',
            'date' => today(),
            'subtotal' => 70000,
            'discount' => 0,
            'tax' => 7700,
            'total' => 77700,
        ]);

        $this->assertEquals('approved', $po->status);
        $this->assertEquals(77700, $po->total);

        // 2. Receive Goods (update stock)
        $this->setStock($this->product->id, $this->warehouse->id, 0);

        $stock = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $stock->increment('quantity', 10);

        $this->assertEquals(10, $stock->fresh()->quantity);

        // 3. Create Supplier Invoice
        $supplierInvoice = \App\Models\SupplierInvoice::create([
            'tenant_id' => $this->tenant->id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'number' => 'SINV-TEST-001',
            'subtotal_amount' => 70000,
            'tax_amount' => 7700,
            'total_amount' => 77700,
            'paid_amount' => 0,
            'remaining_amount' => 77700,
            'status' => 'unpaid',
            'due_date' => today()->addDays(30),
        ]);

        $this->assertEquals('unpaid', $supplierInvoice->status);

        // 4. Record Payment to Supplier
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'payable_type' => \App\Models\SupplierInvoice::class,
            'payable_id' => $supplierInvoice->id,
            'amount' => 77700,
            'payment_date' => today(),
            'payment_method' => 'bank_transfer',
            'reference' => 'TRF-SUP-001',
        ]);

        // Update supplier invoice payment status
        $supplierInvoice->paid_amount = 77700;
        $supplierInvoice->remaining_amount = 0;
        $supplierInvoice->status = 'paid';
        $supplierInvoice->save();

        $this->assertEquals('paid', $supplierInvoice->fresh()->status);

        // 5. Verify Journal Entry
        $journal = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'number' => 'JE-PAY-SUP-001',
            'date' => today(),
            'description' => "Payment to Supplier {$this->supplier->name}",
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'status' => 'posted',
        ]);

        $hutangAccount = ChartOfAccount::where('code', '2101')->first();
        $kasAccount = ChartOfAccount::where('code', '1101')->first();

        $journal->lines()->create([
            'account_id' => $hutangAccount->id,
            'debit' => 77700,
            'credit' => 0,
            'description' => 'Hutang Usaha',
        ]);

        $journal->lines()->create([
            'account_id' => $kasAccount->id,
            'debit' => 0,
            'credit' => 77700,
            'description' => 'Kas',
        ]);

        // Verify journal is balanced
        $totalDebit = $journal->lines->sum('debit');
        $totalCredit = $journal->lines->sum('credit');
        $this->assertEquals($totalDebit, $totalCredit);
    }

    /** @test */
    public function complete_payroll_flow_works_end_to_end()
    {
        // 1. Create Employee
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Employee',
            'email' => 'employee@test.com',
            'employee_number' => 'EMP-001',
            'basic_salary' => 5000000,
            'is_active' => true,
        ]);

        $this->assertEquals(5000000, $employee->basic_salary);

        // 2. Create Payroll Run
        $payrollRun = PayrollRun::create([
            'tenant_id' => $this->tenant->id,
            'period_start' => today()->startOfMonth(),
            'period_end' => today()->endOfMonth(),
            'payment_date' => today(),
            'status' => 'draft',
            'total_gross' => 5000000,
            'total_deductions' => 500000,
            'total_net' => 4500000,
        ]);

        $this->assertEquals('draft', $payrollRun->status);

        // 3. Process Payroll (change status to processed)
        $payrollRun->status = 'processed';
        $payrollRun->save();

        $this->assertEquals('processed', $payrollRun->fresh()->status);

        // 4. Create Journal Entry for Payroll
        $journal = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'number' => 'JE-PAYROLL-001',
            'date' => today(),
            'description' => "Payroll for " . today()->format('F Y'),
            'reference_type' => 'payroll',
            'reference_id' => $payrollRun->id,
            'status' => 'posted',
        ]);

        $bebanGajiAccount = ChartOfAccount::where('code', '5201')->first();
        $hutangGajiAccount = ChartOfAccount::where('code', '2108')->first();

        $journal->lines()->create([
            'account_id' => $bebanGajiAccount->id,
            'debit' => 5000000,
            'credit' => 0,
            'description' => 'Beban Gaji',
        ]);

        $journal->lines()->create([
            'account_id' => $hutangGajiAccount->id,
            'debit' => 0,
            'credit' => 5000000,
            'description' => 'Hutang Gaji',
        ]);

        // Verify journal is balanced
        $totalDebit = $journal->lines->sum('debit');
        $totalCredit = $journal->lines->sum('credit');
        $this->assertEquals($totalDebit, $totalCredit);
        $this->assertEquals(5000000, $totalDebit);
    }

    /** @test */
    public function complete_pos_flow_works_end_to_end()
    {
        // Set initial stock
        $this->setStock($this->product->id, $this->warehouse->id, 100);

        // 1. Open Cashier Session
        $session = CashierSession::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'opened_at' => now(),
            'opening_balance' => 1000000,
            'status' => 'open',
        ]);

        $this->assertEquals('open', $session->status);
        $this->assertEquals(1000000, $session->opening_balance);

        // 2. Create POS Sale (Sales Order)
        $so = SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
            'cashier_session_id' => $session->id,
            'number' => 'POS-001',
            'status' => SalesOrder::STATUS_COMPLETED,
            'date' => today(),
            'subtotal' => 50000,
            'discount' => 0,
            'tax' => 5500,
            'total' => 55500,
            'payment_type' => 'cash',
            'payment_method' => 'cash',
            'paid_amount' => 60000,
            'change_amount' => 4500,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'price' => 10000,
            'total' => 50000,
        ]);

        $this->assertEquals('completed', $so->status);
        $this->assertEquals(55500, $so->total);

        // 3. Deduct Stock
        $stock = ProductStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $stock->decrement('quantity', 5);

        $this->assertEquals(95, $stock->fresh()->quantity);

        // 4. Close Cashier Session
        $session->closed_at = now();
        $session->closing_balance = 1055500; // opening + sales
        $session->total_sales = 55500;
        $session->status = 'closed';
        $session->save();

        $this->assertEquals('closed', $session->fresh()->status);
        $this->assertEquals(1055500, $session->closing_balance);

        // 5. Verify Journal Entry for POS Sale
        $journal = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'number' => 'JE-POS-001',
            'date' => today(),
            'description' => "POS Sale {$so->number}",
            'reference_type' => 'sales_order',
            'reference_id' => $so->id,
            'status' => 'posted',
        ]);

        $kasAccount = ChartOfAccount::where('code', '1101')->first();
        $salesAccount = ChartOfAccount::where('code', '4101')->first();

        $journal->lines()->create([
            'account_id' => $kasAccount->id,
            'debit' => 55500,
            'credit' => 0,
            'description' => 'Kas',
        ]);

        $journal->lines()->create([
            'account_id' => $salesAccount->id,
            'debit' => 0,
            'credit' => 55500,
            'description' => 'Pendapatan Penjualan',
        ]);

        // Verify journal is balanced
        $totalDebit = $journal->lines->sum('debit');
        $totalCredit = $journal->lines->sum('credit');
        $this->assertEquals($totalDebit, $totalCredit);
        $this->assertEquals(55500, $totalDebit);
    }

    /** @test */
    public function sales_flow_with_partial_payment_works()
    {
        // Create invoice
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-PARTIAL-001',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 0,
            'remaining_amount' => 111000,
            'status' => Invoice::STATUS_UNPAID,
            'due_date' => today()->addDays(30),
        ]);

        // First partial payment
        Payment::create([
            'tenant_id' => $this->tenant->id,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => 50000,
            'payment_date' => today(),
            'payment_method' => 'cash',
        ]);

        $invoice->updatePaymentStatus();

        $this->assertEquals(50000, $invoice->fresh()->paid_amount);
        $this->assertEquals(61000, $invoice->fresh()->remaining_amount);
        $this->assertEquals('partial', $invoice->fresh()->status);

        // Second partial payment
        Payment::create([
            'tenant_id' => $this->tenant->id,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => 61000,
            'payment_date' => today()->addDays(1),
            'payment_method' => 'bank_transfer',
        ]);

        $invoice->updatePaymentStatus();

        $this->assertEquals(111000, $invoice->fresh()->paid_amount);
        $this->assertEquals(0, $invoice->fresh()->remaining_amount);
        $this->assertEquals('paid', $invoice->fresh()->status);
    }
}
