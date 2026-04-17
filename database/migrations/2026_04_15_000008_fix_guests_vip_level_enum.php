<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Fixes guests.vip_level enum to include 'none' which is used by HotelGenerator::seedGuests().
     * The current enum is: regular, silver, gold, platinum
     * The generator uses: 'gold' for first 2 guests and 'none' for the rest.
     * 'none' is missing from the enum, causing guest inserts to fail.
     */
    public function up(): void
    {
        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'vip_level')) {
            DB::statement("ALTER TABLE guests MODIFY COLUMN vip_level 
                ENUM('regular','silver','gold','platinum','none') NOT NULL DEFAULT 'regular'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'vip_level')) {
            DB::statement("ALTER TABLE guests MODIFY COLUMN vip_level 
                ENUM('regular','silver','gold','platinum') NOT NULL DEFAULT 'regular'");
        }
    }
};
