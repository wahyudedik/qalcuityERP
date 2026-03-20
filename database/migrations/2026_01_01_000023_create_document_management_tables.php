<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // pdf, docx, xlsx, jpg, etc
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->string('category')->nullable(); // contract, invoice, po, so, hr, other
            $table->string('related_type')->nullable(); // App\Models\PurchaseOrder, etc
            $table->unsignedBigInteger('related_id')->nullable();
            $table->text('description')->nullable();
            $table->string('tags')->nullable(); // comma-separated
            $table->timestamps();
            $table->index(['tenant_id', 'related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
