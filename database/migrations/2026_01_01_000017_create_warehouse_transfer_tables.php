<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom to_warehouse_id ke stock_movements untuk tracking transfer
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete()->after('warehouse_id');
        });

        // Tabel transfer antar gudang
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('transfer_number')->unique();
            $table->integer('quantity');
            $table->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['to_warehouse_id']);
            $table->dropColumn('to_warehouse_id');
        });
    }
};
