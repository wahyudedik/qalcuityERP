<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surgery_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('surgery_schedules', 'tenant_id')) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('surgery_schedules', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add index separately to handle case where column exists but index doesn't
        Schema::table('surgery_schedules', function (Blueprint $table) {
            $sm = Schema::getConnection()->getSchemaBuilder();
            $indexes = collect($sm->getIndexes('surgery_schedules'))->pluck('name')->toArray();

            if (! in_array('idx_surgery_schedules_tenant_id', $indexes)) {
                $table->index('tenant_id', 'idx_surgery_schedules_tenant_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surgery_schedules', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex('idx_surgery_schedules_tenant_id');
            $table->dropColumn(['tenant_id', 'deleted_at']);
        });
    }
};
