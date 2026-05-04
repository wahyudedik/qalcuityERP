<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * TASK-2.13: Enhance Work Order with scheduling and scrap tracking
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Production scheduling fields
            if (!Schema::hasColumn('work_orders', 'planned_start_date')) {
                $table->date('planned_start_date')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('work_orders', 'planned_end_date')) {
                $table->date('planned_end_date')->nullable()->after('planned_start_date');
            }
            if (!Schema::hasColumn('work_orders', 'actual_start_date')) {
                $table->date('actual_start_date')->nullable()->after('planned_end_date');
            }
            if (!Schema::hasColumn('work_orders', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('actual_start_date');
            }
            if (!Schema::hasColumn('work_orders', 'priority')) {
                $table->integer('priority')->default(3)->after('actual_end_date'); // 1=Urgent, 2=High, 3=Normal, 4=Low
                $table->string('production_line')->nullable()->after('priority');
            }

            // Scrap/Waste tracking
            if (!Schema::hasColumn('work_orders', 'scrap_quantity')) {
                $table->decimal('scrap_quantity', 10, 3)->default(0)->after('total_cost');
            }
            if (!Schema::hasColumn('work_orders', 'scrap_cost')) {
                $table->decimal('scrap_cost', 12, 2)->default(0)->after('scrap_quantity');
            }
            if (!Schema::hasColumn('work_orders', 'scrap_reason')) {
                $table->string('scrap_reason')->nullable()->after('scrap_cost');
            }
            if (!Schema::hasColumn('work_orders', 'rework_quantity')) {
                $table->decimal('rework_quantity', 10, 3)->default(0)->after('scrap_reason');
            }
            if (!Schema::hasColumn('work_orders', 'rework_cost')) {
                $table->decimal('rework_cost', 12, 2)->default(0)->after('rework_quantity');
            }

            // Progress tracking
            if (!Schema::hasColumn('work_orders', 'progress_percent')) {
                $table->decimal('progress_percent', 5, 2)->default(0)->after('rework_cost');
            }
            if (!Schema::hasColumn('work_orders', 'progress_stage')) {
                $table->string('progress_stage')->nullable()->after('progress_percent'); // setup, processing, finishing, qc
    
                // Additional metrics
                $table->decimal('efficiency_rate', 5, 2)->nullable()->after('progress_stage'); // actual vs planned
                $table->decimal('schedule_variance', 5, 2)->nullable()->after('efficiency_rate'); // days ahead/behind
            
            }});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'planned_start_date',
                'planned_end_date',
                'actual_start_date',
                'actual_end_date',
                'priority',
                'production_line',
                'scrap_quantity',
                'scrap_cost',
                'scrap_reason',
                'rework_quantity',
                'rework_cost',
                'progress_percent',
                'progress_stage',
                'efficiency_rate',
                'schedule_variance',
            ]);
        });
    }
};
