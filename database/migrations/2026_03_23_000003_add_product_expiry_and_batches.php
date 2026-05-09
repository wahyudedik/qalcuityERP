<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah flag has_expiry ke products (opsional per produk)
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'has_expiry')) {
                $table->boolean('has_expiry')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('products', 'expiry_alert_days')) {
                $table->integer('expiry_alert_days')->default(2)->after('has_expiry')
                    ->comment('Berapa hari sebelum expired untuk kirim notifikasi');
            }
        });

        // Tabel batch/lot untuk tracking expired per batch
        if (! Schema::hasTable('product_batches')) {
            Schema::create('product_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->string('batch_number', 100);
                $table->integer('quantity')->default(0);
                $table->date('manufacture_date')->nullable();
                $table->date('expiry_date');
                $table->enum('status', ['active', 'expired', 'recalled', 'consumed'])->default('active');
                $table->string('notes', 500)->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'expiry_date', 'status']);
                $table->index(['product_id', 'warehouse_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_expiry', 'expiry_alert_days']);
        });
        Schema::dropIfExists('product_batches');
    }
};
