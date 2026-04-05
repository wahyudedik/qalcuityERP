<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            // First, update any invalid roles to 'staff' as default
            DB::table('users')->whereNotIn('role', ['super_admin', 'admin', 'manager', 'staff'])
                ->update(['role' => 'staff']);

            // Then add 'housekeeping' and 'maintenance' roles to enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'staff', 'housekeeping', 'maintenance') NOT NULL DEFAULT 'staff'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            // Remove housekeeping and maintenance roles (will fail if any records use these roles)
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }
};
