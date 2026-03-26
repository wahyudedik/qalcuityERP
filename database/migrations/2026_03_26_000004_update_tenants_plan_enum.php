<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('trial','basic','pro','starter','business','professional','enterprise') NOT NULL DEFAULT 'trial'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tenants SET plan = 'trial' WHERE plan IN ('starter','business','professional')");
        DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('trial','basic','pro','enterprise') NOT NULL DEFAULT 'trial'");
    }
};
