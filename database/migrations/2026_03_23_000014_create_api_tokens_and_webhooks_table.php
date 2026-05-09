<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // API tokens for tenant REST API access
        if (! Schema::hasTable('api_tokens')) {
            Schema::create('api_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('token', 80)->unique();
                $table->json('abilities')->nullable(); // ['read', 'write', 'delete']
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('token');
            });
        }

        // Outbound webhook subscriptions
        if (! Schema::hasTable('webhook_subscriptions')) {
            Schema::create('webhook_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('url');
                $table->string('secret', 64)->nullable();
                $table->json('events');   // ['invoice.created', 'order.status_changed', ...]
                $table->boolean('is_active')->default(true);
                $table->integer('retry_count')->default(0);
                $table->timestamp('last_triggered_at')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        // Webhook delivery log
        if (! Schema::hasTable('webhook_deliveries')) {
            Schema::create('webhook_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('webhook_subscription_id');
                $table->string('event');
                $table->json('payload');
                $table->integer('response_code')->nullable();
                $table->text('response_body')->nullable();
                $table->string('status')->default('pending'); // pending, success, failed
                $table->integer('attempt')->default(1);
                $table->timestamps();

                $table->index(['webhook_subscription_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_subscriptions');
        Schema::dropIfExists('api_tokens');
    }
};
