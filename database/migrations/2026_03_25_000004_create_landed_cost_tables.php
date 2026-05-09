<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Landed cost header — linked to a PO or GR
        if (! Schema::hasTable('landed_costs')) {
            Schema::create('landed_costs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('number', 30);
                $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('goods_receipt_id')->nullable()->constrained()->nullOnDelete();
                $table->date('date');
                $table->string('description')->nullable();
                $table->enum('allocation_method', ['by_value', 'by_quantity', 'by_weight', 'equal'])->default('by_value');
                $table->decimal('total_additional_cost', 15, 2)->default(0);
                $table->enum('status', ['draft', 'allocated', 'posted'])->default('draft');
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'number']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // Cost components (freight, customs, insurance, etc.)
        if (! Schema::hasTable('landed_cost_components')) {
            Schema::create('landed_cost_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('landed_cost_id')->constrained()->cascadeOnDelete();
                $table->string('name');                              // Freight, Bea Masuk, Asuransi, dll
                $table->string('type', 30)->default('freight');      // freight, customs, insurance, handling, other
                $table->decimal('amount', 15, 2);
                $table->string('vendor')->nullable();                // vendor/forwarder
                $table->string('reference')->nullable();             // no. invoice vendor
                $table->timestamps();
            });
        }

        // Allocation per product line
        if (! Schema::hasTable('landed_cost_allocations')) {
            Schema::create('landed_cost_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('landed_cost_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->decimal('original_cost', 15, 2);             // harga beli asli (qty × price)
                $table->decimal('quantity', 12, 3);
                $table->decimal('weight', 12, 3)->nullable();
                $table->decimal('allocated_cost', 15, 2)->default(0); // biaya tambahan yang dialokasikan
                $table->decimal('landed_unit_cost', 15, 2)->default(0); // (original + allocated) / qty
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('landed_cost_allocations');
        Schema::dropIfExists('landed_cost_components');
        Schema::dropIfExists('landed_costs');
    }
};
