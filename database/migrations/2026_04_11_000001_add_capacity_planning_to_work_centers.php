<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Work Centers enhancement for capacity planning
     */
    public function up(): void
    {
        // Add capacity planning columns to work_centers table
        Schema::table('work_centers', function (Blueprint $table) {
            // Scheduling
            if (!Schema::hasColumn('work_centers', 'start_time')) {
                $table->time('start_time')->default('08:00:00')->after('capacity_per_day');
            }
            if (!Schema::hasColumn('work_centers', 'end_time')) {
                $table->time('end_time')->default('17:00:00')->after('start_time');
            }
            if (!Schema::hasColumn('work_centers', 'break_minutes')) {
                $table->integer('break_minutes')->default(60)->after('end_time');
            }

            // Efficiency
            if (!Schema::hasColumn('work_centers', 'efficiency_percent')) {
                $table->decimal('efficiency_percent', 5, 2)->default(100.00)->after('break_minutes')
                    ->comment('Work center efficiency percentage');
            }

            // Utilization tracking
            if (!Schema::hasColumn('work_centers', 'current_utilization')) {
                $table->decimal('current_utilization', 5, 2)->default(0.00)->after('efficiency_percent')
                    ->comment('Current utilization percentage');
            }
            if (!Schema::hasColumn('work_centers', 'planned_hours_today')) {
                $table->decimal('planned_hours_today', 8, 2)->default(0.00)->after('current_utilization');
            }
            if (!Schema::hasColumn('work_centers', 'actual_hours_today')) {
                $table->decimal('actual_hours_today', 8, 2)->default(0.00)->after('planned_hours_today');
            }

            // Maintenance
            if (!Schema::hasColumn('work_centers', 'last_maintenance_date')) {
                $table->date('last_maintenance_date')->nullable()->after('actual_hours_today');
            }
            if (!Schema::hasColumn('work_centers', 'next_maintenance_date')) {
                $table->date('next_maintenance_date')->nullable()->after('last_maintenance_date');
            }
            if (!Schema::hasColumn('work_centers', 'maintenance_interval_days')) {
                $table->integer('maintenance_interval_days')->default(90)->after('next_maintenance_date');
            }

            // Indexes
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'current_utilization']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_active']);
            $table->dropIndex(['tenant_id', 'current_utilization']);

            $table->dropColumn([
                'start_time',
                'end_time',
                'break_minutes',
                'efficiency_percent',
                'current_utilization',
                'planned_hours_today',
                'actual_hours_today',
                'last_maintenance_date',
                'next_maintenance_date',
                'maintenance_interval_days',
            ]);
        });
    }
};
