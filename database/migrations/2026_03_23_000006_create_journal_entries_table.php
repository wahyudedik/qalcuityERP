<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('period_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->string('number', 30)->unique();
                $table->date('date');
                $table->string('description');
                $table->string('reference')->nullable();       // SO/INV/PO number
                $table->string('reference_type')->nullable();  // invoice, purchase_order, etc.
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('currency_code', 3)->default('IDR');
                $table->decimal('currency_rate', 15, 6)->default(1);
                $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
                $table->unsignedBigInteger('reversed_by')->nullable(); // journal_entry_id
                $table->unsignedBigInteger('posted_by')->nullable();
                $table->timestamp('posted_at')->nullable();
                $table->boolean('is_recurring')->default(false);
                $table->unsignedBigInteger('recurring_journal_id')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'date']);
                $table->index(['tenant_id', 'status']);
                $table->index(['reference_type', 'reference_id']);
                $table->foreign('period_id')->references('id')->on('accounting_periods')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('journal_entry_lines')) {
            Schema::create('journal_entry_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('journal_entry_id');
                $table->unsignedBigInteger('account_id');
                $table->decimal('debit', 20, 2)->default(0);
                $table->decimal('credit', 20, 2)->default(0);
                $table->decimal('foreign_amount', 20, 2)->nullable(); // amount in foreign currency
                $table->string('description')->nullable();
                $table->unsignedBigInteger('cost_center_id')->nullable(); // department/branch
                $table->timestamps();

                $table->index('journal_entry_id');
                $table->index('account_id');
                $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->cascadeOnDelete();
                $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
