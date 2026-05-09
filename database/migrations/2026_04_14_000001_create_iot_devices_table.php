<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('iot_devices')) {
            Schema::create('iot_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('device_id')->unique(); // token unik per device
                $table->string('device_token', 64)->unique(); // secret token untuk auth
                $table->enum('device_type', ['esp32', 'arduino', 'raspberry_pi', 'generic'])->default('generic');
                $table->string('location')->nullable(); // lokasi fisik device
                $table->enum('target_module', [
                    'inventory', 'manufacturing', 'livestock', 'fisheries',
                    'agriculture', 'hrm', 'healthcare', 'general',
                ])->default('general'); // ke module mana data dikirim
                $table->json('sensor_types')->nullable(); // ['temperature','humidity','counter','weight','ph','gps']
                $table->string('firmware_version')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_connected')->default(false);
                $table->timestamp('last_seen_at')->nullable();
                $table->json('config')->nullable(); // konfigurasi tambahan per tenant
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index(['tenant_id', 'target_module']);
                $table->index('device_token');
            });
        }

        if (! Schema::hasTable('iot_telemetry_logs')) {
            Schema::create('iot_telemetry_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('iot_device_id')->constrained()->cascadeOnDelete();
                $table->string('sensor_type'); // temperature, humidity, counter, weight, ph, gps, custom
                $table->decimal('value', 15, 4)->nullable(); // nilai numerik
                $table->string('unit')->nullable(); // °C, %, kg, ppm, dll
                $table->json('payload')->nullable(); // raw payload lengkap dari device
                $table->string('status')->default('received'); // received, processed, error
                $table->timestamp('recorded_at'); // waktu dari device
                $table->timestamps();

                $table->index(['tenant_id', 'iot_device_id', 'recorded_at']);
                $table->index(['tenant_id', 'sensor_type', 'recorded_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_telemetry_logs');
        Schema::dropIfExists('iot_devices');
    }
};
