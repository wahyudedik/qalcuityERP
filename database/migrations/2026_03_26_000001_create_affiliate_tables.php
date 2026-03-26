<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Affiliate profiles (linked to users with role=affiliate)
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();              // referral code: AFF-XXXXX
            $table->string('company_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account', 30)->nullable();
            $table->string('bank_holder')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00); // 10%
            $table->decimal('total_earned', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);     // earned - paid
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Track which tenant was referred by which affiliate (1 tenant = max 1 affiliate)
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamp('referred_at');
            $table->string('source')->nullable();              // link, wa, manual
            $table->timestamps();

            $table->unique('tenant_id'); // 1 tenant = max 1 affiliate
        });

        // Commission per payment
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_payment_id')->nullable();
            $table->string('plan_name');
            $table->decimal('payment_amount', 15, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
        });

        // Payouts to affiliates
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30)->default('transfer');
            $table->string('reference')->nullable();           // no. transfer
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add affiliate referral code tracking to tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('referred_by_code', 20)->nullable()->after('enabled_modules');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('referred_by_code');
        });
        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_referrals');
        Schema::dropIfExists('affiliates');
    }
};
