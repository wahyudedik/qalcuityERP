<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rab_items')) {
            Schema::create('rab_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('parent_id')->nullable(); // for hierarchy (group → sub-group → item)
                $table->string('code', 30)->nullable();              // e.g. "I", "I.1", "I.1.a"
                $table->string('name');                               // item pekerjaan
                $table->enum('type', ['group', 'item'])->default('item');
                $table->string('category')->nullable();               // material, labor, equipment, subcontract, overhead
                $table->decimal('volume', 15, 3)->default(0);         // jumlah/kuantitas
                $table->string('unit', 30)->nullable();               // m3, m2, kg, ls, unit, titik, dll
                $table->decimal('unit_price', 15, 2)->default(0);     // harga satuan
                $table->decimal('coefficient', 10, 4)->default(1);    // koefisien (default 1)
                $table->decimal('subtotal', 15, 2)->default(0);       // volume × unit_price × coefficient
                $table->decimal('actual_cost', 15, 2)->default(0);    // realisasi biaya
                $table->decimal('actual_volume', 15, 3)->default(0);  // realisasi volume
                $table->integer('sort_order')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
    
                $table->foreign('parent_id')->references('id')->on('rab_items')->nullOnDelete();
                $table->index(['project_id', 'parent_id']);
                $table->index(['project_id', 'type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_items');
    }
};
