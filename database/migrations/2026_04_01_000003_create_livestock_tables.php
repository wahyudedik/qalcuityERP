<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Livestock herds / flocks — a group of animals in a pen/coop
        if (! Schema::hasTable('livestock_herds')) {
            Schema::create('livestock_herds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('farm_plot_id')->nullable()->constrained()->nullOnDelete(); // kandang/area
                $table->string('code', 30);                            // FLK-001, HRD-A1
                $table->string('name');                                 // "Ayam Broiler Batch 12"
                $table->string('animal_type', 50);                     // ayam, sapi, kambing, ikan, bebek
                $table->string('breed')->nullable();                   // Broiler, Layer, Brahman, Etawa
                $table->integer('initial_count')->default(0);          // jumlah awal masuk
                $table->integer('current_count')->default(0);          // jumlah hidup saat ini
                $table->date('entry_date')->nullable();                // tanggal masuk/DOC
                $table->integer('entry_age_days')->default(0);         // umur saat masuk (hari)
                $table->decimal('entry_weight_kg', 10, 3)->default(0); // berat rata-rata saat masuk
                $table->decimal('purchase_price', 15, 2)->default(0);  // total harga beli
                $table->enum('status', ['active', 'sold', 'harvested', 'completed', 'cancelled'])->default('active');
                $table->date('target_harvest_date')->nullable();
                $table->decimal('target_weight_kg', 10, 3)->default(0); // target berat panen
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'code']);
                $table->index(['tenant_id', 'status']);
                $table->index(['farm_plot_id']);
            });
        }

        // Population movements — every change in count
        if (! Schema::hasTable('livestock_movements')) {
            Schema::create('livestock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('livestock_herd_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->enum('type', [
                    'purchase',    // beli/masuk DOC
                    'birth',       // lahir
                    'transfer_in', // pindah masuk dari kandang lain
                    'transfer_out', // pindah keluar ke kandang lain
                    'death',       // mati
                    'cull',        // afkir
                    'sold',        // dijual hidup
                    'harvested',   // dipotong/panen
                    'adjustment',  // koreksi stok
                ]);
                $table->integer('quantity');                            // + for in, - for out
                $table->integer('count_after');                         // populasi setelah movement
                $table->decimal('weight_kg', 10, 3)->default(0);       // berat total (jika relevan)
                $table->decimal('price_total', 15, 2)->default(0);     // nilai transaksi
                $table->string('reason')->nullable();                  // alasan kematian, tujuan jual, dll
                $table->string('destination')->nullable();             // kandang tujuan / pembeli
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['livestock_herd_id', 'date']);
                $table->index(['tenant_id', 'type', 'date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_movements');
        Schema::dropIfExists('livestock_herds');
    }
};
