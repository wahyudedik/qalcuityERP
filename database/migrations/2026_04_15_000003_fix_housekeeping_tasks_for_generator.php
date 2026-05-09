<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes housekeeping_tasks table for HotelGenerator::seedHousekeepingTasks():
     * 1. Adds actual_duration column (missing from recreated table in 2026_04_05_000012)
     * 2. Expands type enum to include 'regular_cleaning' and 'turndown_service'
     *    which are used by the generator but not in the current enum definition.
     */
    public function up(): void
    {
        if (! Schema::hasTable('housekeeping_tasks')) {
            return;
        }

        // Add actual_duration column if missing
        if (! Schema::hasColumn('housekeeping_tasks', 'actual_duration')) {
            Schema::table('housekeeping_tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('housekeeping_tasks', 'actual_duration')) {
                    $table->integer('actual_duration')->nullable()->after('estimated_duration');
                }
            });
        }

        // Expand enum type to include generator values
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type 
            ENUM('checkout_clean','stay_clean','deep_clean','inspection','regular_cleaning','turndown_service') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('housekeeping_tasks')) {
            return;
        }

        // Revert enum to original values
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type 
            ENUM('checkout_clean','stay_clean','deep_clean','inspection') NOT NULL");

        // Drop actual_duration if it was added by this migration
        if (Schema::hasColumn('housekeeping_tasks', 'actual_duration')) {
            Schema::table('housekeeping_tasks', function (Blueprint $table) {
                $table->dropColumn('actual_duration');
            });
        }
    }
};
