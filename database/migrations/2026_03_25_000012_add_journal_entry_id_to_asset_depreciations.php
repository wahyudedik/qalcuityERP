<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_depreciations', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_depreciations', 'journal_entry_id')) {
                $table->foreignId('journal_entry_id')
                    ->nullable()
                    ->after('book_value_after')
                    ->constrained('journal_entries')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('asset_depreciations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('journal_entry_id');
        });
    }
};
