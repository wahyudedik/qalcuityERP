<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            // Kode akun COA yang digunakan saat auto-posting GL
            if (!Schema::hasColumn('expense_categories', 'coa_account_code')) {
                $table->string('coa_account_code', 20)->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn('coa_account_code');
        });
    }
};
