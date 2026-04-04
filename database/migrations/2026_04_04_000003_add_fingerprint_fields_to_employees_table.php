<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('fingerprint_uid')->nullable()->after('employee_id'); // UID fingerprint untuk karyawan
            $table->boolean('fingerprint_registered')->default(false)->after('fingerprint_uid'); // Status registrasi fingerprint

            $table->index(['tenant_id', 'fingerprint_uid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['fingerprint_uid', 'fingerprint_registered']);
        });
    }
};
