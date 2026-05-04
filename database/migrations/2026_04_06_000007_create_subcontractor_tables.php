<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('subcontractors')) {
            Schema::create('subcontractors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('company_name');
                $table->string('contact_person');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('specialization')->nullable(); // electrical, plumbing, structural, etc
                $table->string('license_number')->nullable();
                $table->string('tax_id')->nullable();
                $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
                $table->decimal('rating', 3, 1)->default(0);
                $table->integer('total_projects')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'specialization']);
            });
        }

        if (!Schema::hasTable('subcontractor_contracts')) {
            Schema::create('subcontractor_contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('subcontractor_id')->constrained()->onDelete('cascade');
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->string('contract_number')->unique();
                $table->text('scope_of_work');
                $table->decimal('contract_value', 15, 2);
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['draft', 'active', 'completed', 'terminated'])->default('draft');
                $table->text('payment_terms')->nullable();
                $table->decimal('retention_percentage', 5, 2)->default(5);
                $table->integer('warranty_period_months')->default(12);
                $table->decimal('performance_rating', 3, 1)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['subcontractor_id', 'status']);
                $table->index(['project_id', 'status']);
            });
        }

        if (!Schema::hasTable('subcontractor_payments')) {
            Schema::create('subcontractor_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('contract_id')->constrained('subcontractor_contracts')->onDelete('cascade');
                $table->string('invoice_number')->unique();
                $table->string('billing_period');
                $table->text('work_description');
                $table->decimal('claimed_amount', 15, 2);
                $table->decimal('approved_amount', 15, 2)->default(0);
                $table->decimal('retention_deducted', 15, 2)->default(0);
                $table->decimal('net_payable', 15, 2);
                $table->date('payment_date')->nullable();
                $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
                $table->text('remarks')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['contract_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcontractor_payments');
        Schema::dropIfExists('subcontractor_contracts');
        Schema::dropIfExists('subcontractors');
    }
};
