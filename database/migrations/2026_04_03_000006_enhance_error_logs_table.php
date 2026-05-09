<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            // Add UUID for unique identification
            if (! Schema::hasColumn('error_logs', 'uuid')) {
                $table->uuid('uuid')->unique()->index()->after('id');
            }

            // Enhance type tracking
            if (! Schema::hasColumn('error_logs', 'type')) {
                $table->string('type')->default('exception')->after('level');
            }

            // Rename 'trace' to 'stack_trace' if exists
            if (Schema::hasColumn('error_logs', 'trace') && ! Schema::hasColumn('error_logs', 'stack_trace')) {
                $table->renameColumn('trace', 'stack_trace');
            }

            // Add request data tracking
            if (! Schema::hasColumn('error_logs', 'request_data')) {
                $table->json('request_data')->nullable()->after('context');
            }

            // Add exception classification
            if (! Schema::hasColumn('error_logs', 'exception_class')) {
                $table->string('exception_class')->nullable()->after('method');
            }

            // Add resolution tracking - check constraint carefully
            if (! Schema::hasColumn('error_logs', 'resolved_by')) {
                try {
                    if (! Schema::hasColumn('error_logs', 'resolved_by')) {
                        $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete()->after('resolved_at');
                    }
                } catch (Exception $e) {
                    // Constraint might already exist, skip
                    Log::warning('Could not add resolved_by constraint: '.$e->getMessage());
                }
            }

            if (! Schema::hasColumn('error_logs', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('resolved_by');
            }

            // Add alerting fields
            if (! Schema::hasColumn('error_logs', 'notified')) {
                $table->boolean('notified')->default(false)->after('resolution_notes');
            }
            if (! Schema::hasColumn('error_logs', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('notified');
            }
            if (! Schema::hasColumn('error_logs', 'occurrence_count')) {
                $table->integer('occurrence_count')->default(1)->after('notified_at');
            }
            if (! Schema::hasColumn('error_logs', 'first_occurrence')) {
                $table->timestamp('first_occurrence')->nullable()->after('occurrence_count');
            }
        });

        // Add indexes safely (ignore if already exist)
        try {
            Schema::table('error_logs', function (Blueprint $table) {
                $table->index(['created_at', 'level'], 'idx_errorlogs_created_level');
                $table->index(['tenant_id', 'level'], 'idx_errorlogs_tenant_level');
                $table->index(['exception_class', 'created_at'], 'idx_errorlogs_exception_created');
            });
        } catch (Exception $e) {
            Log::warning('Some indexes might already exist: '.$e->getMessage());
        }

        // Generate UUIDs for existing records
        try {
            DB::statement('UPDATE error_logs SET uuid = LOWER(HEX(RANDOM_BYTES(16))) WHERE uuid IS NULL');
        } catch (Exception $e) {
            Log::warning('Could not generate UUIDs: '.$e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'type',
                'request_data',
                'exception_class',
                'resolved_by',
                'resolution_notes',
                'notified',
                'notified_at',
                'occurrence_count',
                'first_occurrence',
            ]);

            // Restore original column name
            if (Schema::hasColumn('error_logs', 'stack_trace')) {
                $table->renameColumn('stack_trace', 'trace');
            }
        });
    }
};
