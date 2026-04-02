<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Mapping COA antar tenant dalam group untuk konsolidasi
        Schema::create('consolidation_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('source_tenant_id');
            $table->unsignedBigInteger('source_account_id');
            $table->unsignedBigInteger('consolidated_account_id')->nullable(); // akun di master COA konsolidasi
            $table->string('mapping_type')->default('direct'); // direct, aggregate, eliminate
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_group_id', 'source_tenant_id'], 'consol_acct_map_group_tenant_idx');
            $table->unique(['source_tenant_id', 'source_account_id'], 'consol_acct_map_tenant_acct_uniq');
        });

        // Master COA untuk konsolidasi (shared across group)
        Schema::create('consolidation_master_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 50);
            $table->string('name');
            $table->string('type'); // asset, liability, equity, revenue, expense
            $table->string('normal_balance'); // debit, credit
            $table->integer('level')->default(1);
            $table->boolean('is_header')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_group_id', 'code']);
        });

        // Elimination entries untuk transaksi intercompany
        Schema::create('consolidation_eliminations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('consolidation_report_id')->nullable();
            $table->string('type'); // intercompany_sale, intercompany_loan, unrealized_profit, investment_elimination
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('related_transaction_id')->nullable(); // link ke intercompany_transactions
            $table->date('date');
            $table->text('description');
            $table->decimal('amount', 18, 2);
            $table->string('status')->default('draft'); // draft, applied, cancelled
            $table->timestamps();

            $table->index(['company_group_id', 'consolidation_report_id'], 'consol_elim_group_report_idx');
        });

        // Detail elimination entries (debit/credit)
        Schema::create('consolidation_elimination_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('elimination_id');
            $table->unsignedBigInteger('master_account_id'); // akun di consolidation_master_accounts
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('elimination_id');
        });

        // Laporan konsolidasi yang di-generate
        Schema::create('consolidation_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('generated_by'); // user_id
            $table->string('report_type'); // balance_sheet, income_statement, cash_flow
            $table->string('period_type'); // monthly, quarterly, yearly
            $table->date('period_start');
            $table->date('period_end');
            $table->json('included_tenants'); // array of tenant_ids
            $table->json('report_data')->nullable(); // cached report result
            $table->string('status')->default('draft'); // draft, finalized, archived
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['company_group_id', 'period_start', 'period_end'], 'consol_reports_group_period_idx');
        });

        // Adjustments manual untuk konsolidasi
        Schema::create('consolidation_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('consolidation_report_id')->nullable();
            $table->unsignedBigInteger('created_by'); // user_id
            $table->string('number'); // adjustment number
            $table->date('date');
            $table->text('description');
            $table->string('status')->default('draft'); // draft, posted
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['company_group_id', 'consolidation_report_id'], 'consol_adj_group_report_idx');
        });

        // Detail adjustment entries
        Schema::create('consolidation_adjustment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adjustment_id');
            $table->unsignedBigInteger('master_account_id');
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('adjustment_id');
        });

        // Ownership percentages untuk partial consolidation
        Schema::create('consolidation_ownership', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->unsignedBigInteger('parent_tenant_id'); // tenant yang memiliki
            $table->unsignedBigInteger('subsidiary_tenant_id'); // tenant yang dimiliki
            $table->decimal('ownership_percentage', 5, 2); // 0.00 - 100.00
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('consolidation_method'); // full, proportional, equity
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_group_id', 'parent_tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consolidation_ownership');
        Schema::dropIfExists('consolidation_adjustment_lines');
        Schema::dropIfExists('consolidation_adjustments');
        Schema::dropIfExists('consolidation_reports');
        Schema::dropIfExists('consolidation_elimination_lines');
        Schema::dropIfExists('consolidation_eliminations');
        Schema::dropIfExists('consolidation_master_accounts');
        Schema::dropIfExists('consolidation_account_mappings');
    }
};
