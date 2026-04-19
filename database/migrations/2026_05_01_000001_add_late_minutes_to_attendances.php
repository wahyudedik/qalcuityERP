<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom late_minutes ke tabel attendances.
 * Kolom ini menyimpan jumlah menit keterlambatan karyawan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->unsignedSmallInteger('late_minutes')->nullable()->default(0)
                    ->after('overtime_minutes')
                    ->comment('Jumlah menit keterlambatan karyawan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'late_minutes')) {
                $table->dropColumn('late_minutes');
            }
        });
    }
};
