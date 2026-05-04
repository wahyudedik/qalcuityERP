<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Print jobs queue
        if (!Schema::hasTable('print_jobs')) {
            Schema::create('print_jobs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('job_type'); // receipt, kitchen_ticket, barcode_label, test_page
                $table->foreignId('reference_id')->nullable(); // order_id, etc
                $table->string('reference_number')->nullable(); // order_number, etc
                $table->string('printer_type')->default('usb'); // usb, network, file, cups
                $table->string('printer_destination');
                $table->json('print_data'); // The data to print
                $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
                $table->text('error_message')->nullable();
                $table->integer('retry_count')->default(0);
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
    
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'created_at']);
            });
        }

        // Printer settings per tenant
        if (!Schema::hasTable('printer_settings')) {
            Schema::create('printer_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('printer_name'); // receipt_printer, kitchen_printer, barcode_printer
                $table->string('printer_type')->default('usb');
                $table->string('printer_destination');
                $table->integer('paper_width')->default(80); // 58 or 80 mm
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('settings')->nullable(); // Additional printer-specific settings
                $table->timestamps();
    
                $table->unique(['tenant_id', 'printer_name']);
                $table->index(['tenant_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('printer_settings');
        Schema::dropIfExists('print_jobs');
    }
};
