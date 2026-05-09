<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add print tracking to sales_orders
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_orders', 'receipt_printed_at')) {
                    $table->timestamp('receipt_printed_at')->nullable()->after('updated_at');
                }
                if (! Schema::hasColumn('sales_orders', 'kitchen_ticket_printed_at')) {
                    $table->timestamp('kitchen_ticket_printed_at')->nullable()->after('receipt_printed_at');
                }
                if (! Schema::hasColumn('sales_orders', 'print_count')) {
                    $table->integer('print_count')->default(0)->after('kitchen_ticket_printed_at');
                }
            });
        }

        // Add barcode support to products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'barcode')) {
                    $table->string('barcode')->unique()->nullable()->after('sku');
                }
                if (! Schema::hasColumn('products', 'qr_code')) {
                    $table->string('qr_code')->nullable()->after('barcode');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['receipt_printed_at', 'kitchen_ticket_printed_at', 'print_count']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['barcode', 'qr_code']);
        });
    }
};
