<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix webhook_deliveries table structure
        if (Schema::hasTable('webhook_deliveries')) {
            Schema::table('webhook_deliveries', function (Blueprint $table) {
                // Remove old columns
                if (Schema::hasColumn('webhook_deliveries', 'webhook_subscription_id')) {
                    // Try to drop foreign key with different possible names
                    $connection = Schema::getConnection();
                    $foreignKeys = [
                        'webhook_deliveries_webhook_subscription_id_foreign',
                        'fk_webhook_subscription_id',
                    ];

                    foreach ($foreignKeys as $fkName) {
                        try {
                            $connection->statement("ALTER TABLE webhook_deliveries DROP FOREIGN KEY {$fkName}");
                            break;
                        } catch (Exception $e) {
                            // Try next foreign key name
                        }
                    }

                    $table->dropColumn('webhook_subscription_id');
                }
                if (Schema::hasColumn('webhook_deliveries', 'event')) {
                    $table->dropColumn('event');
                }
                if (Schema::hasColumn('webhook_deliveries', 'attempt')) {
                    $table->dropColumn('attempt');
                }
                if (Schema::hasColumn('webhook_deliveries', 'duration_ms')) {
                    $table->dropColumn('duration_ms');
                }

                // Add new columns if they don't exist
                if (! Schema::hasColumn('webhook_deliveries', 'subscription_id')) {
                    $table->foreignId('subscription_id')->after('id')->constrained('webhook_subscriptions')->onDelete('cascade');
                }
                if (! Schema::hasColumn('webhook_deliveries', 'event_type')) {
                    $table->string('event_type')->after('subscription_id');
                }
                if (! Schema::hasColumn('webhook_deliveries', 'attempt_count')) {
                    $table->integer('attempt_count')->default(0)->after('response_body');
                }
                if (! Schema::hasColumn('webhook_deliveries', 'max_attempts')) {
                    $table->integer('max_attempts')->default(5)->after('attempt_count');
                }
                if (! Schema::hasColumn('webhook_deliveries', 'next_retry_at')) {
                    $table->timestamp('next_retry_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('webhook_deliveries', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('next_retry_at');
                }
                if (! Schema::hasColumn('webhook_deliveries', 'error_message')) {
                    $table->text('error_message')->nullable()->after('delivered_at');
                }
            });

            // Add indexes - just try to add them, ignore if they exist
            try {
                Schema::table('webhook_deliveries', function (Blueprint $table) {
                    $table->index(['subscription_id', 'status']);
                });
            } catch (Exception $e) {
                // Index might already exist
            }

            try {
                Schema::table('webhook_deliveries', function (Blueprint $table) {
                    $table->index(['status', 'next_retry_at']);
                });
            } catch (Exception $e) {
                // Index might already exist
            }

            try {
                Schema::table('webhook_deliveries', function (Blueprint $table) {
                    $table->index(['event_type', 'created_at']);
                });
            } catch (Exception $e) {
                // Index might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('webhook_deliveries')) {
            Schema::table('webhook_deliveries', function (Blueprint $table) {
                $table->dropForeign(['subscription_id']);
                $table->dropColumn(['subscription_id', 'event_type', 'attempt_count', 'max_attempts', 'next_retry_at', 'delivered_at', 'error_message']);
                $table->foreignId('webhook_subscription_id')->constrained('webhook_subscriptions')->onDelete('cascade');
                $table->string('event');
                $table->integer('attempt')->default(1);
                $table->unsignedInteger('duration_ms')->nullable();
            });
        }
    }
};
