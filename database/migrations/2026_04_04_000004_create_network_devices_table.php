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
        if (! Schema::hasTable('network_devices')) {
            Schema::create('network_devices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name'); // e.g., "Main Router - Branch A"
                $table->string('device_type'); // router, access_point, switch, modem
                $table->string('brand'); // mikrotik, ubiquiti, cisco, openwrt, etc
                $table->string('model')->nullable();
                $table->string('ip_address');
                $table->integer('port')->default(8728); // API port (MikroTik default: 8728)
                $table->string('username')->nullable();
                $table->string('password_encrypted')->nullable();
                $table->string('api_token')->nullable(); // For cloud-managed devices
                $table->string('mac_address')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('firmware_version')->nullable();
                $table->enum('status', ['online', 'offline', 'maintenance', 'error'])->default('offline');
                $table->timestamp('last_seen_at')->nullable();
                $table->json('capabilities')->nullable(); // Supported features
                $table->json('configuration')->nullable(); // Device-specific config
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('parent_device_id')->nullable(); // For hierarchical networks
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('parent_device_id')->references('id')->on('network_devices')->onDelete('set null');

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'device_type']);
                $table->unique(['tenant_id', 'ip_address', 'port']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_devices');
    }
};
