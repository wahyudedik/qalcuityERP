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
        if (!Schema::hasTable('usage_tracking')) {
            Schema::create('usage_tracking', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('subscription_id');
                $table->unsignedBigInteger('device_id')->nullable();
    
                // Usage metrics
                $table->bigInteger('bytes_in')->default(0); // Download bytes
                $table->bigInteger('bytes_out')->default(0); // Upload bytes
                $table->bigInteger('bytes_total')->default(0); // Total bytes
                $table->integer('packets_in')->default(0);
                $table->integer('packets_out')->default(0);
                $table->integer('sessions_count')->default(0); // Number of sessions
    
                // Time tracking
                $table->integer('session_duration_seconds')->default(0);
                $table->timestamp('first_seen_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
    
                // Period tracking
                $table->enum('period_type', ['hourly', 'daily', 'weekly', 'monthly'])->default('daily');
                $table->timestamp('period_start');
                $table->timestamp('period_end');
    
                // Peak usage
                $table->integer('peak_bandwidth_kbps')->default(0);
                $table->timestamp('peak_usage_time')->nullable();
    
                // Source info
                $table->string('ip_address')->nullable();
                $table->string('mac_address')->nullable();
                $table->json('additional_data')->nullable(); // Router-specific data
    
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('subscription_id')->references('id')->on('telecom_subscriptions')->onDelete('cascade');
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('set null');
    
                // Indexes for fast querying
                $table->index(['tenant_id', 'subscription_id', 'period_start']);
                $table->index(['tenant_id', 'period_type', 'period_start']);
                $table->index(['subscription_id', 'period_start', 'period_end']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_tracking');
    }
};
