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
        if (!Schema::hasTable('hotspot_users')) {
            Schema::create('hotspot_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->unsignedBigInteger('device_id'); // Router/AP that manages this user
    
                // User credentials
                $table->string('username')->unique();
                $table->string('password_encrypted');
                $table->string('mac_address')->nullable();
    
                // Access control
                $table->enum('auth_type', ['hotspot', 'pppoe', 'static'])->default('hotspot');
                $table->boolean('is_active')->default(true);
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('expires_at')->nullable();
    
                // Bandwidth limits
                $table->integer('rate_limit_download_kbps')->nullable(); // in Kbps
                $table->integer('rate_limit_upload_kbps')->nullable();
                $table->integer('burst_limit_download_kbps')->nullable();
                $table->integer('burst_limit_upload_kbps')->nullable();
                $table->integer('burst_threshold_kbps')->nullable();
                $table->integer('burst_time_seconds')->nullable();
    
                // Quota
                $table->bigInteger('quota_bytes')->default(0); // 0 = unlimited
                $table->bigInteger('quota_used_bytes')->default(0);
                $table->timestamp('quota_reset_at')->nullable();
    
                // Session info
                $table->boolean('is_online')->default(false);
                $table->string('current_ip_address')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->timestamp('last_logout_at')->nullable();
                $table->integer('total_sessions')->default(0);
                $table->integer('total_uptime_seconds')->default(0);
    
                // Additional
                $table->json('router_user_profile')->nullable(); // Router-specific profile data
                $table->text('notes')->nullable();
    
                $table->timestamps();
                $table->softDeletes();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('subscription_id')->references('id')->on('telecom_subscriptions')->onDelete('set null');
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('cascade');
    
                $table->index(['tenant_id', 'is_active']);
                $table->index(['tenant_id', 'is_online']);
                $table->index(['subscription_id']);
                $table->unique(['tenant_id', 'username']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_users');
    }
};
