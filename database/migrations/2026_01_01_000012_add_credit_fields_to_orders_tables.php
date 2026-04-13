<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->enum('payment_type', ['cash', 'credit'])->default('cash')->after('notes');
            $table->date('due_date')->nullable()->after('payment_type');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('payment_type', ['cash', 'credit'])->default('cash')->after('notes');
            $table->date('due_date')->nullable()->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'due_date']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'due_date']);
        });
    }
};
