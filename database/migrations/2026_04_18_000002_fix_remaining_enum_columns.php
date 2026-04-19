<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Perbaiki ENUM yang tersisa:
     * 1. work_orders.status — tambahkan 'on_hold'
     * 2. users.role — tambahkan 'affiliate' yang hilang
     * 3. housekeeping_tasks.type — tambahkan 'turndown' (legacy alias)
     *
     * Non-destructive: ALTER MODIFY tidak menghapus data yang sudah ada.
     */
    public function up(): void
    {
        // 1. work_orders.status — tambahkan on_hold
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM(
            'pending',
            'in_progress',
            'on_hold',
            'completed',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");

        // 2. users.role — tambahkan affiliate
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'super_admin',
            'admin',
            'manager',
            'staff',
            'housekeeping',
            'maintenance',
            'kasir',
            'gudang',
            'supervisor',
            'accountant',
            'hr',
            'purchasing',
            'sales',
            'affiliate'
        ) NOT NULL DEFAULT 'staff'");

        // 3. housekeeping_tasks.type — tambahkan turndown (legacy alias)
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type ENUM(
            'checkout_clean',
            'stay_clean',
            'deep_clean',
            'inspection',
            'regular_cleaning',
            'turndown_service',
            'deep_cleaning',
            'turndown'
        ) NOT NULL DEFAULT 'regular_cleaning'");
    }

    public function down(): void
    {
        // Kembalikan ke definisi sebelumnya
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM(
            'pending',
            'in_progress',
            'completed',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'super_admin',
            'admin',
            'manager',
            'staff',
            'housekeeping',
            'maintenance',
            'kasir',
            'gudang',
            'supervisor',
            'accountant',
            'hr',
            'purchasing',
            'sales'
        ) NOT NULL DEFAULT 'staff'");

        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type ENUM(
            'checkout_clean',
            'stay_clean',
            'deep_clean',
            'inspection',
            'regular_cleaning',
            'turndown_service',
            'deep_cleaning'
        ) NOT NULL DEFAULT 'regular_cleaning'");
    }
};
