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
        if (Schema::hasTable('maintenance_requests') && Schema::hasColumn('maintenance_requests', 'status')) {
            // Add 'reported' status to enum
            DB::statement("ALTER TABLE maintenance_requests MODIFY COLUMN status ENUM('reported', 'open', 'in_progress', 'waiting_parts', 'completed', 'cancelled') NOT NULL DEFAULT 'open'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('maintenance_requests') && Schema::hasColumn('maintenance_requests', 'status')) {
            // Remove 'reported' status (will fail if any records use this status)
            DB::table('maintenance_requests')->where('status', 'reported')->update(['status' => 'open']);
            DB::statement("ALTER TABLE maintenance_requests MODIFY COLUMN status ENUM('open', 'in_progress', 'waiting_parts', 'completed', 'cancelled') NOT NULL DEFAULT 'open'");
        }
    }
};
