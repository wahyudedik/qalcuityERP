<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_sessions', 'session_type')) {
                $table->string('session_type')->default('chat')->after('metadata');  // chat | agent
                $table->json('active_plan')->nullable()->after('session_type');
            }
            if (! Schema::hasColumn('chat_sessions', 'execution_status')) {
                $table->string('execution_status')->nullable()->after('active_plan'); // planning | awaiting_approval | executing | completed | cancelled
                $table->json('erp_context_snapshot')->nullable()->after('execution_status');
            }
            if (! Schema::hasColumn('chat_sessions', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false)->after('erp_context_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_type', 'active_plan', 'execution_status', 'erp_context_snapshot', 'is_cancelled']);
        });
    }
};
