<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations - Product Variant Management Module.
     */
    public function up(): void
    {
        // 1. Variant Attributes - Attribute definitions (Color, Size, Fragrance, etc.)
        if (!Schema::hasTable('variant_attributes')) {
            Schema::create('variant_attributes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('attribute_name'); // Color, Size, Fragrance, Packaging
                $table->string('attribute_type'); // select, text, color, number
                $table->json('attribute_values')->nullable(); // ["Red", "Blue", "Green"]
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'attribute_name']);
            });
        }

        // 2. Product Variants - Individual product variants
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->string('variant_name'); // "Matte Lipstick - Ruby Red - 3.5g"
                $table->string('sku')->unique(); // MLS-RUBY-001
                $table->string('barcode')->nullable();
                $table->json('variant_attributes'); // {"color": "Ruby Red", "size": "3.5g", "finish": "Matte"}
                $table->decimal('price', 12, 2)->nullable(); // Variant-specific pricing
                $table->decimal('cost_price', 12, 2)->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->integer('reorder_level')->default(10);
                $table->string('status')->default('active'); // active, inactive, discontinued
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'formula_id']);
                $table->index(['tenant_id', 'sku']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // 3. Variant Inventory - Stock tracking per variant
        if (!Schema::hasTable('variant_inventory')) {
            Schema::create('variant_inventory', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
                $table->date('transaction_date');
                $table->string('transaction_type'); // in, out, adjustment, transfer
                $table->integer('quantity'); // positive or negative
                $table->integer('balance'); // Running balance
                $table->string('reference_type')->nullable(); // batch, order, adjustment
                $table->string('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'variant_id']);
                $table->index(['tenant_id', 'transaction_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_inventory');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('variant_attributes');
    }
};
