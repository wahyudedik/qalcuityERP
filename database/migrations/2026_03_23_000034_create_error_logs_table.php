<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('error_logs')) {
            Schema::create('error_logs', function (Blueprint $table) {
                $table->id();
                $table->string('level', 20)->default('error'); // error, warning, critical, info
                $table->string('message', 500);
                $table->text('trace')->nullable();
                $table->string('file', 300)->nullable();
                $table->unsignedInteger('line')->nullable();
                $table->string('url', 500)->nullable();
                $table->string('method', 10)->nullable();
                $table->string('ip', 45)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('user_agent', 300)->nullable();
                $table->json('context')->nullable();
                $table->boolean('is_resolved')->default(false);
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
    
                $table->index(['level', 'created_at']);
                $table->index(['is_resolved', 'created_at']);
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
