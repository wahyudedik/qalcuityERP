<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel universal untuk melacak rantai transaksi
        Schema::create('transaction_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');

            // Source (transaksi asal)
            $table->string('source_type', 60);   // App\Models\SalesOrder, etc.
            $table->unsignedBigInteger('source_id');
            $table->string('source_number', 60)->nullable();

            // Target (transaksi turunan)
            $table->string('target_type', 60);
            $table->unsignedBigInteger('target_id');
            $table->string('target_number', 60)->nullable();

            // Relasi
            $table->string('link_type', 40);     // so_to_do, do_to_invoice, invoice_to_payment, invoice_to_gl, etc.
            $table->decimal('amount', 18, 2)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'source_type', 'source_id']);
            $table->index(['tenant_id', 'target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_links');
    }
};
