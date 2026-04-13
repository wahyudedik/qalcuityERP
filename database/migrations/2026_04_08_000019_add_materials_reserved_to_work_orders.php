<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * BUG-MFG-002 FIX: Add materials_reserved column to work_orders
     */
    public function up(): void
    {
        if (!Schema::hasColumn('work_orders', 'materials_reserved')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->boolean('materials_reserved')->default(false)
                    ->after('materials_consumed')
                    ->comment('Whether materials have been reserved for this WO');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('work_orders', 'materials_reserved')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropColumn('materials_reserved');
            });
        }
    }
};
