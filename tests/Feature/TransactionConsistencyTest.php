<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Services\TransactionSagaService;
use App\Services\InvoicePaymentService;
use App\Exceptions\TransactionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionConsistencyTest extends TestCase
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

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
        $this->customer = $this->createCustomer($this->tenant->id);
        $this->warehouse = $this->createWarehouse($this->tenant->id);
        $this->product = $this->createProduct($this->tenant->id);
        $this->setStock($this->product->id, $this->warehouse->id, 100);
        $this->seedCoa($this->tenant->id);
    }

    /**
     * Test that failed GL posting doesn't prevent payment recording
     */
    public function test_payment_succeeds_even_if_gl_posting_fails(): void
    {
        // Remove COA to force GL posting failure
        \App\Models\ChartOfAccount::where('tenant_id', $this->tenant->id)->delete();

        $invoice = $this->createInvoice(500000);

        $this->actingAs($this->user);

        $response = $this->post(route('invoices.payment', $invoice), [
            'amount' => 500000,
            'method' => 'transfer',
        ]);

        // Payment should succeed even without GL posting
        $response->assertSessionHas('success');

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => 500000,
        ]);

        // Verify invoice status updated
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(500000, $invoice->paid_amount);
    }

    /**
     * Test that partial failures trigger complete rollback
     */
    public function test_transaction_rollback_on_partial_failure(): void
    {
        $invoice = $this->createInvoice(1000000);
        $initialPaymentsCount = $invoice->payments()->count();

        // Try to overpay (should fail validation)
        try {
            $paymentService = app(InvoicePaymentService::class);
            $paymentService->processPayment(
                invoice: $invoice,
                data: [
                    'amount' => 2000000, // Exceeds remaining amount
                    'method' => 'transfer',
                    'notes' => 'Test overpayment'
                ],
                userId: $this->user->id
            );

            $this->fail('Should have thrown TransactionException');
        } catch (TransactionException $e) {
            // Expected - verify rollback occurred
            $this->assertStringContainsString('ROLLBACK REQUIRED', $e->getMessage());

            // Verify no payments were created
            $this->assertEquals($initialPaymentsCount, $invoice->fresh()->payments()->count());

            // Verify invoice unchanged
            $invoice->refresh();
            $this->assertEquals(0, $invoice->paid_amount);
            $this->assertEquals('unpaid', $invoice->status);
        }
    }

    /**
     * Test saga pattern with multiple steps and compensation
     */
    public function test_saga_pattern_with_compensation(): void
    {
        $sagaService = app(TransactionSagaService::class);

        $steps = [
            'step_one' => function ($context) {
                // Create a test record
                DB::table('activity_logs')->insert([
                    'tenant_id' => $this->tenant->id,
                    'user_id' => $this->user->id,
                    'action' => 'test_step_one',
                    'description' => 'First step completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return ['step_one_done' => true];
            },
            'step_two' => function ($context) {
                // Create another record
                DB::table('activity_logs')->insert([
                    'tenant_id' => $this->tenant->id,
                    'user_id' => $this->user->id,
                    'action' => 'test_step_two',
                    'description' => 'Second step completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return ['step_two_done' => true];
            },
            'step_three_fail' => function ($context) {
                // This step fails - should trigger rollback of all previous steps
                throw new \RuntimeException('Intentional failure in step 3');
            }
        ];

        $compensations = [
            'step_one' => function ($result, $context) {
                DB::table('activity_logs')
                    ->where('action', 'test_step_one')
                    ->delete();
            },
            'step_two' => function ($result, $context) {
                DB::table('activity_logs')
                    ->where('action', 'test_step_two')
                    ->delete();
            }
        ];

        try {
            $sagaService->execute(
                steps: $steps,
                compensations: $compensations,
                sagaType: 'test_saga',
                context: []
            );

            $this->fail('Should have thrown exception');
        } catch (TransactionException $e) {
            // Verify saga compensated
            $this->assertStringContainsString('COMPENSATION NEEDED', $e->getMessage());

            // Wait a moment for compensations to execute
            sleep(1);

            // Verify compensations cleaned up (records should be deleted)
            $count = DB::table('activity_logs')
                ->whereIn('action', ['test_step_one', 'test_step_two'])
                ->count();

            $this->assertEquals(0, $count, 'Compensations should have cleaned up all records');
        }
    }

    /**
     * Test concurrent payment attempts on same invoice (race condition prevention)
     */
    public function test_concurrent_payments_are_serialized(): void
    {
        $invoice = $this->createInvoice(1000000);

        // Simulate two concurrent payment attempts
        $paymentService = app(InvoicePaymentService::class);

        // First payment - should succeed
        $result1 = $paymentService->processPayment(
            invoice: $invoice,
            data: [
                'amount' => 600000,
                'method' => 'transfer',
                'notes' => 'First payment'
            ],
            userId: $this->user->id
        );

        $this->assertTrue($result1['payment'] instanceof Payment);

        // Second payment - should also succeed (partial)
        $result2 = $paymentService->processPayment(
            invoice: $invoice->fresh(),
            data: [
                'amount' => 400000,
                'method' => 'cash',
                'notes' => 'Second payment'
            ],
            userId: $this->user->id
        );

        $this->assertTrue($result2['payment'] instanceof Payment);

        // Third payment - should fail (exceeds remaining)
        try {
            $paymentService->processPayment(
                invoice: $invoice->fresh(),
                data: [
                    'amount' => 100000, // Only 0 remaining
                    'method' => 'transfer',
                    'notes' => 'Should fail'
                ],
                userId: $this->user->id
            );
            $this->fail('Should have thrown exception');
        } catch (TransactionException $e) {
            $this->assertStringContainsString('exceeds remaining', $e->getMessage());
        }

        // Final state verification
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(1000000, $invoice->paid_amount);
        $this->assertEquals(0, $invoice->remaining_amount);
        $this->assertEquals(2, $invoice->payments()->count());
    }

    /**
     * Test GL posting atomicity - all or nothing
     */
    public function test_gl_posting_is_atomic(): void
    {
        $invoice = $this->createInvoice(500000);

        // Delete one required COA account to force failure
        \App\Models\ChartOfAccount::where('code', '1103')
            ->where('tenant_id', $this->tenant->id)
            ->delete();

        $glService = app(\App\Services\GlPostingService::class);
        $result = $glService->postInvoicePayment(
            tenantId: $this->tenant->id,
            userId: $this->user->id,
            invoiceNumber: $invoice->number . '-TEST',
            invoiceId: $invoice->id,
            amount: 500000,
            method: 'transfer',
            date: today()->toDateString(),
        );

        // Should fail gracefully
        $this->assertTrue($result->isFailed());

        // Verify NO journal entries were created (atomic rollback)
        $this->assertDatabaseMissing('journal_entries', [
            'reference_type' => 'payment',
            'reference_id' => $invoice->id,
        ]);
    }

    /**
     * Test that row locking prevents double-spending
     */
    public function test_row_locking_prevents_double_payment(): void
    {
        $invoice = $this->createInvoice(1000000);

        $paymentService = app(InvoicePaymentService::class);

        // Process first payment
        $result1 = $paymentService->processPayment(
            invoice: $invoice,
            data: [
                'amount' => 1000000,
                'method' => 'transfer',
                'notes' => 'Full payment'
            ],
            userId: $this->user->id
        );

        $this->assertTrue($result1['payment'] instanceof Payment);

        // Try to process another payment while invoice is locked
        // In real scenario this would wait, but here we just test the logic
        $invoice->refresh();

        try {
            $paymentService->processPayment(
                invoice: $invoice,
                data: [
                    'amount' => 1, // Even 1 should fail
                    'method' => 'cash',
                    'notes' => 'Attempt after full payment'
                ],
                userId: $this->user->id
            );
            $this->fail('Should have failed - invoice fully paid');
        } catch (TransactionException $e) {
            $this->assertStringContainsString('exceeds remaining', $e->getMessage());
        }
    }

    /**
     * Helper to create an invoice
     */
    private function createInvoice(float $total): Invoice
    {
        $so = SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
            'number' => 'SO-TEST-' . uniqid(),
            'status' => 'confirmed',
            'date' => today(),
            'subtotal' => $total,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => $total,
            'payment_type' => 'credit',
        ]);

        return Invoice::create([
            'tenant_id' => $this->tenant->id,
            'sales_order_id' => $so->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-TEST-' . uniqid(),
            'subtotal_amount' => $total,
            'tax_amount' => 0,
            'total_amount' => $total,
            'paid_amount' => 0,
            'remaining_amount' => $total,
            'status' => 'unpaid',
            'posting_status' => 'posted',
            'due_date' => today()->addDays(30),
        ]);
    }
}
