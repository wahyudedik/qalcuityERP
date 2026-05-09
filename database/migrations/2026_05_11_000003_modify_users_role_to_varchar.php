<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert role column from ENUM to VARCHAR(100) to support custom role references
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(100) NOT NULL DEFAULT 'staff'");

        // Add composite index for tenant-scoped role queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'role'], 'users_tenant_id_role_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the composite index
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_tenant_id_role_index');
        });

        // Revert custom role values back to 'staff' before reverting to ENUM
        DB::statement("UPDATE users SET role = 'staff' WHERE role NOT IN ('super_admin', 'admin', 'manager', 'staff', 'housekeeping', 'maintenance', 'kasir', 'gudang', 'supervisor', 'accountant', 'hr', 'purchasing', 'sales', 'affiliate')");

        // Revert to ENUM with all known values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'staff', 'housekeeping', 'maintenance', 'kasir', 'gudang', 'supervisor', 'accountant', 'hr', 'purchasing', 'sales', 'affiliate') NOT NULL DEFAULT 'staff'");
    }
};
