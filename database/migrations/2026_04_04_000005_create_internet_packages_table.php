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
        if (! Schema::hasTable('internet_packages')) {
            Schema::create('internet_packages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name'); // e.g., "Paket Basic 10Mbps"
                $table->string('code')->unique(); // e.g., "PKG-BASIC-10"
                $table->text('description')->nullable();

                // Bandwidth specifications
                $table->integer('download_speed_mbps'); // in Mbps
                $table->integer('upload_speed_mbps'); // in Mbps
                $table->integer('burst_download_mbps')->nullable(); // Burst speed
                $table->integer('burst_upload_mbps')->nullable();

                // Quota management
                $table->bigInteger('quota_bytes')->default(0); // 0 = unlimited
                $table->enum('quota_period', ['hourly', 'daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
                $table->boolean('rollover_enabled')->default(false); // Rollover unused quota

                // Pricing
                $table->decimal('price', 15, 2);
                $table->decimal('installation_fee', 15, 2)->default(0);
                $table->decimal('overage_price_per_gb', 15, 2)->default(0); // Price per GB after quota exceeded

                // Features
                $table->json('features')->nullable(); // Additional features
                $table->integer('max_devices')->default(1); // Max concurrent devices
                $table->boolean('priority_traffic')->default(false); // QoS priority
                $table->boolean('static_ip')->default(false); // Include static IP
                $table->string('static_ip_address')->nullable();

                // Status & visibility
                $table->boolean('is_active')->default(true);
                $table->boolean('is_public')->default(true); // Show to customers
                $table->integer('sort_order')->default(0);

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['tenant_id', 'is_active']);
                $table->index(['tenant_id', 'is_public']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internet_packages');
    }
};
