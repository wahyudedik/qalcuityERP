<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Create tenant WhatsApp settings table for multi-provider support.
     * Tenants can configure their own WhatsApp provider and credentials.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tenant_whatsapp_settings')) {
            Schema::create('tenant_whatsapp_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    
                // Provider configuration
                $table->string('provider')->default('fonnte'); // fonnte, wablas, twilio, ultramsg, custom
                $table->string('api_key')->nullable(); // API key/token
                $table->string('api_secret')->nullable(); // API secret (for some providers)
                $table->string('phone_number')->nullable(); // WhatsApp phone number (for Twilio, etc.)
                $table->string('webhook_url')->nullable(); // Custom webhook URL
    
                // Settings
                $table->boolean('is_active')->default(false);
                $table->boolean('enable_invoice_notifications')->default(true);
                $table->boolean('enable_appointment_reminders')->default(true);
                $table->boolean('enable_payment_reminders')->default(true);
                $table->boolean('enable_general_notifications')->default(true);
    
                // Rate limiting
                $table->integer('max_messages_per_day')->default(1000);
                $table->integer('current_messages_today')->default(0);
                $table->date('last_reset_date')->nullable();
    
                $table->timestamps();
    
                // Unique constraint per tenant
                $table->unique('tenant_id');
    
                // Indexes
                $table->index(['provider', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_whatsapp_settings');
    }
};
