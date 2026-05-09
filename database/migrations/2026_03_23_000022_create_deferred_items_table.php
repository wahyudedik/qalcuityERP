<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('deferred_items')) {
            Schema::create('deferred_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('type', ['deferred_revenue', 'prepaid_expense']);
                $table->string('number', 30)->unique();
                $table->string('description');
                $table->decimal('total_amount', 20, 2);
                $table->decimal('recognized_amount', 20, 2)->default(0);
                $table->decimal('remaining_amount', 20, 2);
                $table->date('start_date');
                $table->date('end_date');
                $table->unsignedTinyInteger('total_periods'); // jumlah bulan
                $table->unsignedTinyInteger('recognized_periods')->default(0);
                $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
                // COA references
                $table->unsignedBigInteger('deferred_account_id');   // akun deferred (liability/asset)
                $table->unsignedBigInteger('recognition_account_id'); // akun tujuan (revenue/expense)
                // Optional link ke transaksi asal
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_number')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'type', 'status']);
                $table->index(['tenant_id', 'status']);
                $table->foreign('deferred_account_id')->references('id')->on('chart_of_accounts');
                $table->foreign('recognition_account_id')->references('id')->on('chart_of_accounts');
            });
        }

        if (! Schema::hasTable('deferred_item_schedules')) {
            Schema::create('deferred_item_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deferred_item_id');
                $table->unsignedTinyInteger('period_number'); // 1, 2, 3, ...
                $table->date('recognition_date');
                $table->decimal('amount', 20, 2);
                $table->enum('status', ['pending', 'posted', 'skipped'])->default('pending');
                $table->unsignedBigInteger('journal_entry_id')->nullable();
                $table->timestamps();

                $table->index(['deferred_item_id', 'status']);
                $table->index(['recognition_date', 'status']);
                $table->foreign('deferred_item_id')->references('id')->on('deferred_items')->cascadeOnDelete();
                $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deferred_item_schedules');
        Schema::dropIfExists('deferred_items');
    }
};
