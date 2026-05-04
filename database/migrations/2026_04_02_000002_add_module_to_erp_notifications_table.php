<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('erp_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_notifications', 'module')) {
                $table->string('module', 50)->nullable()->after('type')->index();
            }
        });

        // Backfill existing notifications
        DB::table('erp_notifications')->where('type', 'like', '%stock%')->orWhere('type', 'like', '%expir%')->update(['module' => 'inventory']);
        DB::table('erp_notifications')->where('type', 'like', '%invoice%')->orWhere('type', 'like', '%budget%')->update(['module' => 'finance']);
        DB::table('erp_notifications')->where('type', 'like', '%report%')->orWhere('type', 'like', '%attendance%')->update(['module' => 'hrm']);
        DB::table('erp_notifications')->where('type', 'like', '%ai%')->update(['module' => 'ai']);
        DB::table('erp_notifications')->whereNull('module')->update(['module' => 'system']);
    }

    public function down(): void
    {
        Schema::table('erp_notifications', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }
};
