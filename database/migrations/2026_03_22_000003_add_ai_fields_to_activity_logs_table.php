<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->boolean('is_ai_action')->default(false)->after('user_agent');
            $table->string('ai_tool_name')->nullable()->after('is_ai_action');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['is_ai_action', 'ai_tool_name']);
        });
    }
};
