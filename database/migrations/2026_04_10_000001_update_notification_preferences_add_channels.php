<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Add WhatsApp channel, digest frequency, quiet hours, and DND support
     * to notification_preferences table.
     */
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            // Add WhatsApp channel
            $table->boolean('whatsapp')->default(true)->after('push');

            // Digest email frequency: daily, weekly, never
            $table->string('digest_frequency')->default('daily')->after('whatsapp');

            // Quiet hours (Do Not Disturb)
            $table->time('quiet_hours_start')->nullable()->after('digest_frequency');
            $table->time('quiet_hours_end')->nullable()->after('quiet_hours_start');
            $table->boolean('is_dnd')->default(false)->after('quiet_hours_end');

            // Per-module toggles (JSON for flexibility)
            $table->json('module_preferences')->nullable()->after('is_dnd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp',
                'digest_frequency',
                'quiet_hours_start',
                'quiet_hours_end',
                'is_dnd',
                'module_preferences',
            ]);
        });
    }
};
