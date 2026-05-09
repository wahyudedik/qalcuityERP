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
        Schema::table('rooms', function (Blueprint $table) {
            // Add missing housekeeping columns
            if (! Schema::hasColumn('rooms', 'last_cleaned_at')) {
                $table->timestamp('last_cleaned_at')->nullable();
            }

            if (! Schema::hasColumn('rooms', 'cleaned_by')) {
                $table->foreignId('cleaned_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('rooms', 'last_inspected_at')) {
                $table->timestamp('last_inspected_at')->nullable();
            }

            if (! Schema::hasColumn('rooms', 'inspected_by')) {
                $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('rooms', 'cleaning_count_today')) {
                $table->integer('cleaning_count_today')->default(0);
            }

            if (! Schema::hasColumn('rooms', 'requires_deep_clean')) {
                $table->boolean('requires_deep_clean')->default(false);
            }

            if (! Schema::hasColumn('rooms', 'last_deep_clean_at')) {
                $table->date('last_deep_clean_at')->nullable();
            }

            if (! Schema::hasColumn('rooms', 'next_deep_clean_due')) {
                $table->date('next_deep_clean_due')->nullable();
            }

            if (! Schema::hasColumn('rooms', 'occupancy_days')) {
                $table->integer('occupancy_days')->default(0);
            }

            if (! Schema::hasColumn('rooms', 'housekeeping_notes')) {
                $table->json('housekeeping_notes')->nullable();
            }

            // Update status enum to include 'out_of_order'
            // Note: MySQL doesn't support modifying enum directly, so we need to use raw SQL
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied', 'maintenance', 'cleaning', 'blocked', 'out_of_order') DEFAULT 'available'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Revert status enum
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied', 'maintenance', 'cleaning', 'blocked') DEFAULT 'available'");

            // Drop added columns
            $table->dropForeign(['cleaned_by']);
            $table->dropForeign(['inspected_by']);
            $table->dropColumn([
                'last_cleaned_at',
                'cleaned_by',
                'last_inspected_at',
                'inspected_by',
                'cleaning_count_today',
                'requires_deep_clean',
                'last_deep_clean_at',
                'next_deep_clean_due',
                'occupancy_days',
                'housekeeping_notes',
            ]);
        });
    }
};
