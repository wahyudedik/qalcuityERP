<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table) {
            // Journal for payroll payment (Dr Hutang Gaji / Cr Bank)
            $table->foreignId('payment_journal_entry_id')
                ->nullable()
                ->after('journal_entry_id')
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->timestamp('paid_at')->nullable()->after('processed_at');
            $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_journal_entry_id');
            $table->dropConstrainedForeignId('paid_by');
            $table->dropColumn('paid_at');
        });
    }
};
