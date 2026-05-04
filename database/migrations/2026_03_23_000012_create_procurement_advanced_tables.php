<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Purchase Requisition ──────────────────────────────────
        if (!Schema::hasTable('purchase_requisitions')) {
            Schema::create('purchase_requisitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('number')->unique();
                $table->string('department')->nullable();
                $table->date('required_date')->nullable();
                $table->text('purpose')->nullable();
                $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'converted'])
                      ->default('draft');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->decimal('estimated_total', 15, 2)->default(0);
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'status']);
            });
        }

        if (!Schema::hasTable('purchase_requisition_items')) {
            Schema::create('purchase_requisition_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_requisition_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('description');          // free-text fallback
                $table->decimal('quantity', 10, 2);
                $table->string('unit')->nullable();
                $table->decimal('estimated_price', 15, 2)->default(0);
                $table->decimal('estimated_total', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // ── RFQ (Request for Quotation) ───────────────────────────
        if (!Schema::hasTable('rfqs')) {
            Schema::create('rfqs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_requisition_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->string('number')->unique();
                $table->date('issue_date');
                $table->date('deadline');               // batas waktu respon supplier
                $table->text('notes')->nullable();
                $table->enum('status', ['open', 'closed', 'converted'])->default('open');
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'status']);
            });
        }

        if (!Schema::hasTable('rfq_items')) {
            Schema::create('rfq_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('description');
                $table->decimal('quantity', 10, 2);
                $table->string('unit')->nullable();
                $table->timestamps();
            });
        }

        // Supplier responses to RFQ
        if (!Schema::hasTable('rfq_responses')) {
            Schema::create('rfq_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->date('response_date');
                $table->decimal('total_price', 15, 2)->default(0);
                $table->integer('delivery_days')->nullable();
                $table->string('payment_terms')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_selected')->default(false);
                $table->json('item_prices')->nullable(); // [{rfq_item_id, unit_price, total}]
                $table->timestamps();
    
                $table->unique(['rfq_id', 'supplier_id']);
            });
        }

        // ── Goods Receipt (GR) ────────────────────────────────────
        if (!Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
                $table->string('number')->unique();
                $table->date('receipt_date');
                $table->string('delivery_note')->nullable();    // nomor surat jalan
                $table->enum('status', ['draft', 'confirmed'])->default('draft');
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'purchase_order_id']);
            });
        }

        if (!Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity_received', 10, 2);
                $table->decimal('quantity_accepted', 10, 2);    // setelah QC
                $table->decimal('quantity_rejected', 10, 2)->default(0);
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
            });
        }

        // Link PO ke PR
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'purchase_requisition_id')) {
                $table->foreignId('purchase_requisition_id')->nullable()->after('warehouse_id')
                      ->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_orders', 'rfq_id')) {
                $table->foreignId('rfq_id')->nullable()->after('purchase_requisition_id')
                      ->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['purchase_requisition_id']);
            $table->dropForeign(['rfq_id']);
            $table->dropColumn(['purchase_requisition_id', 'rfq_id']);
        });
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('rfq_responses');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
        Schema::dropIfExists('purchase_requisition_items');
        Schema::dropIfExists('purchase_requisitions');
    }
};
