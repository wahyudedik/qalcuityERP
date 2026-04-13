<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run migrations for Product Variants & Recall Management.
     */
    public function up(): void
    {
        // 1. Product Variants - Variant matrix for cosmetic products
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->string('sku')->unique(); // SKU-001
                $table->string('variant_name'); // "Lavender - 50ml"
                $table->json('attributes')->nullable(); // {"color": "lavender", "size": "50ml"}
                $table->decimal('size', 10, 2)->nullable(); // 50.00
                $table->string('unit')->default('ml'); // ml, g, pcs
                $table->decimal('price_adjustment', 10, 2)->default(0); // Price difference from base
                $table->decimal('cost_adjustment', 10, 2)->default(0); // Cost difference from base
                $table->string('barcode')->nullable(); // Barcode/GTIN
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'formula_id']);
                $table->index(['tenant_id', 'is_active']);
                $table->unique(['tenant_id', 'sku']);
            });
        }

        // 2. Variant Attributes - Individual attribute values
        if (!Schema::hasTable('variant_attributes')) {
            Schema::create('variant_attributes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
                $table->string('attribute_name'); // color, size, scent, etc.
                $table->string('attribute_value'); // lavender, 50ml, etc.
                $table->timestamps();

                $table->index(['tenant_id', 'attribute_name']);
                $table->unique(['tenant_id', 'variant_id', 'attribute_name']);
            });
        }

        // 3. Product Recalls - Recall management
        if (!Schema::hasTable('product_recalls')) {
            Schema::create('product_recalls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('recall_number')->unique(); // RCL-2026-0001
                $table->foreignId('product_id')->nullable()->constrained('cosmetic_formulas')->nullOnDelete();
                $table->json('batch_ids')->nullable(); // Array of batch IDs
                $table->string('recall_type'); // voluntary, mandatory
                $table->string('severity'); // critical, major, minor
                $table->text('reason');
                $table->text('description')->nullable();
                $table->integer('affected_units')->default(0);
                $table->text('action_required'); // Return, Dispose, Replace, etc.
                $table->string('contact_person')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->date('completion_date')->nullable();
                $table->string('status')->default('initiated'); // initiated, in_progress, completed, cancelled
                $table->text('resolution_notes')->nullable();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'severity']);
                $table->index(['tenant_id', 'start_date']);
            });
        }

        // 4. Distribution Channels - Channel tracking
        if (!Schema::hasTable('distribution_channels')) {
            Schema::create('distribution_channels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('channel_name'); // Retail, E-commerce, Distributor, etc.
                $table->string('channel_type')->default('direct'); // direct, wholesale, marketplace
                $table->string('contact_person')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->text('address')->nullable();
                $table->string('region')->nullable();
                $table->string('status')->default('active'); // active, inactive
                $table->decimal('commission_rate', 5, 2)->nullable(); // Percentage
                $table->integer('priority')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'channel_type']);
            });
        }

        // 5. Channel Sales - Sales per channel tracking
        if (!Schema::hasTable('channel_sales')) {
            Schema::create('channel_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('channel_id')->constrained('distribution_channels')->onDelete('cascade');
                $table->foreignId('formula_id')->nullable()->constrained('cosmetic_formulas')->nullOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('cosmetic_batch_records')->nullOnDelete();
                $table->date('sale_date');
                $table->integer('quantity_sold');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_amount', 15, 2);
                $table->decimal('commission_amount', 15, 2)->nullable();
                $table->string('status')->default('completed'); // pending, completed, returned
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'channel_id']);
                $table->index(['tenant_id', 'sale_date']);
                $table->index(['tenant_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_sales');
        Schema::dropIfExists('distribution_channels');
        Schema::dropIfExists('product_recalls');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('product_variants');
    }
};
