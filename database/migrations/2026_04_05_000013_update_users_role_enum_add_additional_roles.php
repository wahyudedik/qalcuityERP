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
            DB::table('users')->whereNotIn('role', ['super_admin', 'admin', 'manager', 'staff', 'housekeeping', 'maintenance'])
                ->update(['role' => 'staff']);

            // Add additional roles: kasir, gudang, and other common roles
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'staff', 'housekeeping', 'maintenance', 'kasir', 'gudang', 'supervisor', 'accountant', 'hr', 'purchasing', 'sales') NOT NULL DEFAULT 'staff'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            // Update roles that will be removed to 'staff'
            DB::table('users')->whereIn('role', ['kasir', 'gudang', 'supervisor', 'accountant', 'hr', 'purchasing', 'sales'])
                ->update(['role' => 'staff']);

            // Remove additional roles
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'staff', 'housekeeping', 'maintenance') NOT NULL DEFAULT 'staff'");
        }
    }
};
