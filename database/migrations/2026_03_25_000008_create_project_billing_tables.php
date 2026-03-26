<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Project billing config per project
        Schema::create('project_billing_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('billing_type', ['time_material', 'milestone', 'retainer', 'fixed_price'])->default('time_material');
            $table->decimal('hourly_rate', 12, 2)->default(0);       // for time & material
            $table->decimal('retainer_amount', 15, 2)->default(0);   // monthly retainer
            $table->enum('retainer_cycle', ['monthly', 'quarterly'])->default('monthly');
            $table->decimal('fixed_price', 15, 2)->default(0);       // for fixed price
            $table->date('next_billing_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('project_id');
        });

        // Project milestones (for milestone billing)
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2)->default(0);        // % of total project
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'completed', 'invoiced'])->default('pending');
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Project invoices (link project billing → invoice)
        Schema::create('project_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('billing_type', ['time_material', 'milestone', 'retainer', 'fixed_price']);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('hours', 10, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('labor_amount', 15, 2)->default(0);
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->enum('status', ['draft', 'invoiced', 'paid'])->default('draft');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add billing_status to timesheets
        Schema::table('timesheets', function (Blueprint $table) {
            $table->enum('billing_status', ['unbilled', 'billed'])->default('unbilled')->after('hourly_rate');
            $table->foreignId('project_invoice_id')->nullable()->after('billing_status')
                  ->constrained('project_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropForeign(['project_invoice_id']);
            $table->dropColumn(['billing_status', 'project_invoice_id']);
        });
        Schema::dropIfExists('project_invoices');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_billing_configs');
    }
};
