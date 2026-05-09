<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('activity_logs', 'rolled_back_at')) {
                $table->timestamp('rolled_back_at')->nullable()->after('ai_tool_name');
            }
            if (! Schema::hasColumn('activity_logs', 'rolled_back_by')) {
                $table->unsignedBigInteger('rolled_back_by')->nullable()->after('rolled_back_at');
            }
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropColumn(['rolled_back_at', 'rolled_back_by']);
        });
    }
};
