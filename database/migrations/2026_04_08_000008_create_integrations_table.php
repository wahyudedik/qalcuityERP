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
        if (! Schema::hasTable('integrations')) {
            Schema::create('integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('name');
                $table->string('slug')->unique(); // shopify, woocommerce, etc.
                $table->string('type'); // e-commerce, payment, logistics, etc.
                $table->string('status')->default('inactive'); // active, inactive, error
                $table->json('config')->nullable(); // API keys, endpoints, sync settings
                $table->json('oauth_tokens')->nullable(); // access_token, refresh_token, expires_at
                $table->string('sync_frequency')->default('hourly'); // realtime, hourly, daily
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamp('next_sync_at')->nullable();
                $table->json('metadata')->nullable(); // version, features enabled
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Indexes for performance
                $table->index(['tenant_id', 'type']);
                $table->index(['tenant_id', 'status']);
                $table->index(['slug', 'tenant_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
