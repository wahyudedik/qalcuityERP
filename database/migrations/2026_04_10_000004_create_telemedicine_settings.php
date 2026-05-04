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
        if (!Schema::hasTable('telemedicine_settings')) {
            Schema::create('telemedicine_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
    
                // Jitsi Configuration
                $table->string('jitsi_server_url')->default('https://meet.jit.si');
                $table->string('jitsi_app_id')->nullable(); // For self-hosted with auth
                $table->string('jitsi_secret')->nullable(); // JWT secret for self-hosted
    
                // Features
                $table->boolean('enable_recording')->default(true);
                $table->string('recording_storage_path')->nullable();
                $table->boolean('enable_waiting_room')->default(true);
                $table->boolean('enable_chat')->default(true);
                $table->boolean('enable_screen_share')->default(true);
    
                // Reminders
                $table->boolean('reminder_enabled')->default(true);
                $table->integer('reminder_minutes_before')->default(30);
                $table->boolean('send_email_reminder')->default(true);
                $table->boolean('send_sms_reminder')->default(false);
    
                // Feedback
                $table->boolean('enable_feedback')->default(true);
                $table->boolean('require_feedback')->default(false);
    
                // Consultation Settings
                $table->integer('consultation_timeout_minutes')->default(60);
                $table->integer('max_participants')->default(10);
                $table->boolean('allow_group_consultation')->default(false);
    
                // Branding
                $table->string('custom_logo_url')->nullable();
                $table->string('welcome_message')->nullable();
    
                $table->timestamps();
                $table->unique('tenant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemedicine_settings');
    }
};
