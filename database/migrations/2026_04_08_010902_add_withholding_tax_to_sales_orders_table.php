<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * BUG-FIN-004 FIX: Add columns for withholding tax and tax-inclusive pricing
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // BUG-FIN-004: Store withholding tax amount (PPh 23, PPh 21)
            $table->decimal('withholding_tax_amount', 18, 2)->default(0)->after('tax_amount');

            // BUG-FIN-004: Flag for tax-inclusive pricing
            $table->boolean('tax_inclusive')->default(false)->after('withholding_tax_amount');

            // Index for tax queries
            $table->index('withholding_tax_amount');
            $table->index('tax_inclusive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['withholding_tax_amount']);
            $table->dropIndex(['tax_inclusive']);
            $table->dropColumn(['withholding_tax_amount', 'tax_inclusive']);
        });
    }
};
