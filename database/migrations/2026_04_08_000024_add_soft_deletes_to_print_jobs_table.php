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
        // Add soft deletes to print_jobs table if not exists
        if (Schema::hasTable('print_jobs') && ! Schema::hasColumn('print_jobs', 'deleted_at')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('print_jobs') && Schema::hasColumn('print_jobs', 'deleted_at')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
