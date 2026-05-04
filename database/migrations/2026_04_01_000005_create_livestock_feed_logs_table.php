<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('livestock_feed_logs')) {
            Schema::create('livestock_feed_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('livestock_herd_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->string('feed_type');                           // Starter, Grower, Finisher, Konsentrat
                $table->decimal('quantity_kg', 10, 3);                 // jumlah pakan (kg)
                $table->decimal('cost', 15, 2)->default(0);            // biaya pakan hari ini
                $table->integer('population_at_feeding')->default(0);  // populasi saat pemberian
                $table->decimal('avg_body_weight_kg', 8, 3)->default(0); // berat rata-rata saat ini (sampling)
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->index(['livestock_herd_id', 'date']);
                $table->index(['tenant_id', 'date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_feed_logs');
    }
};
