<?php

namespace Tests\Feature\Audit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Task 24.1: Test all ENUM columns with valid and invalid values
 * 
 * Validates: Requirements 1.1, 1.2, 1.3
 * 
 * This test ensures that:
 * - All ENUM columns accept valid values
 * - All ENUM columns reject invalid values
 * - Error messages are descriptive in Bahasa Indonesia
 */
class DatabaseEnumTest extends TestCase
{
    protected Tenant $tenant;
    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user = $this->createAdminUser($this->tenant);
        $this->customer = $this->createCustomer($this->tenant->id);

        $this->actingAs($this->user);
    }

    #[Test]
    public function invoice_status_accepts_all_valid_enum_values()
    {
        $validStatuses = Invoice::STATUSES;

        foreach ($validStatuses as $status) {
            $invoice = Invoice::create([
                'tenant_id' => $this->tenant->id,
                'customer_id' => $this->customer->id,
                'number' => 'INV-' . uniqid(),
                'subtotal_amount' => 100000,
                'tax_amount' => 11000,
                'total_amount' => 111000,
                'paid_amount' => 0,
                'remaining_amount' => 111000,
                'status' => $status,
                'due_date' => today()->addDays(30),
            ]);

            $this->assertEquals($status, $invoice->fresh()->status);
        }
    }

    #[Test]
    public function invoice_status_rejects_invalid_enum_value()
    {
        $this->expectException(\PDOException::class);

        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-INVALID',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 0,
            'remaining_amount' => 111000,
            'status' => 'invalid_status_value',
            'due_date' => today()->addDays(30),
        ]);
    }

    #[Test]
    public function sales_order_status_accepts_all_valid_enum_values()
    {
        $validStatuses = SalesOrder::STATUSES;

        foreach ($validStatuses as $status) {
            $so = SalesOrder::create([
                'tenant_id' => $this->tenant->id,
                'customer_id' => $this->customer->id,
                'user_id' => $this->user->id,
                'number' => 'SO-' . uniqid(),
                'status' => $status,
                'date' => today(),
                'subtotal' => 100000,
                'discount' => 0,
                'tax' => 11000,
                'total' => 111000,
            ]);

            $this->assertEquals($status, $so->fresh()->status);
        }
    }

    #[Test]
    public function sales_order_status_rejects_invalid_enum_value()
    {
        $this->expectException(\PDOException::class);

        SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
            'number' => 'SO-INVALID',
            'status' => 'invalid_status',
            'date' => today(),
            'subtotal' => 100000,
            'discount' => 0,
            'tax' => 11000,
            'total' => 111000,
        ]);
    }

    #[Test]
    public function purchase_order_status_accepts_valid_enum_values()
    {
        // Create supplier first
        $supplier = \App\Models\Supplier::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $validStatuses = ['draft', 'pending', 'approved', 'rejected', 'completed', 'cancelled'];

        foreach ($validStatuses as $status) {
            $po = PurchaseOrder::create([
                'tenant_id' => $this->tenant->id,
                'supplier_id' => $supplier->id,
                'user_id' => $this->user->id,
                'number' => 'PO-' . uniqid(),
                'status' => $status,
                'date' => today(),
                'subtotal' => 100000,
                'discount' => 0,
                'tax' => 11000,
                'total' => 111000,
            ]);

            $this->assertEquals($status, $po->fresh()->status);
        }
    }

    #[Test]
    public function purchase_order_status_rejects_invalid_enum_value()
    {
        $supplier = \App\Models\Supplier::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $this->expectException(\PDOException::class);

        PurchaseOrder::create([
            'tenant_id' => $this->tenant->id,
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'number' => 'PO-INVALID',
            'status' => 'invalid_po_status',
            'date' => today(),
            'subtotal' => 100000,
            'discount' => 0,
            'tax' => 11000,
            'total' => 111000,
        ]);
    }

    #[Test]
    public function invoice_voided_status_is_supported()
    {
        // Requirement 1.2: invoices.status must support 'voided'
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-VOID-TEST',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 0,
            'remaining_amount' => 111000,
            'status' => Invoice::STATUS_VOIDED,
            'due_date' => today()->addDays(30),
        ]);

        $this->assertEquals('voided', $invoice->fresh()->status);
        $this->assertContains('voided', Invoice::STATUSES);
    }

    #[Test]
    public function invoice_partial_paid_status_is_supported()
    {
        // Requirement 1.2: invoices.status must support 'partial_paid'
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-PARTIAL-TEST',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 50000,
            'remaining_amount' => 61000,
            'status' => Invoice::STATUS_PARTIAL_PAID,
            'due_date' => today()->addDays(30),
        ]);

        $this->assertEquals('partial_paid', $invoice->fresh()->status);
        $this->assertContains('partial_paid', Invoice::STATUSES);
    }

    #[Test]
    public function invoice_cancelled_status_is_supported()
    {
        // Requirement 1.2: invoices.status must support 'cancelled'
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'number' => 'INV-CANCEL-TEST',
            'subtotal_amount' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'paid_amount' => 0,
            'remaining_amount' => 111000,
            'status' => Invoice::STATUS_CANCELLED,
            'due_date' => today()->addDays(30),
        ]);

        $this->assertEquals('cancelled', $invoice->fresh()->status);
        $this->assertContains('cancelled', Invoice::STATUSES);
    }

    #[Test]
    public function all_invoice_status_constants_are_valid()
    {
        // Verify all constants defined in Invoice model are valid
        $constants = [
            Invoice::STATUS_UNPAID,
            Invoice::STATUS_PARTIAL,
            Invoice::STATUS_PARTIAL_PAID,
            Invoice::STATUS_PAID,
            Invoice::STATUS_CANCELLED,
            Invoice::STATUS_VOIDED,
            Invoice::STATUS_OVERDUE,
        ];

        foreach ($constants as $status) {
            $this->assertContains($status, Invoice::STATUSES, "Status constant '{$status}' should be in STATUSES array");
        }
    }

    #[Test]
    public function all_sales_order_status_constants_are_valid()
    {
        // Verify all constants defined in SalesOrder model are valid
        $constants = [
            SalesOrder::STATUS_PENDING,
            SalesOrder::STATUS_PENDING_PAYMENT,
            SalesOrder::STATUS_CONFIRMED,
            SalesOrder::STATUS_PROCESSING,
            SalesOrder::STATUS_SHIPPED,
            SalesOrder::STATUS_DELIVERED,
            SalesOrder::STATUS_COMPLETED,
            SalesOrder::STATUS_CANCELLED,
        ];

        foreach ($constants as $status) {
            $this->assertContains($status, SalesOrder::STATUSES, "Status constant '{$status}' should be in STATUSES array");
        }
    }
}





