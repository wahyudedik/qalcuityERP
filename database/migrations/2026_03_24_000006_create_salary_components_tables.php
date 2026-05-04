<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Master komponen gaji per tenant (template)
        if (!Schema::hasTable('salary_components')) {
            Schema::create('salary_components', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');                          // e.g. "Tunjangan Transport"
                $table->string('code')->nullable();              // e.g. "T_TRANSPORT"
                $table->enum('type', ['allowance', 'deduction'])->default('allowance');
                $table->enum('calc_type', ['fixed', 'percent_base'])->default('fixed');
                // fixed: amount langsung; percent_base: % dari gaji pokok
                $table->decimal('default_amount', 18, 2)->default(0);
                $table->boolean('taxable')->default(false);      // masuk perhitungan PPh21?
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'type']);
            });
        }

        // Komponen yang di-assign ke karyawan tertentu (override amount)
        if (!Schema::hasTable('employee_salary_components')) {
            Schema::create('employee_salary_components', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('salary_component_id');
                $table->decimal('amount', 18, 2)->default(0);   // override dari default
                $table->boolean('is_active')->default(true);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->timestamps();
                $table->unique(['employee_id', 'salary_component_id'], 'emp_comp_unique');
                $table->index(['tenant_id', 'employee_id']);
            });
        }

        // Snapshot komponen per payroll item (audit trail)
        if (!Schema::hasTable('payroll_item_components')) {
            Schema::create('payroll_item_components', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_item_id');
                $table->unsignedBigInteger('salary_component_id');
                $table->string('name');
                $table->enum('type', ['allowance', 'deduction']);
                $table->decimal('amount', 18, 2)->default(0);
                $table->timestamps();
                $table->index('payroll_item_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_item_components');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('salary_components');
    }
};
