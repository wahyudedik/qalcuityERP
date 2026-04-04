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
        Schema::create('network_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();

            // Alert details
            $table->string('alert_type'); // device_offline, high_usage, quota_exceeded, etc
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title');
            $table->text('message');
            $table->enum('status', ['new', 'acknowledged', 'resolved', 'ignored'])->default('new');

            // Threshold info
            $table->json('threshold_data')->nullable(); // What threshold was exceeded
            $table->json('current_metrics')->nullable(); // Current values when alert triggered

            // Notification
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->json('notified_users')->nullable(); // User IDs notified

            // Resolution
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('network_devices')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('telecom_subscriptions')->onDelete('cascade');
            $table->foreign('acknowledged_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'severity']);
            $table->index(['device_id', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_alerts');
    }
};
