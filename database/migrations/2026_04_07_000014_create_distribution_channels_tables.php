<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations - Distribution Channel Management Module.
     */
    public function up(): void
    {
        // 1. Distribution Channels - Channel types (retail, online, distributor, reseller)
        if (!Schema::hasTable('distribution_channels')) {
            Schema::create('distribution_channels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('channel_name');
                $table->string('channel_type'); // retail, online_marketplace, distributor, reseller_mlm
                $table->string('channel_code')->unique(); // CHN-001
                $table->text('description')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
                $table->decimal('discount_rate', 5, 2)->default(0); // Channel discount
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
    
                $table->index(['tenant_id', 'channel_type']);
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // 2. Channel Pricing - Different prices per channel
        if (!Schema::hasTable('channel_pricing')) {
            Schema::create('channel_pricing', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('channel_id')->constrained('distribution_channels')->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->decimal('base_price', 12, 2); // Standard price
                $table->decimal('channel_price', 12, 2); // Channel-specific price
                $table->decimal('minimum_order_quantity', 10, 2)->default(1);
                $table->decimal('bulk_discount_threshold', 10, 2)->nullable();
                $table->decimal('bulk_discount_rate', 5, 2)->nullable();
                $table->date('effective_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
    
                $table->unique(['channel_id', 'formula_id']);
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // 3. Channel Inventory - Stock allocation per channel
        if (!Schema::hasTable('channel_inventory')) {
            Schema::create('channel_inventory', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('channel_id')->constrained('distribution_channels')->onDelete('cascade');
                $table->foreignId('formula_id')->constrained('cosmetic_formulas')->onDelete('cascade');
                $table->decimal('allocated_stock', 10, 2)->default(0);
                $table->decimal('sold_stock', 10, 2)->default(0);
                $table->decimal('available_stock', 10, 2)->default(0); // calculated
                $table->decimal('reserved_stock', 10, 2)->default(0);
                $table->date('last_restock_date')->nullable();
                $table->timestamps();
    
                $table->unique(['channel_id', 'formula_id']);
                $table->index(['tenant_id', 'available_stock']);
            });
        }

        // 4. Channel Sales Performance - Sales tracking & analytics
        if (!Schema::hasTable('channel_sales_performance')) {
            Schema::create('channel_sales_performance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('channel_id')->constrained('distribution_channels')->onDelete('cascade');
                $table->date('sale_date');
                $table->decimal('total_sales', 12, 2)->default(0);
                $table->decimal('total_units', 10, 2)->default(0);
                $table->decimal('total_commission', 12, 2)->default(0);
                $table->decimal('total_discount', 12, 2)->default(0);
                $table->decimal('net_revenue', 12, 2)->default(0);
                $table->integer('order_count')->default(0);
                $table->json('top_products')->nullable(); // Top selling products
                $table->timestamps();
    
                $table->index(['tenant_id', 'channel_id']);
                $table->index(['tenant_id', 'sale_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_sales_performance');
        Schema::dropIfExists('channel_inventory');
        Schema::dropIfExists('channel_pricing');
        Schema::dropIfExists('distribution_channels');
    }
};
