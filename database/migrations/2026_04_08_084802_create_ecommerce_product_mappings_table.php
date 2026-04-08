<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ecommerce_product_mappings')) {
            Schema::create('ecommerce_product_mappings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('channel_id')->constrained('integrations')->onDelete('cascade');
                $table->string('external_id'); // Product ID on marketplace
                $table->string('external_sku')->nullable(); // SKU on marketplace
                $table->string('external_variant_id')->nullable(); // Variant ID if applicable
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable(); // Additional mapping data
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'channel_id']);
                $table->index(['tenant_id', 'is_active']);
                $table->index(['channel_id', 'external_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_mappings');
    }
};
