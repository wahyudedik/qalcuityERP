<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('duration_minutes'); // total menit lembur
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->decimal('overtime_pay', 18, 2)->default(0); // dihitung saat approve
            $table->boolean('included_in_payroll')->default(false);
            $table->string('payroll_period')->nullable(); // Y-m
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'employee_id', 'date']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'payroll_period', 'included_in_payroll'], 'ot_payroll_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
