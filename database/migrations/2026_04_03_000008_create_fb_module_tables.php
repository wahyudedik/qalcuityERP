<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Restaurant Menus
        if (! Schema::hasTable('restaurant_menus')) {
            Schema::create('restaurant_menus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // e.g., "Breakfast Menu", "Dinner Menu"
                $table->text('description')->nullable();
                $table->enum('type', ['breakfast', 'lunch', 'dinner', 'all_day', 'room_service', 'bar', 'minibar']);
                $table->time('available_from')->nullable();
                $table->time('available_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'type', 'is_active']);
            });
        }

        // Menu Items
        if (! Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_id')->constrained('restaurant_menus')->cascadeOnDelete();
                $table->unsignedBigInteger('category_id')->nullable(); // product category reference
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 15, 2);
                $table->decimal('cost', 15, 2)->default(0); // Cost of goods
                $table->string('category')->nullable(); // Menu category name (e.g., "Set Menu", "Sandwiches")
                $table->string('image_path')->nullable();
                $table->json('allergens')->nullable(); // Array of allergens
                $table->json('dietary_info')->nullable(); // vegetarian, vegan, halal, etc.
                $table->integer('preparation_time')->nullable(); // in minutes
                $table->boolean('is_available')->default(true);
                $table->integer('daily_limit')->nullable(); // Max servings per day
                $table->integer('sold_today')->default(0);
                $table->integer('display_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'menu_id', 'is_available']);
            });
        }

        // Food & Beverage Orders
        if (! Schema::hasTable('fb_orders')) {
            Schema::create('fb_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('order_number')->unique(); // e.g., "RS-20260403-001"
                $table->enum('order_type', ['restaurant_dine_in', 'restaurant_takeaway', 'room_service', 'minibar', 'banquet']);
                $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
                $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
                $table->integer('room_number')->nullable();
                $table->foreignId('table_number')->nullable(); // For restaurant dine-in
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('server_id')->nullable()->constrained('users')->nullOnDelete(); // Waiter/staff
                $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled']);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('service_charge', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->text('special_instructions')->nullable();
                $table->timestamp('ordered_at')->useCurrent();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('prepared_at')->nullable();
                $table->timestamp('served_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
                $table->enum('payment_method', ['cash', 'credit_card', 'room_charge', 'voucher'])->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'order_type', 'status']);
                $table->index(['tenant_id', 'room_number', 'ordered_at']);
            });
        }

        // Order Items
        if (! Schema::hasTable('fb_order_items')) {
            Schema::create('fb_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->constrained('fb_orders')->cascadeOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
                $table->string('item_name'); // Snapshot of item name
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->text('special_requests')->nullable();
                $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
                $table->timestamps();

                $table->index(['order_id', 'status']);
            });
        }

        // Mini-bar Inventory
        if (! Schema::hasTable('minibar_inventories')) {
            Schema::create('minibar_inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->integer('room_number');
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->integer('initial_stock');
                $table->integer('current_stock');
                $table->integer('minimum_stock')->default(2);
                $table->date('last_restocked_at')->nullable();
                $table->foreignId('restocked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['tenant_id', 'room_number', 'menu_item_id']);
                $table->index(['tenant_id', 'room_number']);
            });
        }

        // Mini-bar Transactions (charges to guest)
        if (! Schema::hasTable('minibar_transactions')) {
            Schema::create('minibar_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
                $table->integer('room_number');
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->integer('quantity_consumed');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_charge', 15, 2);
                $table->date('consumption_date');
                $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
                $table->enum('billing_status', ['pending', 'billed', 'waived'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'reservation_id', 'consumption_date'], 'minibar_tx_tenant_res_date_idx');
            });
        }

        // Banquet/Event Bookings
        if (! Schema::hasTable('banquet_events')) {
            Schema::create('banquet_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('event_number')->unique(); // e.g., "BNQ-20260403-001"
                $table->string('event_name');
                $table->text('description')->nullable();
                $table->foreignId('client_guest_id')->nullable()->constrained('guests')->nullOnDelete();
                $table->string('client_name');
                $table->string('client_phone');
                $table->string('client_email')->nullable();
                $table->string('company_name')->nullable();
                $table->enum('event_type', ['wedding', 'conference', 'meeting', 'birthday', 'corporate', 'social', 'other']);
                $table->date('event_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('expected_guests');
                $table->integer('confirmed_guests')->default(0);
                $table->string('venue_room')->nullable(); // Function room name
                $table->text('setup_requirements')->nullable(); // Theater, U-shape, etc.
                $table->json('menu_selection')->nullable(); // Selected menu items
                $table->decimal('venue_rental_fee', 15, 2)->default(0);
                $table->decimal('food_beverage_total', 15, 2)->default(0);
                $table->decimal('additional_charges', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('deposit_amount', 15, 2)->default(0);
                $table->enum('status', ['inquiry', 'proposal_sent', 'confirmed', 'in_progress', 'completed', 'cancelled']);
                $table->foreignId('assigned_coordinator')->nullable()->constrained('users')->nullOnDelete();
                $table->text('internal_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'event_date', 'status']);
            });
        }

        // Banquet Event Orders (BEO) - Detailed catering orders
        if (! Schema::hasTable('banquet_event_orders')) {
            Schema::create('banquet_event_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('banquet_event_id')->constrained('banquet_events')->cascadeOnDelete();
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_price', 15, 2);
                $table->text('special_instructions')->nullable();
                $table->time('serving_time')->nullable();
                $table->timestamps();

                $table->index(['banquet_event_id']);
            });
        }

        // Tables (for restaurant management)
        if (! Schema::hasTable('restaurant_tables')) {
            Schema::create('restaurant_tables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->integer('table_number');
                $table->integer('capacity');
                $table->string('location')->nullable(); // Indoor, Outdoor, Terrace
                $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'table_number']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // Table Reservations
        if (! Schema::hasTable('table_reservations')) {
            Schema::create('table_reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('table_id')->constrained('restaurant_tables')->cascadeOnDelete();
                $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
                $table->string('guest_name');
                $table->string('guest_phone');
                $table->integer('party_size');
                $table->date('reservation_date');
                $table->time('reservation_time');
                $table->enum('status', ['confirmed', 'seated', 'completed', 'no_show', 'cancelled'])->default('confirmed');
                $table->text('special_requests')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'reservation_date', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_reservations');
        Schema::dropIfExists('restaurant_tables');
        Schema::dropIfExists('banquet_event_orders');
        Schema::dropIfExists('banquet_events');
        Schema::dropIfExists('minibar_transactions');
        Schema::dropIfExists('minibar_inventories');
        Schema::dropIfExists('fb_order_items');
        Schema::dropIfExists('fb_orders');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('restaurant_menus');
    }
};
