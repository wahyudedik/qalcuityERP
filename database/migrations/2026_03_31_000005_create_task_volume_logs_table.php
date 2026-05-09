<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('task_volume_logs')) {
            Schema::create('task_volume_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('volume', 15, 3);          // volume added in this entry
                $table->decimal('cumulative', 15, 3);       // running total after this entry
                $table->date('date');
                $table->string('description')->nullable();  // e.g. "Pengecoran zona A"
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['project_task_id', 'date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_volume_logs');
    }
};
