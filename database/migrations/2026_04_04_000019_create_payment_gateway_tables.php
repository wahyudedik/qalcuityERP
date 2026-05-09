<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tenant payment gateway configurations
        if (! Schema::hasTable('tenant_payment_gateways')) {
            Schema::create('tenant_payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('provider'); // midtrans, xendit, duitku, tripay
                $table->string('environment')->default('sandbox'); // sandbox, production
                $table->json('credentials'); // Encrypted credentials
                $table->json('settings')->nullable(); // Additional settings
                $table->boolean('is_active')->default(false);
                $table->boolean('is_default')->default(false);
                $table->text('webhook_url')->nullable();
                $table->string('webhook_secret')->nullable();
                $table->timestamp('last_verified_at')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'provider']);
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Payment transactions
        if (! Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
                $table->string('transaction_number')->unique(); // e.g., PAY-20260404-001
                $table->string('gateway_provider'); // midtrans, xendit, etc
                $table->string('gateway_transaction_id')->nullable(); // ID from payment gateway
                $table->string('payment_method'); // qris, credit_card, bank_transfer, ewallet
                $table->string('payment_channel')->nullable(); // gopay, ovo, dana, bca, etc
                $table->decimal('amount', 15, 2);
                $table->decimal('fee', 15, 2)->default(0); // Gateway fee
                $table->decimal('net_amount', 15, 2)->storedAs('(`amount` - `fee`)');
                $table->enum('status', [
                    'pending',
                    'waiting_payment',
                    'processing',
                    'success',
                    'failed',
                    'expired',
                    'cancelled',
                    'refund',
                ])->default('pending');
                $table->text('gateway_response')->nullable(); // Full response from gateway
                $table->string('qr_string')->nullable(); // QRIS string
                $table->string('qr_image_url')->nullable(); // QR image URL
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->json('metadata')->nullable(); // Additional data
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'created_at']);
                $table->index('gateway_transaction_id');
            });
        }

        // Payment callbacks/logs
        if (! Schema::hasTable('payment_callbacks')) {
            Schema::create('payment_callbacks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
                $table->string('gateway_provider');
                $table->string('event_type'); // payment.success, payment.failed, etc
                $table->json('payload'); // Full webhook payload
                $table->string('signature')->nullable(); // Webhook signature
                $table->boolean('verified')->default(false);
                $table->boolean('processed')->default(false);
                $table->text('error_message')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'processed']);
                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_callbacks');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('tenant_payment_gateways');
    }
};
