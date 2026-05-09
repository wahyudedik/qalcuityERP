<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('restaurant_menus') && Schema::hasColumn('restaurant_menus', 'type')) {
            // MySQL requires dropping and recreating enum columns to add new values
            DB::statement("ALTER TABLE restaurant_menus MODIFY COLUMN type ENUM('breakfast', 'lunch', 'dinner', 'all_day', 'room_service', 'bar', 'minibar') NOT NULL DEFAULT 'breakfast'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('restaurant_menus') && Schema::hasColumn('restaurant_menus', 'type')) {
            // Remove 'minibar' from enum (note: this will fail if any records have type='minibar')
            DB::statement("ALTER TABLE restaurant_menus MODIFY COLUMN type ENUM('breakfast', 'lunch', 'dinner', 'all_day', 'room_service', 'bar') NOT NULL DEFAULT 'breakfast'");
        }
    }
};
