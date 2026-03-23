<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['tier', 'contract', 'promo'])->default('tier');
            $table->text('description')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 20, 2);           // harga khusus
            $table->decimal('discount_percent', 5, 2)->default(0); // diskon % opsional
            $table->decimal('min_qty', 10, 2)->default(1); // minimum qty untuk harga ini
            $table->timestamps();

            $table->unique(['price_list_id', 'product_id', 'min_qty']);
            $table->index('price_list_id');
            $table->index('product_id');
            $table->foreign('price_list_id')->references('id')->on('price_lists')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // Assign price list ke customer (many-to-many dengan prioritas)
        Schema::create('customer_price_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedTinyInteger('priority')->default(1); // 1 = tertinggi
            $table->timestamps();

            $table->unique(['customer_id', 'price_list_id']);
            $table->index('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('price_list_id')->references('id')->on('price_lists')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_price_lists');
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
    }
};
