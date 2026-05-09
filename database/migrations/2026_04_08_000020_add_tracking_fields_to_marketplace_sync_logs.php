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
        $tableChecks = [
            'marketplace_sync_logs' => [
                'sync_id' => function (Blueprint $table) {
                    $table->string('sync_id')->nullable()->after('id')
                        ->comment('Unique ID for sync batch');
                },
                'data_before' => function (Blueprint $table) {
                    $table->json('data_before')->nullable()->after('payload')
                        ->comment('Data state before sync');
                },
                'data_after' => function (Blueprint $table) {
                    $table->json('data_after')->nullable()->after('data_before')
                        ->comment('Data state after sync');
                },
            ],
        ];

        foreach ($tableChecks as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName) {
                    foreach ($columns as $columnName => $definition) {
                        if (! Schema::hasColumn($tableName, $columnName)) {
                            $definition($table);
                        }
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('marketplace_sync_logs')) {
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
    }
};
