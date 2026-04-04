<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // F&B Suppliers
        Schema::create('fb_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_code')->unique(); // e.g., SUP-001
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Indonesia');
            $table->string('tax_number')->nullable(); // NPWP
            $table->text('payment_terms')->nullable(); // e.g., "Net 30"
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->integer('rating')->default(0); // 1-5
            $table->text('notes')->nullable();
            $table->json('supplied_categories')->nullable(); // Array of categories
            $table->timestamp('last_order_date')->nullable();
            $table->decimal('total_orders_value', 15, 2)->default(0);
            $table->integer('total_orders_count')->default(0);
            $table->boolean('is_preferred')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'is_preferred']);
        });

        // Supplier Products (Link suppliers to supplies they provide)
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('fb_suppliers')->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('fb_supplies')->cascadeOnDelete();
            $table->decimal('supplier_unit_price', 15, 2); // Price from this supplier
            $table->integer('minimum_order_quantity')->default(1);
            $table->integer('lead_time_days')->default(7); // Days to deliver
            $table->boolean('is_primary')->default(false); // Primary supplier for this item
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['supplier_id', 'supply_id']);
            $table->index(['tenant_id', 'supply_id']);
        });

        // Purchase Orders
        Schema::create('fb_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('po_number')->unique(); // e.g., PO-20260404-001
            $table->foreignId('supplier_id')->constrained('fb_suppliers')->cascadeOnDelete();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'confirmed', 'partially_received', 'received', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'order_date']);
        });

        // Purchase Order Items
        Schema::create('fb_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('fb_purchase_orders')->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('fb_supplies')->cascadeOnDelete();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2)->storedAs('(`quantity_ordered` * `unit_price`)');
            $table->date('expected_delivery_date')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id', 'supply_id']);
        });

        // Goods Receipts
        Schema::create('fb_goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('fb_purchase_orders')->cascadeOnDelete();
            $table->string('gr_number')->unique(); // e.g., GR-20260404-001
            $table->date('receipt_date');
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'receipt_date']);
        });

        // Goods Receipt Items
        Schema::create('fb_goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goods_receipt_id')->constrained('fb_goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained('fb_purchase_order_items')->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('fb_supplies')->cascadeOnDelete();
            $table->integer('quantity_received');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2)->storedAs('(`quantity_received` * `unit_price`)');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('quality_notes')->nullable();
            $table->timestamps();

            $table->index(['goods_receipt_id', 'supply_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_goods_receipt_items');
        Schema::dropIfExists('fb_goods_receipts');
        Schema::dropIfExists('fb_purchase_order_items');
        Schema::dropIfExists('fb_purchase_orders');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('fb_suppliers');
    }
};
