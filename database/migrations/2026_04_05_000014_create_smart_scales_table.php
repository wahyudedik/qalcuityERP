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
        if (! Schema::hasTable('smart_scales')) {
            Schema::create('smart_scales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('device_id')->unique();
                $table->string('vendor'); // Mettler Toledo, Ohaus, CAS, etc
                $table->string('model')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('connection_type'); // serial, usb, bluetooth, network
                $table->string('port'); // COM port or IP address
                $table->integer('baud_rate')->default(9600);
                $table->integer('data_bits')->default(8);
                $table->integer('stop_bits')->default(1);
                $table->string('parity')->default('none');
                $table->decimal('max_capacity', 12, 2)->comment('Maximum capacity in grams');
                $table->integer('precision')->default(2)->comment('Decimal places');
                $table->string('unit')->default('g')->comment('Default unit: g, kg, lb, oz');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_connected')->default(false);
                $table->decimal('last_reading', 12, 4)->nullable();
                $table->timestamp('last_sync_at')->nullable();
                $table->json('config')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
                $table->index('device_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_scales');
    }
};
