<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('webhook_deliveries', 'duration_ms')) {
                $table->unsignedInteger('duration_ms')->nullable()->after('attempt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->dropColumn('duration_ms');
        });
    }
};
