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
        if (! Schema::hasTable('integration_configs')) {
            Schema::create('integration_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('integration_id')->constrained()->onDelete('cascade');
                $table->string('key'); // api_key, webhook_secret, etc.
                $table->text('value')->nullable(); // Encrypted value
                $table->string('category')->default('general'); // api, sync, webhook, mapping
                $table->boolean('is_encrypted')->default(false);
                $table->timestamps();

                $table->unique(['integration_id', 'key']);
                $table->index(['tenant_id', 'category']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_configs');
    }
};
