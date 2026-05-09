<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Audit Trail ───────────────────────────────────────────
        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action'); // created, updated, deleted, login, logout, viewed
                $table->string('model_type')->nullable();
                $table->unsignedBigInteger('model_id')->nullable();
                $table->string('description');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'model_type', 'model_id']);
                $table->index(['tenant_id', 'user_id']);
            });
        }

        // ── Approval Workflow ─────────────────────────────────────
        if (! Schema::hasTable('approval_workflows')) {
            Schema::create('approval_workflows', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('model_type'); // PurchaseOrder, SalesOrder, etc
                $table->decimal('min_amount', 18, 2)->default(0);
                $table->decimal('max_amount', 18, 2)->nullable();
                $table->json('approver_roles'); // ["manager","admin"]
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('approval_requests')) {
            Schema::create('approval_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('workflow_id')->nullable();
                $table->unsignedBigInteger('requested_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->decimal('amount', 18, 2)->default(0);
                $table->text('notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'status']);
                $table->index(['model_type', 'model_id']);
            });
        }

        // ── Bank Reconciliation ───────────────────────────────────
        if (! Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('bank_name');
                $table->string('account_number');
                $table->string('account_name');
                $table->decimal('balance', 18, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('bank_statements')) {
            Schema::create('bank_statements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('bank_account_id');
                $table->date('transaction_date');
                $table->string('description');
                $table->string('type'); // credit, debit
                $table->decimal('amount', 18, 2);
                $table->decimal('balance', 18, 2)->default(0);
                $table->string('reference')->nullable();
                $table->string('status')->default('unmatched'); // unmatched, matched, ignored
                $table->unsignedBigInteger('matched_transaction_id')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'bank_account_id', 'status']);
            });
        }

        // ── Digital Signature ─────────────────────────────────────
        if (! Schema::hasTable('digital_signatures')) {
            Schema::create('digital_signatures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->string('role')->nullable(); // signer, approver, witness
                $table->text('signature_data'); // base64 SVG/PNG
                $table->string('ip_address', 45)->nullable();
                $table->string('hash')->nullable(); // document hash at time of signing
                $table->timestamp('signed_at');
                $table->timestamps();
                $table->index(['tenant_id', 'model_type', 'model_id']);
            });
        }

        // ── Shipping ──────────────────────────────────────────────
        if (! Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('sales_order_id')->nullable();
                $table->string('courier'); // jne, jnt, sicepat, anteraja, pos
                $table->string('service')->nullable(); // REG, YES, OKE, etc
                $table->string('tracking_number')->nullable();
                $table->string('origin_city');
                $table->string('destination_city');
                $table->decimal('weight_kg', 8, 2)->default(1);
                $table->decimal('shipping_cost', 18, 2)->default(0);
                $table->string('status')->default('pending'); // pending, picked_up, in_transit, delivered, returned
                $table->string('recipient_name')->nullable();
                $table->text('recipient_address')->nullable();
                $table->timestamp('estimated_delivery')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->json('tracking_history')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'status']);
            });
        }

        // ── WhatsApp / Telegram Notifications ─────────────────────
        if (! Schema::hasTable('bot_configs')) {
            Schema::create('bot_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('platform'); // whatsapp, telegram
                $table->string('token')->nullable(); // bot token
                $table->string('webhook_url')->nullable();
                $table->string('phone_number')->nullable(); // for WA
                $table->string('chat_id')->nullable(); // for Telegram
                $table->json('notification_events')->nullable(); // which events to notify
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                $table->unique(['tenant_id', 'platform']);
            });
        }

        if (! Schema::hasTable('bot_messages')) {
            Schema::create('bot_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('platform');
                $table->string('direction'); // outbound, inbound
                $table->string('recipient')->nullable();
                $table->text('message');
                $table->string('status')->default('pending'); // pending, sent, failed
                $table->string('event_type')->nullable(); // low_stock, new_order, payment_received
                $table->json('payload')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'status']);
            });
        }

        // ── E-commerce Integration ────────────────────────────────
        if (! Schema::hasTable('ecommerce_channels')) {
            Schema::create('ecommerce_channels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('platform'); // tokopedia, shopee, lazada, bukalapak
                $table->string('shop_name')->nullable();
                $table->string('shop_id')->nullable();
                $table->text('access_token')->nullable();
                $table->text('refresh_token')->nullable();
                $table->timestamp('token_expires_at')->nullable();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'platform']);
            });
        }

        if (! Schema::hasTable('ecommerce_orders')) {
            Schema::create('ecommerce_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('sales_order_id')->nullable();
                $table->string('platform_order_id');
                $table->string('platform');
                $table->string('buyer_name')->nullable();
                $table->string('buyer_phone')->nullable();
                $table->decimal('total', 18, 2)->default(0);
                $table->string('status'); // pending, processing, shipped, delivered, cancelled
                $table->json('items')->nullable();
                $table->json('raw_data')->nullable();
                $table->timestamp('ordered_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'platform', 'status']);
            });
        }

        // ── PWA Push Subscriptions ────────────────────────────────
        if (! Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->text('endpoint');
                $table->text('public_key')->nullable();
                $table->text('auth_token')->nullable();
                $table->timestamps();
                $table->index('user_id');
            });
        }

        // ── Add approval_status to purchase_orders & sales_orders ─
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'approval_status')) {
                $table->string('approval_status')->default('approved')->after('status');
            }
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_orders', 'approval_status')) {
                $table->string('approval_status')->default('approved')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', fn ($t) => $t->dropColumn('approval_status'));
        Schema::table('purchase_orders', fn ($t) => $t->dropColumn('approval_status'));
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_channels');
        Schema::dropIfExists('bot_messages');
        Schema::dropIfExists('bot_configs');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('digital_signatures');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('activity_logs');
    }
};
