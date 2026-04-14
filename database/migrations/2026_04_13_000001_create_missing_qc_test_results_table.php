<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('qc_test_results')) {
            return;
        }

        Schema::create('qc_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('cosmetic_batch_records')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('qc_test_templates')->nullOnDelete();
            $table->string('test_code')->unique();
            $table->string('test_name');
            $table->string('test_category')->default('physical');
            $table->string('sample_id')->nullable();
            $table->json('parameters')->nullable();
            $table->string('result')->nullable(); // pass, fail, inconclusive
            $table->text('observations')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('test_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('status')->default('draft'); // draft, completed, approved, rejected
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_test_results');
    }
};
