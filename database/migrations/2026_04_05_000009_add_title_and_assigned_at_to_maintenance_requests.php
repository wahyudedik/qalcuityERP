<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('maintenance_requests')) {
            Schema::table('maintenance_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('maintenance_requests', 'title')) {
                    $table->string('title')->after('request_number');
                }
                if (!Schema::hasColumn('maintenance_requests', 'assigned_at')) {
                    $table->timestamp('assigned_at')->nullable()->after('assigned_to');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('maintenance_requests')) {
            Schema::table('maintenance_requests', function (Blueprint $table) {
                if (Schema::hasColumn('maintenance_requests', 'title')) {
                    $table->dropColumn('title');
                }
                if (Schema::hasColumn('maintenance_requests', 'assigned_at')) {
                    $table->dropColumn('assigned_at');
                }
            });
        }
    }
};
