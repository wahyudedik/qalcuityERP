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
        // Tour Packages - Main tour products
        if (! Schema::hasTable('tour_packages')) {
            Schema::create('tour_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('package_code')->unique(); // e.g., TOUR-2026-001
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('destination');
                $table->string('category')->default('domestic'); // domestic, international, adventure, luxury, cultural, etc.
                $table->integer('duration_days')->default(1);
                $table->integer('duration_nights')->default(0);
                $table->integer('min_pax')->default(1);
                $table->integer('max_pax')->nullable();
                $table->decimal('price_per_person', 12, 2)->default(0);
                $table->decimal('cost_per_person', 12, 2)->default(0);
                $table->string('currency')->default('IDR');
                $table->string('status')->default('draft'); // draft, active, inactive, archived
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->json('inclusions')->nullable(); // Array of included items
                $table->json('exclusions')->nullable(); // Array of excluded items
                $table->text('terms_conditions')->nullable();
                $table->text('cancellation_policy')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_featured')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['destination', 'category']);
            });
        }

        // Itinerary Days - Day-by-day activities
        if (! Schema::hasTable('itinerary_days')) {
            Schema::create('itinerary_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tour_package_id')->constrained()->onDelete('cascade');
                $table->integer('day_number'); // Day 1, Day 2, etc.
                $table->string('title'); // e.g., "Arrival in Bali"
                $table->text('description')->nullable();
                $table->json('activities')->nullable(); // Array of activities with time, description, location
                $table->string('accommodation')->nullable(); // Hotel name for overnight
                $table->json('meals')->nullable(); // ['breakfast', 'lunch', 'dinner']
                $table->string('transport_mode')->nullable(); // flight, bus, train, boat, walking
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['tour_package_id', 'day_number']);
                $table->index('tour_package_id');
            });
        }

        // Tour Bookings - Customer bookings
        if (! Schema::hasTable('tour_bookings')) {
            Schema::create('tour_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('booking_number')->unique(); // e.g., TB-2026-0001
                $table->foreignId('tour_package_id')->constrained()->onDelete('restrict');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('customer_name');
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->date('departure_date');
                $table->integer('adults')->default(1);
                $table->integer('children')->default(0);
                $table->integer('infants')->default(0);
                $table->integer('total_pax')->storedAs('adults + children + infants');
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->storedAs('total_pax * unit_price');
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->storedAs('subtotal - discount_amount + tax_amount');
                $table->string('currency')->default('IDR');
                $table->string('status')->default('pending'); // pending, confirmed, paid, cancelled, completed, refunded
                $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid, refunded
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->date('payment_due_date')->nullable();
                $table->text('special_requests')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('assigned_guide')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->string('cancellation_reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['departure_date', 'status']);
                $table->index('customer_id');
            });
        }

        // Booking Passengers - Individual passenger details
        if (! Schema::hasTable('booking_passengers')) {
            Schema::create('booking_passengers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tour_booking_id')->constrained()->onDelete('cascade');
                $table->string('full_name');
                $table->string('passport_number')->nullable();
                $table->date('passport_expiry')->nullable();
                $table->string('nationality')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('type')->default('adult'); // adult, child, infant
                $table->text('dietary_requirements')->nullable();
                $table->text('medical_conditions')->nullable();
                $table->text('special_assistance')->nullable();
                $table->timestamps();

                $table->index('tour_booking_id');
            });
        }

        // Tour Suppliers - Hotels, transport, activity providers
        if (! Schema::hasTable('tour_suppliers')) {
            Schema::create('tour_suppliers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('supplier_code')->unique();
                $table->string('name');
                $table->string('type'); // hotel, transport, activity, restaurant, visa_agent, insurance
                $table->text('description')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('contact_email')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country')->nullable();
                $table->string('website')->nullable();
                $table->decimal('rating', 3, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('status')->default('active'); // active, inactive, suspended
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'type']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // Package Supplier Allocations - Link packages to suppliers
        if (! Schema::hasTable('package_supplier_allocations')) {
            Schema::create('package_supplier_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tour_package_id')->constrained()->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('tour_suppliers')->onDelete('restrict');
                $table->string('service_type'); // accommodation, transport, activity, meal, guide
                $table->text('service_description')->nullable();
                $table->integer('day_number')->nullable(); // Which day this applies to
                $table->decimal('cost_per_unit', 12, 2)->default(0);
                $table->string('unit_type')->default('per_person'); // per_person, per_room, per_vehicle, fixed
                $table->json('details')->nullable(); // Additional details (room type, vehicle type, etc.)
                $table->timestamps();

                $table->index('tour_package_id');
                $table->index('supplier_id');
            });
        }

        // Visa & Documentation Tracking
        if (! Schema::hasTable('visa_applications')) {
            Schema::create('visa_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('tour_booking_id')->nullable()->constrained('tour_bookings')->nullOnDelete();
                $table->foreignId('passenger_id')->nullable()->constrained('booking_passengers')->nullOnDelete();
                $table->string('application_number')->unique();
                $table->string('destination_country');
                $table->string('visa_type'); // tourist, business, transit, etc.
                $table->string('applicant_name');
                $table->string('passport_number');
                $table->date('passport_expiry');
                $table->date('application_date');
                $table->date('intended_travel_date')->nullable();
                $table->string('status')->default('preparing'); // preparing, submitted, processing, approved, rejected, expired
                $table->date('submission_date')->nullable();
                $table->date('approval_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('fee_amount', 12, 2)->default(0);
                $table->string('currency')->default('IDR');
                $table->text('requirements_checklist')->nullable(); // JSON array of requirements
                $table->text('notes')->nullable();
                $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index('tour_booking_id');
            });
        }

        // Travel Documents Storage
        if (! Schema::hasTable('travel_documents')) {
            Schema::create('travel_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('tour_booking_id')->nullable()->constrained('tour_bookings')->nullOnDelete();
                $table->foreignId('passenger_id')->nullable()->constrained('booking_passengers')->nullOnDelete();
                $table->foreignId('visa_application_id')->nullable()->constrained('visa_applications')->nullOnDelete();
                $table->string('document_type'); // passport, visa, ticket, voucher, insurance, itinerary, receipt
                $table->string('document_number')->nullable();
                $table->string('file_path');
                $table->string('file_name');
                $table->string('mime_type')->nullable();
                $table->integer('file_size')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'document_type']);
                $table->index('tour_booking_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_documents');
        Schema::dropIfExists('visa_applications');
        Schema::dropIfExists('package_supplier_allocations');
        Schema::dropIfExists('tour_suppliers');
        Schema::dropIfExists('booking_passengers');
        Schema::dropIfExists('tour_bookings');
        Schema::dropIfExists('itinerary_days');
        Schema::dropIfExists('tour_packages');
    }
};
