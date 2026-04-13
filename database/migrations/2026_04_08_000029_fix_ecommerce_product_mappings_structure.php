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
        // Fix ecommerce_product_mappings table structure
        if (Schema::hasTable('ecommerce_product_mappings')) {
            Schema::table('ecommerce_product_mappings', function (Blueprint $table) {
                // Remove old columns
                if (Schema::hasColumn('ecommerce_product_mappings', 'external_product_id')) {
                    $table->dropColumn('external_product_id');
                }
                if (Schema::hasColumn('ecommerce_product_mappings', 'external_url')) {
                    $table->dropColumn('external_url');
                }
                if (Schema::hasColumn('ecommerce_product_mappings', 'price_override')) {
                    $table->dropColumn('price_override');
                }
                if (Schema::hasColumn('ecommerce_product_mappings', 'last_stock_sync_at')) {
                    $table->dropColumn('last_stock_sync_at');
                }
                if (Schema::hasColumn('ecommerce_product_mappings', 'last_price_sync_at')) {
                    $table->dropColumn('last_price_sync_at');
                }

                // Add new columns if they don't exist
                if (!Schema::hasColumn('ecommerce_product_mappings', 'external_id')) {
                    $table->string('external_id')->after('channel_id');
                }
                if (!Schema::hasColumn('ecommerce_product_mappings', 'external_variant_id')) {
                    $table->string('external_variant_id')->nullable()->after('external_sku');
                }
                if (!Schema::hasColumn('ecommerce_product_mappings', 'metadata')) {
                    $table->json('metadata')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('ecommerce_product_mappings', 'last_synced_at')) {
                    $table->timestamp('last_synced_at')->nullable()->after('metadata');
                }

                // Just add unique constraint using ifExists to avoid errors
                try {
                    // Try to add unique constraint - will fail if it already exists
                    Schema::getConnection()->statement(
                        "ALTER TABLE ecommerce_product_mappings ADD UNIQUE KEY unique_product_channel (product_id, channel_id)"
                    );
                } catch (\Exception $e) {
                    // Unique constraint might already exist
                }
            });

            // Add indexes - just try to add them, ignore if they exist
            try {
                Schema::table('ecommerce_product_mappings', function (Blueprint $table) {
                    $table->index(['tenant_id', 'is_active']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                Schema::table('ecommerce_product_mappings', function (Blueprint $table) {
                    $table->index(['channel_id', 'external_id']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ecommerce_product_mappings')) {
            Schema::table('ecommerce_product_mappings', function (Blueprint $table) {
                $table->dropUnique(['product_id', 'channel_id']);
                $table->dropColumn(['external_id', 'external_variant_id', 'metadata', 'last_synced_at']);
                $table->string('external_product_id')->nullable();
                $table->string('external_url')->nullable();
                $table->decimal('price_override', 18, 2)->nullable();
                $table->timestamp('last_stock_sync_at')->nullable();
                $table->timestamp('last_price_sync_at')->nullable();
            });
        }
    }
};
