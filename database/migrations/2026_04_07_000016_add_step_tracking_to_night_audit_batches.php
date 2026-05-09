<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * BUG-HOTEL-002 FIX: Add step tracking columns to prevent incomplete audits
     */
    public function up(): void
    {
        Schema::table('night_audit_batches', function (Blueprint $table) {
            // Track which steps have been completed
            if (! Schema::hasColumn('night_audit_batches', 'room_charges_posted')) {
                $table->boolean('room_charges_posted')->default(false)->after('total_room_revenue');
            }
            if (! Schema::hasColumn('night_audit_batches', 'room_charges_posted_at')) {
                $table->timestamp('room_charges_posted_at')->nullable()->after('room_charges_posted');
            }

            if (! Schema::hasColumn('night_audit_batches', 'fb_revenue_posted')) {
                $table->boolean('fb_revenue_posted')->default(false)->after('total_fb_revenue');
            }
            if (! Schema::hasColumn('night_audit_batches', 'fb_revenue_posted_at')) {
                $table->timestamp('fb_revenue_posted_at')->nullable()->after('fb_revenue_posted');
            }

            if (! Schema::hasColumn('night_audit_batches', 'minibar_charges_posted')) {
                $table->boolean('minibar_charges_posted')->default(false)->after('total_other_revenue');
            }
            if (! Schema::hasColumn('night_audit_batches', 'minibar_charges_posted_at')) {
                $table->timestamp('minibar_charges_posted_at')->nullable()->after('minibar_charges_posted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('night_audit_batches', function (Blueprint $table) {
            $table->dropColumn([
                'room_charges_posted',
                'room_charges_posted_at',
                'fb_revenue_posted',
                'fb_revenue_posted_at',
                'minibar_charges_posted',
                'minibar_charges_posted_at',
            ]);
        });
    }
};
