<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'completed' to status enum for POS transactions
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','completed') NOT NULL DEFAULT 'pending'");

        // Add 'transfer' and 'qris' to payment_type enum for POS
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN payment_type ENUM('cash','credit','transfer','qris') NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN payment_type ENUM('cash','credit') NOT NULL DEFAULT 'cash'");
    }
};
