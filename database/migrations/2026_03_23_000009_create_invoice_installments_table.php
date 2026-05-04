<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_installments')) {
            Schema::create('invoice_installments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('invoice_id');
                $table->tinyInteger('installment_number');
                $table->decimal('amount', 20, 2);
                $table->date('due_date');
                $table->decimal('paid_amount', 20, 2)->default(0);
                $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
                $table->date('paid_date')->nullable();
                $table->string('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status', 'due_date']);
                $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_installments');
    }
};
