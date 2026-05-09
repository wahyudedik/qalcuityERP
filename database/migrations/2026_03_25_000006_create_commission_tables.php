<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Commission rules / tiers
        if (! Schema::hasTable('commission_rules')) {
            Schema::create('commission_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->enum('type', ['flat_pct', 'tiered', 'flat_amount'])->default('flat_pct');
                $table->decimal('rate', 8, 2)->default(0);           // flat % or flat amount
                $table->json('tiers')->nullable();                    // [{min:0,max:10000000,rate:2},{min:10000000,max:null,rate:3}]
                $table->enum('basis', ['revenue', 'profit', 'quantity'])->default('revenue');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        // Sales targets per salesperson per period
        if (! Schema::hasTable('sales_targets')) {
            Schema::create('sales_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // salesperson
                $table->foreignId('commission_rule_id')->nullable()->constrained()->nullOnDelete();
                $table->string('period', 7);                          // 2026-03
                $table->decimal('target_amount', 15, 2)->default(0);
                $table->decimal('achieved_amount', 15, 2)->default(0);
                $table->decimal('achievement_pct', 8, 2)->default(0);
                $table->timestamps();

                $table->unique(['tenant_id', 'user_id', 'period']);
            });
        }

        // Commission calculations per salesperson per period
        if (! Schema::hasTable('commission_calculations')) {
            Schema::create('commission_calculations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('commission_rule_id')->nullable()->constrained()->nullOnDelete();
                $table->string('period', 7);
                $table->decimal('total_sales', 15, 2)->default(0);
                $table->unsignedInteger('total_orders')->default(0);
                $table->decimal('commission_amount', 15, 2)->default(0);
                $table->decimal('bonus_amount', 15, 2)->default(0);   // bonus for exceeding target
                $table->decimal('total_payout', 15, 2)->default(0);
                $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'user_id', 'period']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_calculations');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('commission_rules');
    }
};
