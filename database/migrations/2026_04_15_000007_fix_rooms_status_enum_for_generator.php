<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes rooms.status enum to include 'dirty' which is used by HotelGenerator::seedRooms().
     * The current enum is: available, occupied, maintenance, cleaning, blocked, out_of_order
     * The generator uses: available, occupied, dirty, maintenance
     * 'dirty' is missing from the enum, causing room inserts to fail.
     */
    public function up(): void
    {
        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'status')) {
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status 
                ENUM('available','occupied','maintenance','cleaning','blocked','out_of_order','dirty') NULL DEFAULT 'available'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'status')) {
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status 
                ENUM('available','occupied','maintenance','cleaning','blocked','out_of_order') NULL DEFAULT 'available'");
        }
    }
};
