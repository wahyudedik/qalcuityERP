<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes housekeeping_tasks.type enum to include 'deep_cleaning' which is used by
     * HotelGenerator::seedHousekeepingTasks(). The current enum has 'deep_clean' but
     * the generator uses 'deep_cleaning' (with 'ing' suffix).
     *
     * Current enum: checkout_clean, stay_clean, deep_clean, inspection, regular_cleaning, turndown_service
     * Generator uses: regular_cleaning, turndown_service, deep_cleaning, inspection
     * Missing: deep_cleaning (generator uses this, enum has 'deep_clean')
     */
    public function up(): void
    {
        if (Schema::hasTable('housekeeping_tasks') && Schema::hasColumn('housekeeping_tasks', 'type')) {
            DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type 
                ENUM('checkout_clean','stay_clean','deep_clean','inspection','regular_cleaning','turndown_service','deep_cleaning') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('housekeeping_tasks') && Schema::hasColumn('housekeeping_tasks', 'type')) {
            DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type 
                ENUM('checkout_clean','stay_clean','deep_clean','inspection','regular_cleaning','turndown_service') NOT NULL");
        }
    }
};
