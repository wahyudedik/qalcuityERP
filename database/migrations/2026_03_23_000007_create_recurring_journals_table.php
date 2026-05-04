<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recurring_journals')) {
            Schema::create('recurring_journals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id');
                $table->string('name');
                $table->string('description')->nullable();
                $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->date('next_run_date');
                $table->date('last_run_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('lines'); // snapshot of journal lines [{account_id, debit, credit, description}]
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active', 'next_run_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_journals');
    }
};
