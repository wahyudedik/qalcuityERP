<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_orders')) {
            return;
        }

        // Add 'pending_payment' to status ENUM for POS two-step payment flow
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM('pending','pending_payment','confirmed','processing','shipped','delivered','cancelled','completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','completed') NOT NULL DEFAULT 'pending'");
    }
};
