<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code')->nullable();
                $table->enum('type', ['income', 'expense'])->default('expense');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('number')->unique();
                $table->enum('type', ['income', 'expense']);
                $table->string('reference')->nullable();    // no. SO, PO, dll
                $table->string('reference_type')->nullable(); // sales_order, purchase_order, dll
                $table->date('date');
                $table->decimal('amount', 15, 2);
                $table->string('payment_method')->nullable(); // cash, transfer, dll
                $table->string('account')->nullable();        // nama rekening / kas
                $table->text('description')->nullable();
                $table->string('attachment')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('expense_categories');
    }
};
