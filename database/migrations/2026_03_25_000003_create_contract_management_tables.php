<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Contract templates
        if (!Schema::hasTable('contract_templates')) {
            Schema::create('contract_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('category', 30)->default('service'); // service, lease, supply, maintenance, subscription
                $table->text('body_template')->nullable();          // HTML/text template with placeholders
                $table->text('default_terms')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Contracts
        if (!Schema::hasTable('contracts')) {
            Schema::create('contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('contract_number', 30);
                $table->string('title');
                $table->foreignId('template_id')->nullable()->constrained('contract_templates')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->enum('party_type', ['customer', 'supplier'])->default('customer');
                $table->string('category', 30)->default('service');
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('value', 15, 2)->default(0);        // total contract value
                $table->string('currency_code', 3)->default('IDR');
                $table->enum('billing_cycle', ['one_time', 'monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
                $table->decimal('billing_amount', 15, 2)->default(0); // per cycle
                $table->date('next_billing_date')->nullable();
                $table->boolean('auto_renew')->default(false);
                $table->unsignedSmallInteger('renewal_days_before')->default(30); // notify X days before expiry
                $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'renewed'])->default('draft');
                // SLA
                $table->unsignedSmallInteger('sla_response_hours')->nullable();   // max response time
                $table->unsignedSmallInteger('sla_resolution_hours')->nullable(); // max resolution time
                $table->decimal('sla_uptime_pct', 5, 2)->nullable();             // e.g. 99.90
                $table->text('sla_terms')->nullable();
                // Meta
                $table->text('terms')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('signed_at')->nullable();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
    
                $table->unique(['tenant_id', 'contract_number']);
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'next_billing_date']);
            });
        }

        // Contract billing history
        if (!Schema::hasTable('contract_billings')) {
            Schema::create('contract_billings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->date('billing_date');
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['pending', 'invoiced', 'paid', 'cancelled'])->default('pending');
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
            });
        }

        // SLA incidents / tracking
        if (!Schema::hasTable('contract_sla_logs')) {
            Schema::create('contract_sla_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('incident_type', 30)->default('support'); // support, downtime, delivery_delay
                $table->string('description');
                $table->timestamp('reported_at');
                $table->timestamp('responded_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->boolean('sla_met')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_sla_logs');
        Schema::dropIfExists('contract_billings');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_templates');
    }
};
