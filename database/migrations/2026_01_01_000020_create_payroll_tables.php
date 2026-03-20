<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('period'); // YYYY-MM
            $table->string('status')->default('draft'); // draft, processed, paid
            $table->decimal('total_gross', 18, 2)->default(0);
            $table->decimal('total_deductions', 18, 2)->default(0);
            $table->decimal('total_net', 18, 2)->default(0);
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('payroll_run_id');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('base_salary', 18, 2)->default(0);
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('late_days')->default(0);
            $table->decimal('allowances', 18, 2)->default(0);   // tunjangan
            $table->decimal('overtime_pay', 18, 2)->default(0);
            $table->decimal('deduction_absent', 18, 2)->default(0);
            $table->decimal('deduction_late', 18, 2)->default(0);
            $table->decimal('deduction_other', 18, 2)->default(0);
            $table->decimal('gross_salary', 18, 2)->default(0);
            $table->decimal('tax_pph21', 18, 2)->default(0);
            $table->decimal('bpjs_employee', 18, 2)->default(0);
            $table->decimal('net_salary', 18, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
    }
};
