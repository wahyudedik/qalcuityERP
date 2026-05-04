<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
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

        // Document templates base table
        if (!Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('doc_type')->default('pdf');
                $table->text('html_content')->nullable();
                $table->text('css_content')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
    
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['tenant_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('documents');
    }
};
