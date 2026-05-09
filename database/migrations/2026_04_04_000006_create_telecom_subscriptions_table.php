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
        if (! Schema::hasTable('telecom_subscriptions')) {
            Schema::create('telecom_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('package_id');
                $table->unsignedBigInteger('device_id')->nullable(); // Assigned network device

                // Subscription details
                $table->string('subscription_number')->unique(); // e.g., "SUB-2026-0001"
                $table->enum('status', ['pending', 'active', 'suspended', 'cancelled', 'expired'])->default('pending');
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('suspended_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('expires_at')->nullable();

                // Billing cycle
                $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
                $table->date('next_billing_date');
                $table->date('last_billing_date')->nullable();

                // Quota tracking
                $table->bigInteger('quota_used_bytes')->default(0);
                $table->bigInteger('quota_reset_bytes')->default(0);
                $table->timestamp('quota_period_start')->nullable();
                $table->timestamp('quota_period_end')->nullable();
                $table->boolean('quota_exceeded')->default(false);

                // Network credentials
                $table->string('hotspot_username')->nullable();
                $table->string('hotspot_password_encrypted')->nullable();
                $table->string('pppoe_username')->nullable();
                $table->string('pppoe_password_encrypted')->nullable();
                $table->string('static_ip_address')->nullable();
                $table->string('mac_address_registered')->nullable();

                // Service level
                $table->integer('priority_level')->default(0); // 0-10, higher = more priority
                $table->decimal('current_price', 15, 2); // Price at subscription time
                $table->text('notes')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('package_id')->references('id')->on('internet_packages')->onDelete('restrict');
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('set null');

                $table->index(['tenant_id', 'status']);
                $table->index(['customer_id', 'status']);
                $table->index(['tenant_id', 'expires_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telecom_subscriptions');
    }
};
