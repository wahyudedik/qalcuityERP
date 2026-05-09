<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG-CRM-003 FIX: Add balance_after column for audit trail
     */
    public function up(): void
    {
        if (! Schema::hasColumn('loyalty_transactions', 'balance_after')) {
            Schema::table('loyalty_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('loyalty_transactions', 'balance_after')) {
                    $table->integer('balance_after')->nullable()->after('points')
                        ->comment('Balance after this transaction');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loyalty_transactions', 'balance_after')) {
            Schema::table('loyalty_transactions', function (Blueprint $table) {
                $table->dropColumn('balance_after');
            });
        }
    }
};
