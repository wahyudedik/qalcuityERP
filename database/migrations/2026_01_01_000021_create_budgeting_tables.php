<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('budgets')) {
            Schema::create('budgets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('department')->nullable();
                $table->string('period'); // YYYY-MM or YYYY (annual)
                $table->string('period_type')->default('monthly'); // monthly, quarterly, annual
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('realized', 18, 2)->default(0);
                $table->string('category')->nullable(); // expense category
                $table->string('status')->default('active'); // active, closed
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
