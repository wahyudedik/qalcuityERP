<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel ai_provider_switch_logs untuk mencatat setiap peralihan provider AI.
     *
     * Requirements: 3.4, 7.2
     */
    public function up(): void
    {
        Schema::create('ai_provider_switch_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();  // NULL = system-level switch
            $table->string('from_provider', 50);                  // 'gemini', 'anthropic'
            $table->string('to_provider', 50);
            $table->string('reason', 100);                        // 'rate_limit', 'server_error', 'quota_exceeded'
            $table->string('use_case', 100)->nullable();          // use case yang sedang diproses saat fallback terjadi
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenant_id');
            $table->index('created_at');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_switch_logs');
    }
};
