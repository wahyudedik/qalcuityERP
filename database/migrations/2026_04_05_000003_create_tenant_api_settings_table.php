<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_api_settings')) {
            Schema::create('tenant_api_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('key');           // fonnte_token, telegram_bot_token, weather_api_key, etc
                $table->text('value')->nullable(); // stored encrypted for sensitive keys
                $table->boolean('is_encrypted')->default(false);
                $table->string('group')->default('general'); // communication, agriculture, security, ai
                $table->string('label')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'key']);
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_api_settings');
    }
};
