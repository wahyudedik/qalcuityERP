<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * BUG-API-002: Add composite indexes for efficient API token validation
     * This optimizes the query in ApiTokenAuth middleware:
     * - WHERE token = ? AND is_active = true AND (expires_at IS NULL OR expires_at > ?)
     */
    public function up(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            // Composite index for token validation (most critical query)
            // This covers: WHERE token = ? AND is_active = ? AND expires_at > ?
            if (!$this->hasIndex('api_tokens', 'idx_api_tokens_token_active_expires')) {
                $table->index(['token', 'is_active', 'expires_at'], 'idx_api_tokens_token_active_expires');
            }

            // Index for cleanup job: WHERE is_active = false AND updated_at < ?
            if (!$this->hasIndex('api_tokens', 'idx_api_tokens_active_updated')) {
                $table->index(['is_active', 'updated_at'], 'idx_api_tokens_active_updated');
            }

            // Index for expired tokens query: WHERE is_active = true AND expires_at < ?
            if (!$this->hasIndex('api_tokens', 'idx_api_tokens_active_expires')) {
                $table->index(['is_active', 'expires_at'], 'idx_api_tokens_active_expires');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->dropIndex('idx_api_tokens_token_active_expires');
            $table->dropIndex('idx_api_tokens_active_updated');
            $table->dropIndex('idx_api_tokens_active_expires');
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        return !empty($indexes);
    }
};
