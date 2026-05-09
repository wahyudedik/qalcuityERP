<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            if (Schema::hasColumn('error_logs', 'ip') && ! Schema::hasColumn('error_logs', 'ip_address')) {
                $table->renameColumn('ip', 'ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            if (Schema::hasColumn('error_logs', 'ip_address') && ! Schema::hasColumn('error_logs', 'ip')) {
                $table->renameColumn('ip_address', 'ip');
            }
        });
    }
};
