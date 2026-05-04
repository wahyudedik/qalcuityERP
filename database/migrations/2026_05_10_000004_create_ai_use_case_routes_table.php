<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel ai_use_case_routes untuk menyimpan routing rules berbasis use case.
     *
     * Setiap baris memetakan satu use case ke provider dan model tertentu.
     * tenant_id = NULL berarti routing rule berlaku global untuk semua tenant.
     * tenant_id = X berarti routing rule hanya berlaku untuk tenant X (override).
     *
     * Requirements: 1.1, 1.7, 7.2
     */
    public function up(): void
    {
        if (!Schema::hasTable('ai_use_case_routes')) {
            Schema::create('ai_use_case_routes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->nullable(); // NULL = global rule
                $table->string('use_case', 100)->notNull();          // e.g. 'chatbot', 'financial_report'
                $table->string('provider', 50)->notNull();           // 'gemini' | 'anthropic'
                $table->string('model', 100)->nullable();            // NULL = gunakan model default provider
                $table->string('min_plan', 50)->nullable();          // NULL = semua plan; 'professional', dll.
                $table->json('fallback_chain')->nullable();          // ['gemini', 'anthropic'] atau NULL
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
    
                // Unique key: kombinasi use_case + tenant_id.
                // MySQL treats NULL as distinct in unique indexes, sehingga:
                // - (use_case, NULL) = satu global rule per use case
                // - (use_case, tenant_id) = satu override per tenant per use case
                $table->unique(['use_case', 'tenant_id']);
    
                // Index untuk query performa
                $table->index('tenant_id');
                $table->index('use_case');
    
                // Foreign key ke tenants dengan CASCADE delete
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_use_case_routes');
    }
};
