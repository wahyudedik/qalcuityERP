<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Task 39: Sales Returns (Retur Penjualan) ──────────────
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('return_date');
            $table->string('reason');
            $table->enum('status', ['draft', 'approved', 'completed', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            // Credit note: offset ke invoice asal atau jadi saldo customer
            $table->enum('refund_method', ['credit_note', 'cash_refund', 'customer_balance'])->default('credit_note');
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->boolean('is_cross_period')->default(false); // retur beda periode
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'invoice_id']);
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('condition')->default('good'); // good, damaged, expired
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Task 40: Purchase Returns (Retur Pembelian) ───────────
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('return_date');
            $table->string('reason');
            $table->enum('status', ['draft', 'sent', 'completed', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            // Debit note: offset ke hutang supplier atau minta refund
            $table->enum('refund_method', ['debit_note', 'cash_refund', 'supplier_credit'])->default('debit_note');
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->boolean('is_cross_period')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'purchase_order_id']);
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('return_reason')->nullable(); // wrong_item, damaged, expired, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Task 41: Down Payments (Uang Muka) ────────────────────
        Schema::create('down_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->enum('type', ['customer', 'supplier']); // DP dari customer atau ke supplier
            $table->unsignedBigInteger('party_id');   // customer_id atau supplier_id
            $table->string('party_type');             // App\Models\Customer atau App\Models\Supplier
            $table->unsignedBigInteger('reference_id')->nullable();   // sales_order_id atau purchase_order_id
            $table->string('reference_type')->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('applied_amount', 15, 2)->default(0); // sudah di-offset ke invoice
            $table->decimal('remaining_amount', 15, 2);
            $table->enum('status', ['pending', 'partial', 'applied', 'refunded'])->default('pending');
            $table->string('payment_method')->default('transfer');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type', 'party_id']);
            $table->index(['tenant_id', 'status']);
        });

        // Link DP ke invoice (saat offset)
        Schema::create('down_payment_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('down_payment_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('invoice_id')->nullable();   // invoice atau payable
            $table->string('invoice_type')->nullable();             // App\Models\Invoice atau App\Models\Payable
            $table->decimal('amount', 15, 2);
            $table->date('applied_date');
            $table->foreignId('applied_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Task 42: Bulk Payments ────────────────────────────────
        Schema::create('bulk_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->enum('type', ['receivable', 'payable']); // bayar piutang atau hutang
            $table->unsignedBigInteger('party_id');   // customer_id atau supplier_id
            $table->string('party_type');
            $table->date('payment_date');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('applied_amount', 15, 2)->default(0);
            $table->decimal('overpayment', 15, 2)->default(0); // sisa jadi saldo
            $table->string('payment_method')->default('transfer');
            $table->enum('status', ['draft', 'applied', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'type', 'party_id']);
        });

        // Detail invoice yang dibayar dalam bulk payment
        Schema::create('bulk_payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_payment_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('payable_id');   // invoice_id atau payable_id
            $table->string('payable_type');             // App\Models\Invoice atau App\Models\Payable
            $table->decimal('amount', 15, 2);           // jumlah yang dibayar untuk invoice ini
            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
        });

        // ── Task 43: Customer Balance (saldo overpayment) ─────────
        Schema::create('customer_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0); // saldo kredit customer
            $table->timestamps();

            $table->unique(['tenant_id', 'customer_id']);
        });

        Schema::create('customer_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']); // credit = tambah saldo, debit = pakai saldo
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->string('description');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
        });

        // ── Task 43: Partial Delivery tracking ────────────────────
        // SO bisa punya banyak delivery (pengiriman sebagian)
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('delivery_date');
            $table->enum('status', ['draft', 'shipped', 'delivered', 'cancelled'])->default('draft');
            $table->string('shipping_address')->nullable();
            $table->string('courier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'sales_order_id']);
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_ordered', 10, 2);
            $table->decimal('quantity_delivered', 10, 2)->default(0);
            $table->timestamps();
        });

        // Tambah kolom ke sales_orders untuk tracking partial delivery
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('delivered_amount', 15, 2)->default(0)->after('total');
            $table->boolean('is_fully_delivered')->default(false)->after('delivered_amount');
        });

        // Tambah kolom customer_balance ke customers
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('credit_balance', 15, 2)->default(0)->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['delivered_amount', 'is_fully_delivered']);
        });
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('customer_balance_transactions');
        Schema::dropIfExists('customer_balances');
        Schema::dropIfExists('bulk_payment_items');
        Schema::dropIfExists('bulk_payments');
        Schema::dropIfExists('down_payment_applications');
        Schema::dropIfExists('down_payments');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
