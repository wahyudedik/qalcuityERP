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
        if (! Schema::hasTable('table_reservations')) {
            Schema::create('table_reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('table_id')->constrained('restaurant_tables')->onDelete('cascade');
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->string('customer_email')->nullable();
                $table->integer('party_size');
                $table->date('reservation_date');
                $table->time('reservation_time');
                $table->integer('duration_minutes')->default(120);
                $table->string('status')->default('confirmed'); // confirmed, seated, completed, cancelled, no_show
                $table->text('special_requests')->nullable();
                $table->decimal('deposit_amount', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->index(['tenant_id', 'reservation_date']);
                $table->index(['table_id', 'reservation_date', 'reservation_time']);
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_reservations');
    }
};
