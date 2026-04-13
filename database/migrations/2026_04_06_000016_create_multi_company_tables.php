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
        // 1. Company Groups (Parent Companies)
        if (!Schema::hasTable('company_groups')) {
            Schema::create('company_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->foreignId('parent_tenant_id')->constrained('tenants')->onDelete('cascade'); // Parent company
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable(); // Group-wide settings
                $table->timestamps();

                $table->index('is_active');
            });
        }

        // 2. Tenant-Group Relationships (Subsidiaries)
        if (!Schema::hasTable('tenant_group_members')) {
            Schema::create('tenant_group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('ownership_percentage')->default('100.00'); // Ownership %
                $table->date('joined_date');
                $table->date('exited_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('role')->default('subsidiary'); // parent, subsidiary, associate, joint_venture
                $table->timestamps();

                $table->unique(['company_group_id', 'tenant_id']);
                $table->index('is_active');
            });
        }

        // 3. Inter-Company Transactions
        if (!Schema::hasTable('inter_company_transactions')) {
            Schema::create('inter_company_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained()->onDelete('cascade');
                $table->foreignId('from_tenant_id')->constrained('tenants')->onDelete('cascade'); // Sender
                $table->foreignId('to_tenant_id')->constrained('tenants')->onDelete('cascade'); // Receiver
                $table->string('transaction_type'); // sale, purchase, loan, transfer, service_fee
                $table->string('reference_type')->nullable(); // invoice, bill, journal_entry
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->decimal('amount', 15, 2);
                $table->string('currency')->default('IDR');
                $table->decimal('exchange_rate', 12, 6)->default(1.000000);
                $table->date('transaction_date');
                $table->date('due_date')->nullable();
                $table->string('status')->default('pending'); // pending, approved, completed, cancelled
                $table->text('description')->nullable();
                $table->json('line_items')->nullable(); // Transaction details
                $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['company_group_id', 'status']);
                $table->index(['from_tenant_id', 'to_tenant_id']);
                $table->index('transaction_date');
            });
        }

        // 4. Inter-Company Accounts (Receivables/Payables)
        if (!Schema::hasTable('inter_company_accounts')) {
            Schema::create('inter_company_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained()->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('counterparty_tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('account_type'); // receivable, payable
                $table->decimal('balance', 15, 2)->default(0.00);
                $table->string('currency')->default('IDR');
                $table->date('last_reconciliation_date')->nullable();
                $table->timestamps();

                // Custom name to avoid MySQL 64 char limit
                $table->unique(['company_group_id', 'tenant_id', 'counterparty_tenant_id', 'account_type'], 'inter_co_accnt_unique');
                $table->index('balance');
            });
        }

        // 5. Consolidated Financial Reports
        if (!Schema::hasTable('consolidated_reports')) {
            Schema::create('consolidated_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained()->onDelete('cascade');
                $table->string('report_type'); // balance_sheet, income_statement, cash_flow
                $table->date('period_start');
                $table->date('period_end');
                $table->string('currency')->default('IDR');
                $table->json('report_data'); // Consolidated financial data
                $table->json('elimination_entries')->nullable(); // Inter-company eliminations
                $table->json('subsidiary_contributions')->nullable(); // Breakdown by subsidiary
                $table->string('status')->default('draft'); // draft, finalized, published
                $table->foreignId('prepared_by_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['company_group_id', 'report_type']);
                $table->index(['period_start', 'period_end']);
                $table->index('status');
            });
        }

        // 6. Shared Services
        if (!Schema::hasTable('shared_services')) {
            Schema::create('shared_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained()->onDelete('cascade');
                $table->foreignId('provider_tenant_id')->constrained('tenants')->onDelete('cascade'); // Who provides
                $table->string('service_name'); // HR, IT, Accounting, Legal, etc.
                $table->text('description')->nullable();
                $table->string('billing_method')->default('allocation'); // allocation, fixed_fee, usage_based
                $table->decimal('fixed_fee', 15, 2)->nullable();
                $table->json('allocation_rules')->nullable(); // How to allocate costs
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['company_group_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('shared_service_subscriptions')) {
            Schema::create('shared_service_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shared_service_id')->constrained()->onDelete('cascade');
                $table->foreignId('subscriber_tenant_id')->constrained('tenants')->onDelete('cascade'); // Who uses
                $table->decimal('allocation_percentage', 5, 2)->default(0.00); // % of cost
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Custom name to avoid MySQL 64 char limit
                $table->unique(['shared_service_id', 'subscriber_tenant_id'], 'shared_svc_sub_unique');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('shared_service_billings')) {
            Schema::create('shared_service_billings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shared_service_id')->constrained()->onDelete('cascade');
                $table->foreignId('subscriber_tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->date('billing_period_start');
                $table->date('billing_period_end');
                $table->decimal('amount', 15, 2);
                $table->string('currency')->default('IDR');
                $table->string('status')->default('pending'); // pending, invoiced, paid
                $table->foreignId('invoice_id')->nullable(); // Reference to actual invoice
                $table->text('calculation_details')->nullable();
                $table->timestamps();

                $table->index(['shared_service_id', 'status']);
                $table->index('billing_period_start');
            });
        }

        // 7. Cross-Entity Inventory Transfers
        if (!Schema::hasTable('inventory_transfers')) {
            Schema::create('inventory_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_group_id')->constrained()->onDelete('cascade');
                $table->foreignId('from_tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('to_tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('transfer_number')->unique();
                $table->date('transfer_date');
                $table->date('expected_arrival_date')->nullable();
                $table->date('actual_arrival_date')->nullable();
                $table->string('status')->default('draft'); // draft, in_transit, received, cancelled
                $table->string('shipping_method')->nullable();
                $table->string('tracking_number')->nullable();
                $table->decimal('shipping_cost', 15, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('received_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['company_group_id', 'status']);
                $table->index('transfer_date');
            });
        }

        if (!Schema::hasTable('inventory_transfer_items')) {
            Schema::create('inventory_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_transfer_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->integer('quantity_requested');
                $table->integer('quantity_sent');
                $table->integer('quantity_received')->default(0);
                $table->decimal('unit_cost', 15, 2);
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('inventory_transfer_id');
            });
        }

        // 8. Inter-Company Elimination Entries
        if (!Schema::hasTable('elimination_entries')) {
            Schema::create('elimination_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consolidated_report_id')->constrained()->onDelete('cascade');
                $table->string('entry_type'); // revenue, expense, receivable, payable, inventory
                $table->foreignId('from_tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('to_tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->text('description')->nullable();
                $table->json('original_transactions')->nullable(); // References to original transactions
                $table->timestamps();

                $table->index('consolidated_report_id');
                $table->index('entry_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elimination_entries');
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
        Schema::dropIfExists('shared_service_billings');
        Schema::dropIfExists('shared_service_subscriptions');
        Schema::dropIfExists('shared_services');
        Schema::dropIfExists('consolidated_reports');
        Schema::dropIfExists('inter_company_accounts');
        Schema::dropIfExists('inter_company_transactions');
        Schema::dropIfExists('tenant_group_members');
        Schema::dropIfExists('company_groups');
    }
};
