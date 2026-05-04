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
        if (!Schema::hasTable('fingerprint_devices')) {
            Schema::create('fingerprint_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // Nama perangkat fingerprint
                $table->string('device_id')->unique(); // ID unik perangkat dari vendor
                $table->string('ip_address')->nullable(); // IP address perangkat
                $table->string('port')->default(4370); // Port komunikasi (default 4370 untuk ZKTeco)
                $table->string('protocol')->default('tcp'); // Protokol komunikasi (tcp, udp, http)
                $table->string('vendor')->default('generic'); // Vendor perangkat (zkteco, Suprema, dll)
                $table->string('model')->nullable(); // Model perangkat
                $table->string('api_key')->nullable(); // API key jika menggunakan HTTP API
                $table->string('secret_key')->nullable(); // Secret key untuk autentikasi
                $table->boolean('is_active')->default(true); // Status aktif/tidak aktif
                $table->boolean('is_connected')->default(false); // Status koneksi terakhir
                $table->timestamp('last_sync_at')->nullable(); // Waktu sinkronisasi terakhir
                $table->json('config')->nullable(); // Konfigurasi tambahan dalam format JSON
                $table->text('notes')->nullable(); // Catatan tambahan
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active']);
                $table->index(['tenant_id', 'device_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_devices');
    }
};
