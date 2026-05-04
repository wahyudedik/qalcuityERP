<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracking penggunaan AI per tenant per bulan
        if (!Schema::hasTable('ai_usage_logs')) {
            Schema::create('ai_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('chat_session_id')->nullable();
                $table->string('month', 7);   // format: 2026-03
                $table->integer('message_count')->default(0);
                $table->integer('token_count')->default(0);
                $table->timestamps();
    
                $table->unique(['tenant_id', 'user_id', 'month']);
                $table->index(['tenant_id', 'month']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
