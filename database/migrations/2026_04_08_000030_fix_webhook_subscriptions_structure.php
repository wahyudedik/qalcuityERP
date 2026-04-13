<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix webhook_subscriptions table structure
        if (Schema::hasTable('webhook_subscriptions')) {
            Schema::table('webhook_subscriptions', function (Blueprint $table) {
                // Remove old columns
                if (Schema::hasColumn('webhook_subscriptions', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('webhook_subscriptions', 'url')) {
                    $table->dropColumn('url');
                }
                if (Schema::hasColumn('webhook_subscriptions', 'secret')) {
                    $table->dropColumn('secret');
                }
                if (Schema::hasColumn('webhook_subscriptions', 'retry_count')) {
                    $table->dropColumn('retry_count');
                }

                // Add new columns if they don't exist
                if (!Schema::hasColumn('webhook_subscriptions', 'integration_id')) {
                    $table->foreignId('integration_id')->after('tenant_id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('webhook_subscriptions', 'endpoint_url')) {
                    $table->string('endpoint_url')->after('integration_id');
                }
                if (!Schema::hasColumn('webhook_subscriptions', 'secret_key')) {
                    $table->string('secret_key')->nullable()->after('endpoint_url');
                }
            });

            // Add indexes - just try to add them, ignore if they exist
            try {
                Schema::table('webhook_subscriptions', function (Blueprint $table) {
                    $table->index(['tenant_id', 'is_active']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                Schema::table('webhook_subscriptions', function (Blueprint $table) {
                    $table->index(['integration_id', 'is_active']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('webhook_subscriptions')) {
            Schema::table('webhook_subscriptions', function (Blueprint $table) {
                $table->dropForeign(['integration_id']);
                $table->dropColumn(['integration_id', 'endpoint_url', 'secret_key']);
                $table->string('name');
                $table->string('url');
                $table->string('secret', 64)->nullable();
                $table->integer('retry_count')->default(0);
            });
        }
    }
};
