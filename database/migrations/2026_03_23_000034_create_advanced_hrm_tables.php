<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Manager/atasan untuk org chart
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('user_id')
                  ->constrained('employees')->nullOnDelete();
        });

        // Cuti
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['annual', 'sick', 'maternity', 'paternity', 'unpaid', 'other'])
                  ->default('annual');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days')->default(1);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // Penilaian kinerja
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('employees')->cascadeOnDelete();
            $table->string('period');           // e.g. "Q1 2026", "2026-01"
            $table->enum('period_type', ['monthly', 'quarterly', 'annual'])->default('quarterly');
            // Skor 1-5 per kategori
            $table->unsignedTinyInteger('score_work_quality')->default(3);
            $table->unsignedTinyInteger('score_productivity')->default(3);
            $table->unsignedTinyInteger('score_teamwork')->default(3);
            $table->unsignedTinyInteger('score_initiative')->default(3);
            $table->unsignedTinyInteger('score_attendance')->default(3);
            $table->decimal('overall_score', 4, 2)->default(3.00); // computed avg
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('goals_next_period')->nullable();
            $table->enum('recommendation', ['promote', 'retain', 'pip', 'terminate'])->nullable();
            $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period', 'period_type']);
            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('leave_requests');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
