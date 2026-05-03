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
        if (!Schema::hasTable('dairy_milking_sessions')) {
            Schema::create('dairy_milking_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('session_code', 50)->unique();
                $table->date('session_date');
                $table->enum('session_type', ['morning', 'afternoon', 'evening']);
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->integer('total_animals_milked')->default(0);
                $table->decimal('total_milk_volume', 10, 2)->default(0);
                $table->decimal('average_milk_per_animal', 8, 2)->nullable();
                $table->string('operator_name', 100)->nullable();
                $table->text('equipment_notes')->nullable();
                $table->text('issues')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['tenant_id', 'session_date']);
                $table->index(['tenant_id', 'session_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dairy_milking_sessions');
    }
};
