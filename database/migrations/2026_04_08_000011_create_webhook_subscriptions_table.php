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
        if (! Schema::hasTable('webhook_subscriptions')) {
            Schema::create('webhook_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('integration_id')->constrained()->onDelete('cascade');
                $table->string('endpoint_url');
                $table->string('secret_key')->nullable();
                $table->json('events'); // ['order.created', 'product.updated', etc.]
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_triggered_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index(['integration_id', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};
