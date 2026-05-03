<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel ai_usage_cost_logs untuk mencatat setiap request AI beserta estimasi biaya.
     *
     * Tabel ini bersifat immutable log — tidak ada kolom updated_at.
     * Setiap baris merepresentasikan satu eksekusi AI yang berhasil, termasuk
     * informasi use case, provider yang digunakan, token count, dan estimasi biaya IDR.
     *
     * Requirements: 6.1, 10.6
     */
    public function up(): void
    {
        Schema::create('ai_usage_cost_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');              // Wajib — setiap log harus terikat ke tenant
            $table->unsignedBigInteger('user_id')->nullable();    // NULL jika request dari sistem/job
            $table->string('use_case', 100)->notNull();           // e.g. 'chatbot', 'financial_report'
            $table->string('provider', 50)->notNull();            // Provider yang sebenarnya digunakan: 'gemini', 'anthropic'
            $table->string('model', 100)->notNull();              // Model yang sebenarnya digunakan
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('estimated_cost_idr', 10, 4)->default(0); // Estimasi biaya dalam IDR
            $table->unsignedInteger('response_time_ms')->nullable();   // Durasi eksekusi dalam milidetik
            $table->boolean('fallback_degraded')->default(false);      // true jika fallback dari heavyweight ke lightweight

            // Immutable log — hanya created_at, tidak ada updated_at
            $table->timestamp('created_at')->nullable();

            // Index untuk query laporan dan monitoring
            $table->index('tenant_id');
            $table->index('use_case');
            $table->index('provider');
            $table->index('created_at');

            // Foreign key ke tenants dengan CASCADE delete
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // Foreign key ke users dengan SET NULL (user bisa dihapus, log tetap ada)
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_cost_logs');
    }
};
