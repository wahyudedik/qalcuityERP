<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // customer_id nullable untuk POS (walk-in customer)
            if (! Schema::hasColumn('sales_orders', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->change();
            }
            // metode pembayaran
            if (! Schema::hasColumn('sales_orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('notes'); // cash, transfer, qris
                // tipe order: pos = quick sale, order = sales order biasa
                $table->enum('source', ['pos', 'order'])->default('order')->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'source']);
            $table->foreignId('customer_id')->nullable(false)->change();
        });
    }
};
