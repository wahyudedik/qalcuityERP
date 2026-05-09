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
        Schema::create('usage_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('telecom_subscriptions')->nullOnDelete();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->bigInteger('bytes_total')->default(0);
            $table->bigInteger('packets_in')->default(0);
            $table->bigInteger('packets_out')->default(0);
            $table->integer('sessions_count')->default(0);
            $table->integer('session_duration_seconds')->default(0);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('period_type')->default('hourly'); // hourly, daily, monthly
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->integer('peak_bandwidth_kbps')->default(0);
            $table->timestamp('peak_usage_time')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'period_start']);
            $table->index(['tenant_id', 'subscription_id']);
            $table->index(['tenant_id', 'device_id']);
            $table->index(['period_type', 'period_start']);

            $table->foreign('device_id')->references('id')->on('network_devices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_trackings');
    }
};
