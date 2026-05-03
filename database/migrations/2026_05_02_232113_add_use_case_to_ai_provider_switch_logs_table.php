<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom use_case ke tabel ai_provider_switch_logs
     * untuk mencatat use case yang sedang diproses saat fallback terjadi.
     *
     * Requirements: 7.3
     */
    public function up(): void
    {
        Schema::table('ai_provider_switch_logs', function (Blueprint $table) {
            $table->string('use_case', 100)->nullable()->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_provider_switch_logs', function (Blueprint $table) {
            $table->dropColumn('use_case');
        });
    }
};
