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
        if (! Schema::hasTable('bandwidth_allocations')) {
            Schema::create('bandwidth_allocations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('device_id'); // Network device
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->unsignedBigInteger('hotspot_user_id')->nullable();

                // Allocation details
                $table->string('allocation_name'); // e.g., "Customer A - Priority"
                $table->enum('allocation_type', ['subscription', 'hotspot', 'global', 'custom'])->default('subscription');

                // Bandwidth settings
                $table->integer('max_download_kbps'); // Maximum download in Kbps
                $table->integer('max_upload_kbps'); // Maximum upload in Kbps
                $table->integer('guaranteed_download_kbps')->default(0); // Minimum guaranteed
                $table->integer('guaranteed_upload_kbps')->default(0);

                // Queue settings (for QoS)
                $table->integer('priority')->default(8); // 1-16, lower = higher priority (MikroTik style)
                $table->string('queue_type')->default('simple'); // simple, pcq, hfsc, etc
                $table->json('queue_parameters')->nullable(); // Advanced queue config

                // Time-based rules
                $table->json('time_rules')->nullable(); // Different limits at different times
                $table->boolean('is_active')->default(true);
                $table->timestamp('active_from')->nullable();
                $table->timestamp('active_until')->nullable();

                // Monitoring
                $table->bigInteger('current_usage_bytes')->default(0);
                $table->timestamp('last_updated_at')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('cascade');
                $table->foreign('subscription_id')->references('id')->on('telecom_subscriptions')->onDelete('cascade');
                $table->foreign('hotspot_user_id')->references('id')->on('hotspot_users')->onDelete('cascade');

                $table->index(['tenant_id', 'device_id', 'is_active']);
                $table->index(['subscription_id']);
                $table->index(['hotspot_user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bandwidth_allocations');
    }
};
