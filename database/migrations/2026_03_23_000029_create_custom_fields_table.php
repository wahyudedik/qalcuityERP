<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Definisi field kustom per modul
        if (!Schema::hasTable('custom_fields')) {
            Schema::create('custom_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('module');       // invoice, product, customer, supplier, employee, sales_order
                $table->string('key');          // snake_case identifier
                $table->string('label');        // label tampilan
                $table->string('type');         // text, number, date, select, checkbox, textarea
                $table->json('options')->nullable(); // untuk type=select
                $table->boolean('required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
    
                $table->unique(['tenant_id', 'module', 'key']);
                $table->index(['tenant_id', 'module']);
            });
        }

        // Nilai field kustom per record
        if (!Schema::hasTable('custom_field_values')) {
            Schema::create('custom_field_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('custom_field_id');
                $table->string('model_type');   // App\Models\Invoice, App\Models\Product, dll
                $table->unsignedBigInteger('model_id');
                $table->text('value')->nullable();
                $table->timestamps();
    
                $table->unique(['custom_field_id', 'model_type', 'model_id']);
                $table->index(['tenant_id', 'model_type', 'model_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
