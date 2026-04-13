<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // a) Create marketplace_sync_logs table
        if (!Schema::hasTable('marketplace_sync_logs')) {
            Schema::create('marketplace_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('mapping_id')->nullable();
                $table->string('type'); // 'stock', 'price', 'order'
                $table->string('status'); // 'success', 'failed', 'retrying', 'abandoned'
                $table->text('error_message')->nullable();
                $table->integer('attempt_count')->default(1);
                $table->timestamp('next_retry_at')->nullable();
                $table->json('payload')->nullable();
                $table->json('response')->nullable();
                $table->timestamps();

                $table->foreign('channel_id')->references('id')->on('ecommerce_channels')->onDelete('cascade');
                $table->foreign('mapping_id')->references('id')->on('ecommerce_product_mappings')->onDelete('set null');
                $table->index(['channel_id', 'type', 'status'], 'mktpl_sync_ch_type_status');
                $table->index(['status', 'next_retry_at'], 'mktpl_sync_retry_idx');
            });
        }

        // b) Create ecommerce_webhook_logs table (skip if already exists)
        if (!Schema::hasTable('ecommerce_webhook_logs')) {
            Schema::create('ecommerce_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('channel_id')->nullable();
                $table->string('platform'); // shopee, tokopedia, lazada
                $table->string('event_type'); // order.created, inventory.updated, etc.
                $table->json('payload');
                $table->string('signature')->nullable();
                $table->boolean('is_valid')->default(false);
                $table->timestamp('processed_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->foreign('channel_id')->references('id')->on('ecommerce_channels')->onDelete('set null');
                $table->index(['platform', 'event_type'], 'ecom_wh_platform_event');
            });
        }

        // c) Create product_price_history table
        Schema::create('product_price_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->decimal('old_price', 18, 2);
            $table->decimal('new_price', 18, 2);
            $table->string('source')->default('manual'); // 'manual', 'sync', 'bulk_update'
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->integer('orders_before_7d')->default(0);
            $table->integer('orders_after_7d')->default(0);
            $table->decimal('revenue_before_7d', 18, 2)->default(0);
            $table->decimal('revenue_after_7d', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('ecommerce_channels')->onDelete('set null');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['product_id', 'created_at'], 'price_hist_prod_date');
            $table->index(['tenant_id', 'created_at'], 'price_hist_tenant_date');
        });

        // d) Alter ecommerce_product_mappings - add unique index
        Schema::table('ecommerce_product_mappings', function (Blueprint $table) {
            $table->unique(['tenant_id', 'channel_id', 'external_sku'], 'ecom_mapping_sku_unique');
        });

        // e) Alter ecommerce_channels - add webhook columns
        Schema::table('ecommerce_channels', function (Blueprint $table) {
            $table->string('webhook_secret')->nullable()->after('sync_errors');
            $table->boolean('webhook_enabled')->default(false)->after('webhook_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse order of up()

        // e) Drop columns from ecommerce_channels
        Schema::table('ecommerce_channels', function (Blueprint $table) {
            $table->dropColumn(['webhook_secret', 'webhook_enabled']);
        });

        // d) Drop unique index from ecommerce_product_mappings
        Schema::table('ecommerce_product_mappings', function (Blueprint $table) {
            $table->dropUnique('ecom_mapping_sku_unique');
        });

        // c) Drop product_price_history table
        Schema::dropIfExists('product_price_history');

        // b) Drop ecommerce_webhook_logs table
        Schema::dropIfExists('ecommerce_webhook_logs');

        // a) Drop marketplace_sync_logs table
        Schema::dropIfExists('marketplace_sync_logs');
    }
};
