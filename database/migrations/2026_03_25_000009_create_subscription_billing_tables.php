<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Subscription plans defined by tenant for their customers
        if (! Schema::hasTable('customer_subscription_plans')) {
            Schema::create('customer_subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 30)->nullable();
                $table->text('description')->nullable();
                $table->decimal('price', 15, 2);
                $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
                $table->unsignedSmallInteger('trial_days')->default(0);
                $table->boolean('is_active')->default(true);
                $table->json('features')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Customer subscriptions
        if (! Schema::hasTable('customer_subscriptions')) {
            Schema::create('customer_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('customer_subscription_plans')->cascadeOnDelete();
                $table->string('subscription_number', 30);
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->date('trial_ends_at')->nullable();
                $table->date('next_billing_date');
                $table->decimal('price_override', 15, 2)->nullable(); // custom price
                $table->decimal('discount_pct', 5, 2)->default(0);
                $table->boolean('auto_renew')->default(true);
                $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('active');
                $table->string('cancel_reason')->nullable();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'subscription_number']);
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'next_billing_date']);
            });
        }

        // Subscription invoices (recurring billing history)
        if (! Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subscription_id')->constrained('customer_subscriptions')->cascadeOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
                $table->date('billing_date');
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('amount', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2);
                $table->enum('status', ['pending', 'invoiced', 'paid', 'failed'])->default('pending');
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('customer_subscriptions');
        Schema::dropIfExists('customer_subscription_plans');
    }
};
