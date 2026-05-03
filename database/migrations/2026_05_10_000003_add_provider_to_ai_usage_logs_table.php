<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom provider ke tabel ai_usage_logs untuk mencatat provider yang digunakan per request.
     *
     * Requirements: 7.1
     */
    public function up(): void
    {
        // Gunakan Schema::hasColumn() untuk menghindari error jika kolom sudah ada
        if (!Schema::hasColumn('ai_usage_logs', 'provider')) {
            Schema::table('ai_usage_logs', function (Blueprint $table) {
                $table->string('provider', 50)->nullable()->default(null)->after('token_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ai_usage_logs', 'provider')) {
            Schema::table('ai_usage_logs', function (Blueprint $table) {
                $table->dropColumn('provider');
            });
        }
    }
};
