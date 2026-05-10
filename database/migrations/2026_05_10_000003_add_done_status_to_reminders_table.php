<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE reminders MODIFY COLUMN status ENUM('pending', 'sent', 'dismissed', 'done') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE reminders MODIFY COLUMN status ENUM('pending', 'sent', 'dismissed') DEFAULT 'pending'");
    }
};
