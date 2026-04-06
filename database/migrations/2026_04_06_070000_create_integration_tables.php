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
        // 1. Payment Gateway Configurations
        if (!Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('provider'); // midtrans, xendit, duitku
                $table->string('environment')->default('sandbox'); // sandbox, production
                $table->string('api_key')->nullable();
                $table->string('secret_key')->nullable();
                $table->string('merchant_id')->nullable();
                $table->json('configuration')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('is_default')->default(false);
                $table->text('webhook_url')->nullable();
                $table->timestamp('last_tested_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'provider']);
                $table->unique(['tenant_id', 'provider', 'environment']);
            });
        }

        // 2. Payment Transactions
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('transaction_id')->unique(); // From payment gateway
                $table->string('order_id'); // Internal order reference
                $table->string('gateway_provider'); // midtrans, xendit, duitku
                $table->decimal('amount', 15, 2);
                $table->string('currency')->default('IDR');
                $table->string('status'); // pending, success, failed, expired, refund
                $table->string('payment_method')->nullable(); // credit_card, bank_transfer, ewallet
                $table->string('payment_type')->nullable(); // visa, bca_va, gopay, etc
                $table->json('gateway_response')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'order_id']);
                $table->index(['tenant_id', 'status']);
                $table->index('created_at');
            });
        }

        // 3. E-commerce Platforms
        if (!Schema::hasTable('ecommerce_platforms')) {
            Schema::create('ecommerce_platforms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('platform'); // shopify, woocommerce, tokopedia, shopee, lazada
                $table->string('store_name');
                $table->string('store_url')->nullable();
                $table->string('api_key')->nullable();
                $table->string('api_secret')->nullable();
                $table->string('access_token')->nullable();
                $table->json('configuration')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('auto_sync_inventory')->default(true);
                $table->boolean('auto_sync_orders')->default(true);
                $table->integer('sync_interval_minutes')->default(15);
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamp('last_order_sync_at')->nullable();
                $table->timestamp('last_inventory_sync_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'platform']);
            });
        }

        // 4. E-commerce Orders
        if (!Schema::hasTable('ecommerce_orders')) {
            Schema::create('ecommerce_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('platform_id')->constrained('ecommerce_platforms')->onDelete('cascade');
                $table->string('external_order_id'); // Order ID from platform
                $table->string('internal_order_id')->nullable(); // Linked to ERP order
                $table->string('customer_name');
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->text('shipping_address')->nullable();
                $table->decimal('subtotal', 15, 2);
                $table->decimal('shipping_cost', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2);
                $table->string('payment_status')->default('pending');
                $table->string('fulfillment_status')->default('unfulfilled');
                $table->json('line_items')->nullable(); // Products ordered
                $table->json('raw_data')->nullable();
                $table->timestamp('ordered_at');
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'platform_id']);
                $table->index('external_order_id');
                $table->index('ordered_at');
            });
        }

        // 5. Logistics Providers
        if (!Schema::hasTable('logistics_providers')) {
            Schema::create('logistics_providers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('provider'); // jne, jnt, sicepat, anteraja, ninja
                $table->string('account_number')->nullable();
                $table->string('api_key')->nullable();
                $table->string('api_secret')->nullable();
                $table->json('configuration')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['tenant_id', 'provider']);
            });
        }

        // 6. Shipments & Tracking
        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('logistics_provider_id')->constrained('logistics_providers')->onDelete('cascade');
                $table->foreignId('order_id')->nullable(); // Link to internal order
                $table->string('tracking_number')->unique();
                $table->string('service_type')->nullable(); // REG, YES, OKE, etc
                $table->string('status')->default('pending'); // pending, picked_up, in_transit, delivered, failed
                $table->string('origin_city')->nullable();
                $table->string('destination_city')->nullable();
                $table->decimal('weight_kg', 8, 2)->nullable();
                $table->decimal('shipping_cost', 15, 2)->nullable();
                $table->json('tracking_history')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('estimated_delivery')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'tracking_number']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // 7. Accounting Integrations
        if (!Schema::hasTable('accounting_integrations')) {
            Schema::create('accounting_integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('provider'); // jurnal_id, accurate_online, zahir
                $table->string('company_id')->nullable();
                $table->string('api_key')->nullable();
                $table->string('api_secret')->nullable();
                $table->string('access_token')->nullable();
                $table->string('refresh_token')->nullable();
                $table->json('configuration')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('auto_sync_invoices')->default(true);
                $table->boolean('auto_sync_payments')->default(true);
                $table->boolean('auto_sync_expenses')->default(true);
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamp('token_expires_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'provider']);
            });
        }

        // 8. Accounting Sync Logs
        if (!Schema::hasTable('accounting_sync_logs')) {
            Schema::create('accounting_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('integration_id')->constrained('accounting_integrations')->onDelete('cascade');
                $table->string('sync_type'); // invoice, payment, expense, journal
                $table->string('status'); // success, failed, partial
                $table->integer('records_synced')->default(0);
                $table->integer('records_failed')->default(0);
                $table->json('errors')->nullable();
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'integration_id']);
                $table->index('started_at');
            });
        }

        // 9. Communication Channels
        if (!Schema::hasTable('communication_channels')) {
            Schema::create('communication_channels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('channel'); // whatsapp_business, telegram, email, sms
                $table->string('phone_number')->nullable(); // For WhatsApp
                $table->string('bot_token')->nullable(); // For Telegram
                $table->string('api_key')->nullable();
                $table->string('api_secret')->nullable();
                $table->json('configuration')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('is_default')->default(false);
                $table->integer('messages_sent_today')->default(0);
                $table->integer('daily_limit')->default(1000);
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'channel']);
            });
        }

        // 10. Message Logs
        if (!Schema::hasTable('message_logs')) {
            Schema::create('message_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('channel_id')->constrained('communication_channels')->onDelete('cascade');
                $table->string('recipient'); // Phone number or chat ID
                $table->text('message');
                $table->string('message_type')->default('text'); // text, image, document
                $table->string('status')->default('pending'); // pending, sent, delivered, read, failed
                $table->json('response_data')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'channel_id']);
                $table->index('recipient');
                $table->index('created_at');
            });
        }

        // 11. Bank Accounts
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('bank_name'); // BCA, Mandiri, BNI, BRI, etc
                $table->string('account_number');
                $table->string('account_name');
                $table->string('account_type')->default('checking'); // checking, savings
                $table->string('currency')->default('IDR');
                $table->decimal('current_balance', 15, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('auto_import')->default(false);
                $table->string('import_method')->nullable(); // csv, api, ofx
                $table->json('configuration')->nullable();
                $table->timestamp('last_import_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'bank_name']);
            });
        }

        // 12. Bank Transactions (Imported)
        if (!Schema::hasTable('bank_transactions')) {
            Schema::create('bank_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
                $table->string('transaction_id')->nullable(); // From bank statement
                $table->timestamp('transaction_date');
                $table->string('description');
                $table->string('transaction_type'); // debit, credit
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_after', 15, 2)->nullable();
                $table->string('category')->nullable(); // sales, expense, transfer
                $table->foreignId('invoice_id')->nullable(); // Link to invoice if matched
                $table->foreignId('expense_id')->nullable(); // Link to expense if matched
                $table->boolean('reconciled')->default(false);
                $table->boolean('auto_matched')->default(false);
                $table->json('raw_data')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'bank_account_id']);
                $table->index('transaction_date');
                $table->index(['tenant_id', 'reconciled']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('message_logs');
        Schema::dropIfExists('communication_channels');
        Schema::dropIfExists('accounting_sync_logs');
        Schema::dropIfExists('accounting_integrations');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('logistics_providers');
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_platforms');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_gateways');
    }
};
