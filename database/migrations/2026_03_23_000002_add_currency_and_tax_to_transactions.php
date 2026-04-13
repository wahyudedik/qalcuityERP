<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales Orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('IDR')->after('total');
            $table->decimal('currency_rate', 15, 6)->default(1)->after('currency_code');
            $table->unsignedBigInteger('tax_rate_id')->nullable()->after('currency_rate');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate_id');
        });

        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('IDR')->after('notes');
            $table->decimal('currency_rate', 15, 6)->default(1)->after('currency_code');
            $table->unsignedBigInteger('tax_rate_id')->nullable()->after('currency_rate');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate_id');
            $table->decimal('subtotal_amount', 15, 2)->default(0)->after('tax_amount');
        });

        // Purchase Orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('IDR')->after('total');
            $table->decimal('currency_rate', 15, 6)->default(1)->after('currency_code');
            $table->unsignedBigInteger('tax_rate_id')->nullable()->after('currency_rate');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'currency_rate', 'tax_rate_id', 'tax_amount']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'currency_rate', 'tax_rate_id', 'tax_amount', 'subtotal_amount']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'currency_rate', 'tax_rate_id', 'tax_amount']);
        });
    }
};
