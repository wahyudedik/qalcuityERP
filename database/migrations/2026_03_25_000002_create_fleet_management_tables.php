<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kendaraan operasional
        if (! Schema::hasTable('fleet_vehicles')) {
            Schema::create('fleet_vehicles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('plate_number', 20);
                $table->string('name');                          // "Toyota Avanza 2024"
                $table->string('type', 30)->default('car');      // car, truck, motorcycle, van
                $table->string('brand', 50)->nullable();
                $table->string('model', 50)->nullable();
                $table->year('year')->nullable();
                $table->string('color', 30)->nullable();
                $table->string('vin', 50)->nullable();           // Vehicle Identification Number
                $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete(); // link ke modul aset
                $table->enum('status', ['available', 'in_use', 'maintenance', 'retired'])->default('available');
                $table->date('registration_expiry')->nullable(); // STNK expired
                $table->date('insurance_expiry')->nullable();
                $table->unsignedInteger('odometer')->default(0); // km terakhir
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'plate_number']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // Driver / pengemudi
        if (! Schema::hasTable('fleet_drivers')) {
            Schema::create('fleet_drivers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete(); // link ke HRM
                $table->string('name');
                $table->string('license_number', 30)->nullable();  // SIM
                $table->string('license_type', 10)->nullable();    // A, B1, B2, C
                $table->date('license_expiry')->nullable();
                $table->string('phone', 20)->nullable();
                $table->enum('status', ['active', 'on_trip', 'off_duty', 'inactive'])->default('active');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }

        // Trip / penugasan kendaraan
        if (! Schema::hasTable('fleet_trips')) {
            Schema::create('fleet_trips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // yang membuat trip
                $table->string('trip_number', 30);
                $table->string('purpose');                       // tujuan perjalanan
                $table->string('origin')->nullable();
                $table->string('destination')->nullable();
                $table->unsignedInteger('odometer_start')->nullable();
                $table->unsignedInteger('odometer_end')->nullable();
                $table->datetime('departed_at')->nullable();
                $table->datetime('returned_at')->nullable();
                $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
                $table->string('reference_type', 30)->nullable(); // delivery_order, purchase_order, etc
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }

        // Fuel log / catatan BBM
        if (! Schema::hasTable('fleet_fuel_logs')) {
            Schema::create('fleet_fuel_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->unsignedInteger('odometer');
                $table->string('fuel_type', 30)->default('pertamax'); // pertalite, pertamax, solar, dll
                $table->decimal('liters', 8, 2);
                $table->decimal('price_per_liter', 10, 2);
                $table->decimal('total_cost', 12, 2);
                $table->string('station')->nullable();           // nama SPBU
                $table->string('receipt_number', 50)->nullable();
                $table->foreignId('expense_id')->nullable();     // link ke modul expense
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'vehicle_id', 'date']);
            });
        }

        // Maintenance schedule & log
        if (! Schema::hasTable('fleet_maintenances')) {
            Schema::create('fleet_maintenances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
                $table->string('type', 30)->default('routine');  // routine, repair, inspection, tire, oil_change
                $table->string('description');
                $table->date('scheduled_date')->nullable();
                $table->date('completed_date')->nullable();
                $table->unsignedInteger('odometer_at')->nullable();
                $table->decimal('cost', 12, 2)->default(0);
                $table->string('vendor')->nullable();            // bengkel / vendor
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
                $table->unsignedInteger('next_km')->nullable();  // maintenance berikutnya di km berapa
                $table->date('next_date')->nullable();            // atau tanggal berapa
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'vehicle_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_maintenances');
        Schema::dropIfExists('fleet_fuel_logs');
        Schema::dropIfExists('fleet_trips');
        Schema::dropIfExists('fleet_drivers');
        Schema::dropIfExists('fleet_vehicles');
    }
};
