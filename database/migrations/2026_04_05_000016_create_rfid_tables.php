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
        // RFID Scanner Devices table (create first - no dependencies)
        if (!Schema::hasTable('rfid_scanner_devices')) {
            Schema::create('rfid_scanner_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('device_id')->unique();
                $table->string('vendor')->comment('Zebra, Honeywell, Impinj, etc');
                $table->string('model')->nullable();
                $table->string('scanner_type')->comment('handheld, fixed, portal, mobile');
                $table->string('frequency')->comment('LF, HF, UHF');
                $table->string('connection_type')->comment('usb, bluetooth, wifi, ethernet');
                $table->string('port')->nullable();
                $table->string('ip_address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_connected')->default(false);
                $table->timestamp('last_scan_at')->nullable();
                $table->integer('scan_count')->default(0);
                $table->json('config')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'is_active']);
            });
        }

        // RFID Tags table
        if (!Schema::hasTable('rfid_tags')) {
            Schema::create('rfid_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('tag_uid')->unique()->comment('Unique ID from RFID tag');
                $table->string('tag_type')->comment('rfid, nfc, barcode_qr');
                $table->string('frequency')->nullable()->comment('LF, HF, UHF');
                $table->string('protocol')->nullable()->comment('ISO14443A, Mifare, etc');
                $table->morphs('taggable'); // Polymorphic relationship
                $table->string('status')->default('active')->comment('active, inactive, lost, damaged');
                $table->text('encoded_data')->nullable();
                $table->boolean('is_encrypted')->default(false);
                $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('last_scan_at')->nullable();
                $table->integer('scan_count')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index('tag_uid');
            });
        }

        // RFID Scan Logs table (create last - has foreign keys to both tables above)
        if (!Schema::hasTable('rfid_scan_logs')) {
            Schema::create('rfid_scan_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('tag_id')->constrained('rfid_tags')->onDelete('cascade');
                $table->foreignId('scanner_device_id')->nullable()->constrained('rfid_scanner_devices')->onDelete('set null');
                $table->foreignId('location_id')->nullable()->constrained('warehouse_bins')->onDelete('set null');
                $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
                $table->string('scan_type')->comment('check_in, check_out, transfer, audit, movement');
                $table->foreignId('scanned_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('scan_time');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->json('additional_data')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'scan_time']);
                $table->index('scan_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_scan_logs');
        Schema::dropIfExists('rfid_scanner_devices');
        Schema::dropIfExists('rfid_tags');
    }
};
