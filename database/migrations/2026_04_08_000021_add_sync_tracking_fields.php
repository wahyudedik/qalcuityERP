<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG-API-003 FIX: Add tracking fields to marketplace_sync_logs
     */
    public function up(): void
    {
        if (! Schema::hasTable('marketplace_sync_logs')) {
            return;
        }

        Schema::table('marketplace_sync_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('marketplace_sync_logs', 'sync_id')) {
                $table->string('sync_id')->nullable()->after('id')
                    ->comment('Unique ID for sync batch');
            }

            if (! Schema::hasColumn('marketplace_sync_logs', 'data_before')) {
                $table->json('data_before')->nullable()->after('payload')
                    ->comment('Data state before sync');
            }

            if (! Schema::hasColumn('marketplace_sync_logs', 'data_after')) {
                $table->json('data_after')->nullable()->after('data_before')
                    ->comment('Data state after sync');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('marketplace_sync_logs')) {
            return;
        }

        Schema::table('marketplace_sync_logs', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_sync_logs', 'sync_id')) {
                $table->dropColumn('sync_id');
            }
            if (Schema::hasColumn('marketplace_sync_logs', 'data_before')) {
                $table->dropColumn('data_before');
            }
            if (Schema::hasColumn('marketplace_sync_logs', 'data_after')) {
                $table->dropColumn('data_after');
            }
        });
    }
};
