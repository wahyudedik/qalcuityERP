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
        if (! Schema::hasTable('daily_site_reports')) {
            Schema::create('daily_site_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->date('report_date');
                $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
                $table->string('weather_condition')->nullable(); // sunny, rainy, cloudy, windy
                $table->decimal('temperature', 5, 1)->nullable();
                $table->text('work_performed')->nullable();
                $table->integer('manpower_count')->default(0);
                $table->text('equipment_used')->nullable();
                $table->text('materials_received')->nullable();
                $table->text('issues_encountered')->nullable();
                $table->integer('safety_incidents')->default(0);
                $table->decimal('progress_percentage', 5, 2)->default(0);
                $table->json('photos')->nullable(); // JSON array of photo paths
                $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'project_id']);
                $table->index(['tenant_id', 'report_date']);
                $table->index(['project_id', 'report_date']);
                $table->index(['tenant_id', 'status']);
            });
        }

        if (! Schema::hasTable('site_labor_logs')) {
            Schema::create('site_labor_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('daily_report_id')->constrained('daily_site_reports')->onDelete('cascade');
                $table->string('worker_name');
                $table->string('worker_type')->default('unskilled'); // skilled, unskilled, supervisor, foreman
                $table->string('trade')->nullable(); // carpenter, mason, electrician, plumber, etc
                $table->decimal('hours_worked', 5, 2)->default(8);
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);
                $table->string('attendance_status')->default('present'); // present, absent, late, overtime
                $table->timestamps();

                $table->index(['tenant_id', 'daily_report_id']);
                $table->index(['daily_report_id', 'worker_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_labor_logs');
        Schema::dropIfExists('daily_site_reports');
    }
};
