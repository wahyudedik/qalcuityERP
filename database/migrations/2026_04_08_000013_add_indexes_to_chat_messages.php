<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * BUG-AI-001 FIX: Add indexes to optimize chat message queries
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Index for fetching messages by session (most common query)
            $table->index('chat_session_id', 'idx_chat_messages_session');

            // Composite index for ordered queries
            $table->index(['chat_session_id', 'id'], 'idx_session_id_order');

            // Index for role-based filtering
            $table->index(['chat_session_id', 'role'], 'idx_session_role');

            // Index for created_at (useful for cleanup jobs)
            $table->index('created_at', 'idx_chat_messages_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_chat_messages_session');
            $table->dropIndex('idx_session_id_order');
            $table->dropIndex('idx_session_role');
            $table->dropIndex('idx_chat_messages_created');
        });
    }
};
