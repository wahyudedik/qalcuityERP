<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_model_switch_logs')) {
            Schema::create('ai_model_switch_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('from_model', 100);
                $table->string('to_model', 100);
                $table->enum('reason', ['rate_limit', 'quota_exceeded', 'service_unavailable', 'recovery']);
                $table->text('error_message')->nullable();
                $table->string('request_context', 255)->nullable();
                $table->unsignedInteger('triggered_by_tenant_id')->nullable();
                $table->timestamp('switched_at')->useCurrent();
                $table->timestamps();
    
                $table->index('switched_at', 'idx_switched_at');
                $table->index('reason', 'idx_reason');
                $table->index('triggered_by_tenant_id', 'idx_tenant');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_model_switch_logs');
    }
};
