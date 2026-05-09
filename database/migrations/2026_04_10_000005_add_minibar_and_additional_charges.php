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
        // Mini-bar items catalog
        if (! Schema::hasTable('minibar_items')) {
            Schema::create('minibar_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name');
                $table->string('category')->default('beverage'); // beverage, snack, food, other
                $table->decimal('price', 10, 2);
                $table->integer('stock')->default(0);
                $table->string('sku')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'category']);
                $table->index('is_active');
            });
        }

        // Mini-bar charges per reservation
        if (! Schema::hasTable('minibar_charges')) {
            Schema::create('minibar_charges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
                $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
                $table->foreignId('item_id')->constrained('minibar_items')->onDelete('restrict');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total', 10, 2);
                $table->timestamp('consumed_at')->nullable();
                $table->string('status')->default('pending'); // pending, charged, voided
                $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'reservation_id']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // Add additional charges column to reservations if not exists
        if (! Schema::hasColumn('reservations', 'additional_charges')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (! Schema::hasColumn('reservations', 'additional_charges')) {
                    $table->decimal('additional_charges', 10, 2)->default(0)->after('discount');
                }
                if (! Schema::hasColumn('reservations', 'charge_breakdown')) {
                    $table->json('charge_breakdown')->nullable()->after('additional_charges');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minibar_charges');
        Schema::dropIfExists('minibar_items');

        if (Schema::hasColumn('reservations', 'additional_charges')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn(['additional_charges', 'charge_breakdown']);
            });
        }
    }
};
