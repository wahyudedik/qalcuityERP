<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── a) Create ecommerce_product_mappings ─────────────────────
        if (! Schema::hasTable('ecommerce_product_mappings')) {
            Schema::create('ecommerce_product_mappings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('product_id');
                $table->string('external_sku');
                $table->string('external_product_id')->nullable();
                $table->string('external_url')->nullable();
                $table->decimal('price_override', 18, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_stock_sync_at')->nullable();
                $table->timestamp('last_price_sync_at')->nullable();
                $table->timestamps();

                $table->foreign('channel_id')
                    ->references('id')->on('ecommerce_channels')
                    ->onDelete('cascade');

                $table->foreign('product_id')
                    ->references('id')->on('products')
                    ->onDelete('cascade');

                // Short index names to stay within MySQL 64-char limit
                $table->unique(['tenant_id', 'channel_id', 'product_id'], 'ecom_mapping_unique');
                $table->index(['channel_id', 'external_sku'], 'ecom_mapping_ch_sku');
            });
        }

        // ─── b) Alter ecommerce_channels ──────────────────────────────
        Schema::table('ecommerce_channels', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_channels', 'stock_sync_enabled')) {
                $table->boolean('stock_sync_enabled')->default(false)->after('last_sync_at');
            }
            if (! Schema::hasColumn('ecommerce_channels', 'price_sync_enabled')) {
                $table->boolean('price_sync_enabled')->default(false)->after('stock_sync_enabled');
            }
            if (! Schema::hasColumn('ecommerce_channels', 'last_stock_sync_at')) {
                $table->timestamp('last_stock_sync_at')->nullable()->after('price_sync_enabled');
            }
            if (! Schema::hasColumn('ecommerce_channels', 'last_price_sync_at')) {
                $table->timestamp('last_price_sync_at')->nullable()->after('last_stock_sync_at');
            }
            if (! Schema::hasColumn('ecommerce_channels', 'sync_errors')) {
                $table->json('sync_errors')->nullable()->after('last_price_sync_at');
            }
        });

        // ─── c) Alter ecommerce_orders ────────────────────────────────
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_orders', 'synced_to_sales_order')) {
                $table->boolean('synced_to_sales_order')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_mappings');

        Schema::table('ecommerce_channels', function (Blueprint $table) {
            $table->dropColumn([
                'stock_sync_enabled',
                'price_sync_enabled',
                'last_stock_sync_at',
                'last_price_sync_at',
                'sync_errors',
            ]);
        });

        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->dropColumn('synced_to_sales_order');
        });
    }
};
