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
        if (!Schema::hasTable('webhook_deliveries')) {
            Schema::create('webhook_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->constrained('webhook_subscriptions')->onDelete('cascade');
                $table->string('event_type'); // order.created, product.updated, etc.
                $table->json('payload');
                $table->integer('response_code')->nullable();
                $table->text('response_body')->nullable();
                $table->integer('attempt_count')->default(0);
                $table->integer('max_attempts')->default(5);
                $table->string('status')->default('pending'); // pending, delivered, failed
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['subscription_id', 'status']);
                $table->index(['status', 'next_retry_at']);
                $table->index(['event_type', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
